/**
 * Hydration Manager
 * Handles the initialization of interactive components (Grids, etc.)
 */

import { safeAtob, safeBtoa } from '../../utils/base64.js';

const API_BASE_URL = window.AppConfig.API_BASE_URL;
window.gridInstances = {};

async function fetchGridDataForGridJs(container, params = {}) {
    const url = container.dataset.url;
    if (!url) return [];

    try {
        const token = localStorage.getItem('access_token');
        const headers = token ? { 'Authorization': `Bearer ${token}` } : {};

        const urlObj = new URL(`${API_BASE_URL}${url}`);
        Object.keys(params).forEach(key => urlObj.searchParams.append(key, params[key]));

        const res = await fetch(urlObj.toString(), { headers });
        if (!res.ok) throw new Error('Failed to fetch data');
        return await res.json();
    } catch (e) {
        console.error('Grid Data Fetch Error:', e);
        return [];
    }
}

export async function hydrateGrids() {
    const grids = document.querySelectorAll('.js-grid-visual');

    grids.forEach(async (container) => {
        if (container.dataset.initialized) return;
        container.dataset.initialized = "true";

        const gridId = container.id;
        const columns = JSON.parse(container.dataset.columns || '[]');
        const actions = JSON.parse(container.dataset.actions || '[]');
        const schemaStr = container.dataset.schema || '[]';
        const filtersStr = container.dataset.filters || '[]';
        const currentParams = {};

        const gridColumns = [{ id: 'id', name: 'ID', hidden: true }];

        columns.forEach(col => {
            gridColumns.push({
                id: col.key,
                name: col.label,
                sort: col.sortable !== false,
                formatter: (cell) => {
                    if (cell === undefined || cell === null) return '-';
                    if (col.type === 'badge') {
                        const label = (typeof cell === 'object') ? (cell.label || cell.name || JSON.stringify(cell)) : cell;
                        const color = (typeof cell === 'object' && cell.color) ? cell.color : (col.color || 'primary');
                        const mapKey = String(label);
                        const badgeColor = (col.badge_map && col.badge_map[mapKey]) ? col.badge_map[mapKey] : color;
                        return gridjs.html(`<span class="badge bg-${badgeColor}">${label}</span>`);
                    }
                    if (col.truncate && typeof cell === 'string' && cell.length > col.truncate) {
                        return cell.substring(0, col.truncate) + '...';
                    }
                    return cell;
                }
            });
        });

        if (actions.length > 0) {
            gridColumns.push({
                name: 'Actions',
                sort: false,
                formatter: (_, row) => {
                    const idColIndex = gridColumns.findIndex(c => c.id === 'id');
                    const rowId = row.cells[idColIndex].data;
                    const dropdownItems = actions.map(act => {
                        if (act.action === 'modal-form' || act.action === 'edit') {
                            let schemaToPass = act.schema ?
                                ((typeof act.schema === 'string') ? act.schema : safeBtoa(JSON.stringify(act.schema))) :
                                btoa(schemaStr);
                            const url = (act.url || act.action_url || '').replace('{id}', rowId);
                            return `<li><a class="dropdown-item" href="javascript:void(0)" onclick="window.handleEditAction(event, '${rowId}', '${url}', '${schemaToPass}')">
                                <i class="${act.icon} align-bottom me-2 text-muted"></i> ${act.label}
                            </a></li>`;
                        }
                        if (act.action === 'navigate') {
                            const url = (act.url || act.action_url || '').replace('{id}', rowId);
                            return `<li><a class="dropdown-item" href="${url}">
                                <i class="${act.icon} align-bottom me-2 text-muted"></i> ${act.label}
                            </a></li>`;
                        }
                        if ((act.action === 'api-call' && act.method === 'DELETE') || act.action === 'delete') {
                            const url = (act.url || act.action_url || '').replace('{id}', rowId);
                            const msg = act.confirm_message || '¿Estás seguro de eliminar este registro?';
                            return `<li><a class="dropdown-item" href="javascript:void(0)" onclick="window.deleteItem(event, '${url}', '${msg}')">
                                <i class="${act.icon} align-bottom me-2 text-muted text-danger"></i> ${act.label}
                            </a></li>`;
                        }
                        return '';
                    }).join('');

                    return gridjs.html(`
                        <div class="dropdown">
                            <button class="btn btn-soft-secondary btn-sm" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-more-fill"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">${dropdownItems}</ul>
                        </div>
                    `);
                }
            });
        }

        const grid = new gridjs.Grid({
            columns: gridColumns,
            search: true,
            sort: true,
            pagination: { limit: 10 },
            className: { table: 'table table-nowrap table-sm align-middle mb-0' },
            data: async () => {
                if (container.dataset.rowsB64) {
                    try {
                        const preloadedRows = JSON.parse(safeAtob(container.dataset.rowsB64));
                        if (Array.isArray(preloadedRows) && preloadedRows.length > 0) {
                            return preloadedRows.map(item => gridColumns.map(col => item[col.id]));
                        }
                    } catch (e) { }
                }
                if (container.dataset.url && container.dataset.url !== 'undefined') {
                    const rawData = await fetchGridDataForGridJs(container, currentParams);
                    return rawData.map(item => gridColumns.map(col => item[col.id]));
                }
                return [];
            }
        });

        container.innerHTML = '';
        grid.render(container);
        window.gridInstances[gridId] = grid;

        // Filters logic... (Condensed for now, can be modularized further)
        const filters = JSON.parse(filtersStr);
        if (filters.length > 0) {
            setTimeout(async () => {
                const gridHead = container.querySelector('.gridjs-head');
                if (gridHead) {
                    gridHead.style.display = 'flex';
                    gridHead.style.justifyContent = 'space-between';
                    gridHead.style.alignItems = 'center';
                    gridHead.style.gap = '15px';
                    gridHead.style.padding = '12px 12px';
                    const filterWrapper = document.createElement('div');
                    filterWrapper.className = 'd-flex gap-2 flex-grow-1 align-items-center';
                    gridHead.prepend(filterWrapper);

                    for (const filter of filters) {
                        try {
                            const r = await fetch(`${API_BASE_URL}${filter.source}`, {
                                headers: { 'Authorization': `Bearer ${localStorage.getItem('access_token')}` }
                            });
                            const options = await r.json();
                            const select = document.createElement('select');
                            select.className = 'form-select form-select-sm';
                            select.style.minWidth = '150px';
                            select.innerHTML = `<option value="">${filter.label}</option>` +
                                options.map(o => `<option value="${o.id}">${o.name}</option>`).join('');
                            select.addEventListener('change', async (e) => {
                                const val = e.target.value;
                                if (val) currentParams[filter.key] = val;
                                else delete currentParams[filter.key];
                                grid.updateConfig({
                                    data: async () => {
                                        const rawData = await fetchGridDataForGridJs(container, currentParams);
                                        return rawData.map(item => gridColumns.map(col => item[col.id]));
                                    }
                                }).forceRender();
                            });
                            filterWrapper.appendChild(select);
                        } catch (e) { }
                    }
                }
            }, 0);
        }
    });
}

