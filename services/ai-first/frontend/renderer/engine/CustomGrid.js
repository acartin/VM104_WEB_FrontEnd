
/**
 * Custom High-Performance Grid Engine
 * Optimized for specific "Leads Control" requirements:
 * - Direct DOM Manipulation (Atomic updates)
 * - Custom Sorting for "Scoring Pillars"
 * - Lightweight Pagination
 */
export class CustomLeadsGrid {
    constructor(container, config) {
        this.container = container;
        this.config = config;
        this.data = [];
        this.filteredData = [];
        this.currentPage = 1;
        this.pageSize = 10;
        this.sortState = { colId: null, direction: 'asc' }; // asc or desc

        // Register Global Instance for Inline Handlers
        window.gridInstances = window.gridInstances || {};
        if (this.container.id) {
            window.gridInstances[this.container.id] = this;
        } else {
            console.warn("CustomGrid: Container has no ID, sorting will fail.");
        }

        // Render Skeleton immediately
        this.renderSkeleton();
        this.init();
    }

    async init() {
        try {
            await this.fetchData();
            this.applySort(); // Initial sort if any
            this.render();
        } catch (e) {
            console.error("CustomGrid Error:", e);
            this.container.innerHTML = `<div class="text-danger p-3">Error loading grid: ${e.message}</div>`;
        }
    }

    renderSkeleton() {
        this.container.innerHTML = `
            <div class="custom-grid-wrapper">
                <div class="custom-grid-header d-flex justify-content-between align-items-center mb-2">
                    <div class="grid-controls">
                        <!-- Search/Filters go here -->
                    </div>
                    <div class="grid-loading text-muted small" id="${this.container.id}-loader">
                        Loading data...
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-nowrap align-middle mb-0">
                        <thead>
                            <tr>${this.config.columns.map(c => `<th>${c.label}</th>`).join('')}<th></th></tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="${this.config.columns.length + 1}" class="text-center p-5">
                                <div class="spinner-border text-primary" role="status"></div>
                            </td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="custom-grid-footer d-flex justify-content-between align-items-center mt-3">
                    <span class="text-muted small" id="${this.container.id}-info"></span>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-end mb-0" id="${this.container.id}-pager"></ul>
                    </nav>
                </div>
            </div>
            <style>
                .custom-grid-wrapper th { cursor: pointer; user-select: none; transition: all 0.2s ease; background-color: var(--vz-light); color: var(--vz-body-color); }
                .custom-grid-wrapper th:hover { background-color: var(--vz-secondary-bg-subtle) !important; color: var(--vz-secondary) !important; }
                .badge-pill-custom { padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 11px; display: inline-block; min-width: 80px; text-align: center; }
                .sort-icon { margin-left: 5px; opacity: 0.5; font-size: 20px; font-weight: bold; }
                .active-sort { opacity: 1; color: var(--vz-primary); font-size: 20px; font-weight: bold; }
                /* Highlight the entire header cell when sorted */
                .custom-grid-wrapper th.sorted-column { background-color: var(--vz-secondary-bg-subtle) !important; }
                /* Force transparent background on all badges to use Bootstrap outline style */
                .badge-pill-custom { background: transparent !important; }
            </style>
        `;
    }

    async fetchData() {
        const loader = document.getElementById(`${this.container.id}-loader`);
        if (loader) loader.style.display = 'block';

        const token = localStorage.getItem('access_token');
        const headers = token ? { 'Authorization': `Bearer ${token}` } : {};
        const res = await fetch(`${window.AppConfig.API_BASE_URL}${this.config.data_url}`, { headers });
        if (!res.ok) throw new Error("API Fetch failed");
        this.data = await res.json();
        // Console logs removed for production
        this.filteredData = [...this.data]; // Start with full dataset

        if (loader) loader.style.display = 'none';
    }

