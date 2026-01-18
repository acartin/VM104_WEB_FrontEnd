
import { GridFilters } from './GridFilters.js';

export class CustomLeadsGrid {
    constructor(container, config) {
        this.container = container;
        this.config = config;
        this.data = [];
        this.filteredData = [];
        this.currentPage = 1;
        this.pageSize = 10;
        this.sortState = { colId: null, direction: 'asc' };

        // Global Instance Registry for click handlers (sorting/pagination)
        window.gridInstances = window.gridInstances || {};
        if (this.container.id) {
            window.gridInstances[this.container.id] = this;
            console.log(`[CustomLeadsGrid] Registered instance: ${this.container.id}`, this);
        } else {
            console.warn("[CustomLeadsGrid] Container has no ID, cannot register instance!");
        }

        // Initialize Filters
        if (this.config.enableFilters) {
            this.filters = new GridFilters(this);
        }

        this.renderSkeleton();
        this.init();
    }

    async init() {
        try {
            await this.fetchData();

            if (this.filters) this.filters.renderFilterBar();

            this.applySort();
            this.render();
        } catch (e) {
            console.error("CustomLeadsGrid Init Error:", e);
            this.container.innerHTML = `<div class="p-3 text-danger">Error initializing grid: ${e.message}</div>`;
        }
    }

    renderSkeleton() {
        // Initial Loading State
        this.container.innerHTML = `
            <div class="custom-grid-wrapper">
                <div class="custom-grid-header mb-2 d-flex justify-content-between">
                    <div class="grid-controls"></div>
                    <div id="${this.container.id}-loader" class="text-muted small">Loading...</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-nowrap align-middle">
                        <thead>
                            <tr>${this.config.columns.map(c => `<th>${c.label || c.name}</th>`).join('')}<th></th></tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="${this.config.columns.length + 1}" class="text-center p-4">
                                <div class="spinner-border text-primary spinner-sm"></div>
                            </td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="custom-grid-footer mt-2 d-flex justify-content-between align-items-center">
                    <span id="${this.container.id}-info" class="text-muted small"></span>
                    <ul id="${this.container.id}-pager" class="pagination pagination-sm mb-0"></ul>
                </div>
            </div>
            <style>
                .custom-grid-wrapper th { cursor: pointer; user-select: none; transition: all 0.2s ease; background-color: var(--vz-light); color: var(--vz-body-color); }
                .custom-grid-wrapper th:hover { background-color: var(--vz-secondary-bg-subtle) !important; color: var(--vz-secondary) !important; }
                .badge-pill-custom { padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 11px; display: inline-block; min-width: 80px; text-align: center; background: transparent !important; }
                .sort-icon { margin-left: 5px; opacity: 0.5; font-size: 20px; font-weight: bold; }
                .active-sort { opacity: 1; color: var(--vz-primary); font-size: 20px; font-weight: bold; }
                .custom-grid-wrapper th.sorted-column { background-color: var(--vz-secondary-bg-subtle) !important; }
            </style>
        `;
    }

    async fetchData() {
        const loader = document.getElementById(`${this.container.id}-loader`);
        if (loader) loader.style.display = 'block';

        const token = localStorage.getItem('access_token');
        const headers = token ? { 'Authorization': `Bearer ${token}` } : {};

        try {
            // Append version to avoid cache
            const url = `${window.AppConfig.API_BASE_URL}${this.config.data_url}`;
            const res = await fetch(url, { headers });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            this.data = await res.json();
            this.filteredData = [...this.data];
        } finally {
            if (loader) loader.style.display = 'none';
        }
    }

    applySort(colId = null) {
        if (colId) {
            if (this.sortState.colId === colId) {
                this.sortState.direction = this.sortState.direction === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortState.colId = colId;
                this.sortState.direction = 'desc';
            }
        }

        if (!this.sortState.colId) return;

        const colDef = this.config.columns.find(c => c.id === this.sortState.colId);
        const dir = this.sortState.direction === 'asc' ? 1 : -1;

        this.filteredData.sort((a, b) => {
            const valA = this.getSortValue(a, colDef);
            const valB = this.getSortValue(b, colDef);
            if (valA < valB) return -1 * dir;
            if (valA > valB) return 1 * dir;
            return 0;
        });

        this.currentPage = 1;
    }

    getSortValue(row, col) {
        if (col.type === 'scoring-pillar') {
            const item = row[col.id];
            // Sort by Score (numeric) or Label (string)
            if (item && typeof item === 'object') {
                return item.score ?? item.value ?? item.label ?? 0;
            }
            return 0;
        }
        if (col.id === 'identity') {
            return Number(row.score_total || row.identity?.score || 0);
        }
        const val = row[col.id];
        return (typeof val === 'string') ? val.toLowerCase() : (val || 0);
    }