/**
 * Specialized hydration for the Leads Control Panel.
 */
export function hydrateLeadsControlGrid() {
    const grids = document.querySelectorAll('.js-grid-leads-control');
    grids.forEach(el => {
        if (el.dataset.initialized) return;
        el.dataset.initialized = "true";

        const dataUrl = el.dataset.url;
        const columns = JSON.parse(el.dataset.columns || '[]');
        const actions = JSON.parse(el.dataset.actions || '[]');
        const preloadedRowsB64 = el.dataset.rowsB64;

        let preloadedRows = [];
        if (preloadedRowsB64) {
            try { preloadedRows = JSON.parse(safeAtob(preloadedRowsB64)); } catch (e) { }
        }

        const gridColumns = [{ id: 'id', name: 'ID', hidden: true }];

        columns.forEach(col => {
            gridColumns.push({
                id: col.id || col.key,
                name: col.label,
                width: col.width || 'auto',
                sort: {
                    compare: (a, b) => {
                        const valA = (typeof a === 'object') ? (a.score || 0) : (parseInt(a) || 0);
                        const valB = (typeof b === 'object') ? (b.score || 0) : (parseInt(b) || 0);
                        if (valA > valB) return 1;
                        if (valA < valB) return -1;
                        return 0;
                    }
                },
                formatter: (cell, row) => {
                    if (cell === undefined || cell === null) return '-';

                    // Scoring Pillar: Linkable Pill instead of static Icon/Label
                    if (col.type === 'scoring-pillar') {
                        const score = (typeof cell === 'object') ? (cell.score || 0) : (parseInt(cell) || 0);
                        const label = (typeof cell === 'object') ? (cell.label || '-') : '-';
                        const colorClass = (typeof cell === 'object') ? (cell.color || 'thermal-none') : 'thermal-none';
                        const rowId = row.cells[0].data;

                        return gridjs.html(`
                            <div class="text-center" title="Score: ${score}">
                                <a href="/leads/${rowId}" class="thermal-pill ${colorClass}">${label}</a>
                            </div>
                        `);
                    }

                    if (col.type === 'gauge-identity') {
                        const score = (typeof cell === 'object') ? (cell.score || 0) : (parseInt(cell) || 0);
                        const name = (typeof cell === 'object') ? (cell.name || 'S/N') : '';
                        const rowId = row.cells[0].data;

                        let color = '#475569';
                        if (score >= 90) color = '#f06548'; // Danger
                        else if (score >= 70) color = '#f7b84b'; // Warning
                        else if (score >= 50) color = '#4b38b3'; // Primary
                        else if (score >= 20) color = '#0ab39c'; // Success

                        const r = 18;
                        const c = 2 * Math.PI * r;
                        const offset = c - (score / 100) * c;

                        return gridjs.html(`
                            <a href="/leads/${rowId}" class="d-flex align-items-center text-decoration-none shadow-none">
                                <div class="me-3 position-relative" style="width: 44px; height: 44px; cursor: pointer;">
                                    <svg width="44" height="44" viewBox="0 0 44 44">
                                        <circle cx="22" cy="22" r="${r}" fill="none" stroke="currentColor" stroke-width="3" style="opacity: 0.1"></circle>
                                        <circle cx="22" cy="22" r="${r}" fill="none" stroke="${color}" stroke-width="3" 
                                            stroke-dasharray="${c}" stroke-dashoffset="${offset}" 
                                            stroke-linecap="round" transform="rotate(-90 22 22)"></circle>
                                        <text x="50%" y="50%" text-anchor="middle" dy=".3em" font-size="11" font-weight="600" fill="currentColor">${score}</text>
                                    </svg>
                                </div>
                                <div><h6 class="mb-0 fs-14 fw-medium text-body">${name}</h6></div>
                            </a>
                        `);
                    }

                    if (col.type === 'badge') {
                        const label = (typeof cell === 'object') ? (cell.label || cell.name || JSON.stringify(cell)) : cell;
                        const color = (typeof cell === 'object' && cell.color) ? cell.color : (col.color || 'primary');
                        return gridjs.html(`<span class="badge bg-${color}-subtle text-${color} border border-${color}-subtle px-2 py-1">${label}</span>`);
                    }

                    return cell;
                }
            });
        });

        if (actions.length > 0) {
            gridColumns.push({
                name: 'Acciones',
                sort: false,
                formatter: (_, row) => {
                    const idColIndex = gridColumns.findIndex(c => c.id === 'id');
                    const rowId = row.cells[idColIndex].data;
                    return gridjs.html(`
                        <div class="dropdown">
                            <button class="btn btn-icon btn-sm btn-ghost-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-more-2-fill fs-14"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/leads/${rowId}"><i class="ri-eye-line align-middle me-2"></i>Ver Perfil</a></li>
                                <li><a class="dropdown-item" href="/leads/${rowId}/chat"><i class="ri-message-3-line align-middle me-2"></i>Abrir Chat</a></li>
                            </ul>
                        </div>
                    `);
                }
            });
        }

        const grid = new gridjs.Grid({
            columns: gridColumns,
            sort: true,
            pagination: { limit: 10 },
            style: {
                table: { 'white-space': 'nowrap' },
                th: { 'background-color': 'var(--vz-light)', 'color': 'var(--vz-body-color)', 'font-weight': '600', 'border-color': 'var(--vz-border-color)' }
            },
            data: async () => {
                if (preloadedRows.length > 0) return preloadedRows.map(item => gridColumns.map(col => item[col.id]));
                if (dataUrl) {
                    const rawData = await fetchGridDataForGridJs(el);
                    return rawData.map(item => gridColumns.map(col => item[col.id]));
                }
                return [];
            }
        });

        el.innerHTML = '';
        grid.render(el);
    });
}

export async function refreshGrids() {
    const grids = Object.values(window.gridInstances);
    if (grids.length > 0) {
        grids.forEach(grid => grid.forceRender());
    }
}

window.refreshGrids = refreshGrids;