    applySort(colId = null) {
        if (colId) {
            if (this.sortState.colId === colId) {
                this.sortState.direction = this.sortState.direction === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortState.colId = colId;
                this.sortState.direction = 'desc'; // Default to desc for numbers/scores
            }
        }

        if (!this.sortState.colId) return;

        const colDef = this.config.columns.find(c => c.id === this.sortState.colId);
        const dir = this.sortState.direction === 'asc' ? 1 : -1;

        this.filteredData.sort((a, b) => {
            let valA = this.getSortValue(a, colDef);
            let valB = this.getSortValue(b, colDef);

            if (valA < valB) return -1 * dir;
            if (valA > valB) return 1 * dir;
            // Tie-breaker: Total Score
            return (b.score_total - a.score_total);
        });

        // Use setTimeout to allow UI to breathe if dataset huge, but for 50 items it's instant.
        this.currentPage = 1; // Always reset to page 1 on sort
    }

    getSortValue(row, col) {
        if (!col) return 0;

        let val;

        // 1. Scoring Pillars (Unwrap object to get numeric score)
        if (col.type === 'scoring-pillar') {
            const cellData = row[col.id];
            if (cellData && typeof cellData === 'object') {
                // Try 'score', 'puntuacion', or 'value'
                val = cellData.score ?? cellData.puntuacion ?? cellData.value;
            }
            // Fallback to flat score if needed
            if (val === undefined) {
                val = row[`score_${col.id}`];
            }
            return Number(val) || 0;
        }

        // 2. Identity / Lead Column (Sort by proper score_total)
        // CHECK ID "identity" explicitly as defined in router.py
        if (col.id === 'identity' || col.type === 'gauge-identity') {
            // Data in router.py is row.identity = { name: ..., score: ... }
            if (row.identity && row.identity.score !== undefined) {
                return Number(row.identity.score);
            }
            return Number(row.score_total) || 0;
        }

        // 3. Status Object (Sort by label text)
        if (col.id === 'status' && typeof row.status === 'object') {
            return (row.status.label || '').toLowerCase();
        }

        // 4. Default Handling
        val = row[col.id];
        if (typeof val === 'string') return val.toLowerCase();
        if (typeof val === 'number') return val;

        return val || '';
    }

    render() {
        // 1. Calculate Slice
        const start = (this.currentPage - 1) * this.pageSize;
        const end = start + this.pageSize;
        const pageData = this.filteredData.slice(start, end);

        // 2. Build Headers (Update sort icons)
        const theadHtml = `
            <tr>
                ${this.config.columns.map(c => {
            let sortIcon = '';
            const isSorted = this.sortState.colId === c.id;
            if (isSorted) {
                sortIcon = this.sortState.direction === 'asc' ? '↑' : '↓';
            }
            // Increased size to fs-18 based on user feedback
            const iconHtml = c.icon ? `<i class="${c.icon} align-middle me-2 fs-20 text-muted"></i>` : '';
            const sortedClass = isSorted ? 'sorted-column' : '';
            return `<th class="${sortedClass}" onclick="window.gridInstances['${this.container.id}'].handleSort('${c.id}')">
                        ${iconHtml}${c.label} <span class="sort-icon ${isSorted ? 'active-sort' : ''}">${sortIcon}</span>
                    </th>`;
        }).join('')}
                <th></th>
            </tr>
        `;

        // 3. Build Body
        const tbodyHtml = pageData.map(row => {
            return `<tr>
                ${this.config.columns.map(col => `<td>${this.renderCell(row, col)}</td>`).join('')}
                <td>${this.renderActions(row)}</td>
            </tr>`;
        }).join('');

        // 4. Update DOM
        const table = this.container.querySelector('table');
        table.querySelector('thead').innerHTML = theadHtml;
        table.querySelector('tbody').innerHTML = tbodyHtml;

        // 5. Update Pager
        this.renderPager();
    }