    render() {
        const start = (this.currentPage - 1) * this.pageSize;
        const end = start + this.pageSize;
        const pageIcons = { asc: '↑', desc: '↓' };

        // Render Header
        const thead = this.config.columns.map(c => {
            const isSorted = this.sortState.colId === c.id;
            const icon = c.icon ? `<i class="${c.icon} me-1 text-muted"></i>` : '';
            return `<th onclick="window.gridInstances['${this.container.id}'].handleSort('${c.id}')">
                ${icon}${c.label || c.name} ${isSorted ? pageIcons[this.sortState.direction] : ''}
            </th>`;
        }).join('') + '<th></th>';

        // Render Body
        const tbody = this.filteredData.slice(start, end).map(row => `
            <tr onclick="window.navigateTo('/dashboard/leads/${row.id}')" style="cursor: pointer;">
                ${this.config.columns.map(col => `<td>${this.renderCell(row, col)}</td>`).join('')}
                <td onclick="event.stopPropagation()">${this.renderActions(row)}</td>
            </tr>
        `).join('');

        const table = this.container.querySelector('table');
        table.querySelector('thead tr').innerHTML = thead;
        table.querySelector('tbody').innerHTML = tbody || '<tr><td colspan="100" class="text-center text-muted">No data found</td></tr>';

        this.renderPager();
    }

    renderCell(row, col) {
        // Identity Column
        if (col.id === 'identity' || col.type === 'gauge-identity') {
            const identity = row.identity || {};
            const score = identity.score || row.score_total || 0;
            const name = identity.name || row.full_name || 'Unknown';
            const email = row.email || '-';
            // Legacy Style: Dark background circle
            const colorClass = identity.color || (score > 80 ? 'success' : score > 50 ? 'warning' : 'danger');

            return `
                <div class="d-flex align-items-center">
                    <div class="text-${colorClass} border-${colorClass}" style="width:32px; height:32px; border-radius:50%; background:#222; border:2px solid currentColor; display:flex; align-items:center; justify-content:center; font-weight:bold; margin-right:10px; font-size:12px;">
                        ${score}
                    </div>
                    <div>
                        <div class="fw-bold" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:150px;">
                            <a href="/dashboard/leads/${row.id}" class="text-reset text-decoration-none">${name}</a>
                        </div>
                        <div class="text-muted" style="font-size:10px;">${email}</div>
                    </div>
                </div>
            `;
        }

        // Scoring Pillars
        if (col.type === 'scoring-pillar') {
            const item = row[col.id] || {};
            const label = item.label || null;
            const colorClass = item.color || 'secondary';
            const icon = item.icon || '';

            // Special handling for preference/status
            if (col.id === 'contact_preference' || col.id === 'status') {
                if (!label) return `<span class="text-muted fst-italic">-</span>`;
                const iconHtml = icon ? `<i class="${icon} me-2 align-middle fs-22 text-${colorClass}"></i>` : '';
                return `<span class="d-inline-flex align-items-center">${iconHtml}<span class="text-body fw-normal">${label}</span></span>`;
            }

            // Default Pill Style
            const displayLabel = label || '-';
            return `<span class="badge-pill-custom text-${colorClass} border border-${colorClass}">${displayLabel.toUpperCase()}</span>`;
        }

        return row[col.id] || '-';
    }

    renderActions(row) {
        return `
            <button class="btn btn-sm btn-soft-secondary" onclick="window.navigateTo('/dashboard/leads/${row.id}')">
                <i class="ri-eye-line"></i>
            </button>
        `;
    }

    renderPager() {
        const total = this.filteredData.length;
        const totalPages = Math.ceil(total / this.pageSize);
        const start = (this.currentPage - 1) * this.pageSize + 1;
        const end = Math.min(start + this.pageSize - 1, total);

        this.container.querySelector(`#${this.container.id}-info`).innerText = `Showing ${start}-${end} of ${total}`;

        // Simple Pager
        let html = '';
        if (totalPages > 1) {
            html += `<li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="window.gridInstances['${this.container.id}'].setPage(${this.currentPage - 1}); return false;">Prev</a></li>`;
            html += `<li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" onclick="window.gridInstances['${this.container.id}'].setPage(${this.currentPage + 1}); return false;">Next</a></li>`;
        }
        this.container.querySelector(`#${this.container.id}-pager`).innerHTML = html;
    }

    // Handlers
    handleSort(colId) { this.applySort(colId); this.render(); }
    setPage(p) { this.currentPage = p; this.render(); }
}