    renderCell(row, col) {
        if (col.type === 'gauge-identity') {
            // Replicate the Layout: Score Circle + Name + Email
            const identity = row.identity || {};
            const score = identity.score || 0;
            const name = identity.name || row.full_name || 'Unknown';
            const email = row.email || '-';
            // Use backend-provided Bootstrap semantic color (e.g. 'danger', 'warning')
            const colorClass = identity.color || 'secondary';

            return `
                <div class="d-flex align-items-center">
                    <div class="text-${colorClass} border-${colorClass}" style="width:32px; height:32px; border-radius:50%; background:#222; border:2px solid currentColor; display:flex; align-items:center; justify-content:center; font-weight:bold; margin-right:10px; font-size:12px;">
                        ${score}
                    </div>
                    <div>
                        <div class="fw-bold" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:150px;">
                            <a href="/leads/${row.id}" class="text-reset text-decoration-none">${name}</a>
                        </div>
                        <div class="text-muted" style="font-size:10px;">${email}</div>
                    </div>
                </div>
            `;
        }
        if (col.type === 'scoring-pillar') {
            // Data is in nested object: row[col.id] = { label, color, score, ... }
            const item = row[col.id] || {};
            const label = item.label || 'N/A';
            const colorClass = item.color || 'secondary'; // Bootstrap semantic name from DB

            // Use Bootstrap utility classes for theme-aware coloring
            return `<span class="badge-pill-custom text-${colorClass} border border-${colorClass}">${label.toUpperCase()}</span>`;
        }

        // Handle Status Object if present
        if (col.id === 'status' && typeof row.status === 'object') {
            return `<span class="badge bg-${row.status.color || 'primary'}">${row.status.label || 'New'}</span>`;
        }

        return row[col.id] || '-';
    }

    renderActions(row) {
        return `<div class="dropdown">
            <button class="btn btn-ghost-secondary btn-sm" data-bs-toggle="dropdown">•••</button>
            <ul class="dropdown-menu dropdown-menu-end">
                ${this.config.actions.map(act => `
                    <li><a class="dropdown-item" href="${act.url.replace('{id}', row.id)}">
                        <i class="${act.icon}"></i> ${act.label}
                    </a></li>
                `).join('')}
            </ul>
        </div>`;
    }

    renderPager() {
        const totalPages = Math.ceil(this.filteredData.length / this.pageSize);
        const pagerEl = this.container.querySelector(`#${this.container.id}-pager`);
        const infoEl = this.container.querySelector(`#${this.container.id}-info`);

        // Info text
        const start = (this.currentPage - 1) * this.pageSize + 1;
        const end = Math.min(start + this.pageSize - 1, this.filteredData.length);
        infoEl.innerText = `Showing ${start}-${end} of ${this.filteredData.length}`;

        // Buttons
        let html = '';
        // Prev
        html += `<li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="window.gridInstances['${this.container.id}'].setPage(${this.currentPage - 1})">Prev</a>
        </li>`;

        // Numbers (Simplified: only show active + neighbors)
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
                html += `<li class="page-item ${i === this.currentPage ? 'active' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="window.gridInstances['${this.container.id}'].setPage(${i})">${i}</a>
                </li>`;
            } else if (i === this.currentPage - 2 || i === this.currentPage + 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        // Next
        html += `<li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="window.gridInstances['${this.container.id}'].setPage(${this.currentPage + 1})">Next</a>
        </li>`;

        pagerEl.innerHTML = html;
    }

    // Public API for handlers
    handleSort(colId) {
        this.applySort(colId);
        this.render();
    }

    setPage(p) {
        if (p < 1 || p > Math.ceil(this.filteredData.length / this.pageSize)) return;
        this.currentPage = p;
        this.render();
    }

    // Helper colors
    getScoreColor(score) {
        if (score >= 80) return '#00d084'; // Green
        if (score >= 50) return '#ffaa00'; // Orange
        return '#cf2e2e'; // Red
    }
}
