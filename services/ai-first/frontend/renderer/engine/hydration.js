/**
 * Hydration Manager
 * Handles the initialization of interactive components (Grids, etc.)
 */

import { safeAtob, safeBtoa } from '../../utils/base64.js';
import { formatters } from './formatters.js';
import { CustomLeadsGrid } from './CustomGrid.js'; // Import new engine
import { GridFilters } from './GridFilters.js'; // Import GridFilters module

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
    // 1. Standard Grid.js Instances
    const grids = document.querySelectorAll('.js-grid-visual');
    if (grids.length > 0) {
        // ... (Existing Grid.js logic) ...
        _hydrateStandardGrids(grids);
    }

    // 2. Custom Grid Instances (Beta)
    const customGrids = document.querySelectorAll('[data-type="custom-leads-grid"]');
    customGrids.forEach(container => {
        if (container.dataset.initialized) return;
        container.dataset.initialized = "true";

        console.log("Hydrating Custom Grid Beta:", container.id);
        const config = {
            grid_id: container.dataset.gridId || 'default',
            data_url: container.dataset.url,
            columns: JSON.parse(container.dataset.columns || '[]'),
            actions: JSON.parse(container.dataset.actions || '[]'),
            enableFilters: container.dataset.enableFilters === 'true',
            filterConfig: JSON.parse(container.dataset.filterConfig || '{}')
        };

        console.log('[Hydration] Grid config:', config);

        // Initialize Engine
        window.gridInstances[container.id] = new CustomLeadsGrid(container, config);
    });
}

// Renamed internal helper to keep code clean (Refactoring existing hydrateGrids logic)
async function _hydrateStandardGrids(grids) {
    grids.forEach(async (container) => {
        if (container.dataset.initialized) return;
        // ... rest of existing code ...
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

                    // Use external formatters
                    if (col.type === 'badge' && formatters.badge) {
                        return formatters.badge(cell, col);
                    }
                    if (col.truncate && formatters.truncate) {
                        return formatters.truncate(cell, col);
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

        // Filters logic
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
                        return valA - valB;
                    }
                },
                formatter: (cell, row) => {
                    if (cell === undefined || cell === null) return '-';

                    // Use external formatters
                    if (col.type === 'scoring-pillar' && formatters.scoringPillar) {
                        return formatters.scoringPillar(cell, row, gridColumns);
                    }
                    if (col.type === 'gauge-identity' && formatters.gaugeIdentity) {
                        return formatters.gaugeIdentity(cell, row, gridColumns);
                    }
                    if (col.type === 'badge' && formatters.badge) {
                        return formatters.badge(cell, col);
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
            style: {
                table: { 'white-space': 'nowrap' },
                th: { 'background-color': 'var(--vz-light)', 'color': 'var(--vz-body-color)', 'font-weight': '600', 'border-color': 'var(--vz-border-color)' }
            },
            pagination: {
                limit: 10
            },
            sort: {
                multiColumn: false
            },
            data: () => {
                if (!dataUrl) return Promise.resolve([]);
                return fetch(`${API_BASE_URL}${dataUrl}`, {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('access_token')}` }
                })
                    .then(res => res.json())
                    .then(data => data.map(item => gridColumns.map(col => item[col.id])));
            }
        });

        el.innerHTML = '';
        // Debug Visualizer with Atomic Copy
        const debugId = `debug-${el.id}`;
        if (!document.getElementById(debugId)) {
            const debugEl = document.createElement('div');
            debugEl.id = debugId;
            debugEl.style.cssText = "background: #ffecb3; padding: 8px; font-size: 11px; margin-bottom: 5px; border: 1px solid #ffca28; color: #333; font-family: monospace; display: flex; justify-content: space-between; align-items: center;";
            debugEl.innerHTML = `
                <span id="${debugId}-text"><strong>DEBUG MODE:</strong> Waiting...</span>
                <button onclick="window.copyDebugInfo('${debugId}-text')" style="background:#00bd9d; color:white; border:none; padding:2px 8px; font-weight:bold; cursor:pointer; font-size:10px;">COPY INFO</button>
            `;
            el.parentNode.insertBefore(debugEl, el);
        }

        // Poll grid state every 500ms
        setInterval(() => {
            const dText = document.getElementById(`${debugId}-text`);
            if (dText) {
                try {
                    const activeBtn = el.querySelector('.gridjs-pagination .gridjs-currentPage');
                    const visualPager = activeBtn ? activeBtn.innerText : 'None';
                    const firstRow = el.querySelector('tbody tr:first-child td:nth-child(2)');
                    const firstVal = firstRow ? firstRow.innerText : '-';
                    dText.innerHTML = `<strong>DEBUG:</strong> Pager: <b>${visualPager}</b> | Top Cell: <b>${firstVal}</b> | Rows: <b>${el.querySelectorAll('tbody tr').length}</b>`;
                } catch (e) {
                    dText.innerHTML = `DEBUG ERROR: ${e.message}`;
                }
            }
        }, 500);

        // Robust Sort Sync
        grid.on('sort', () => {
            // Force reset of pagination state when sorting
            const state = grid.config.store.getState();
            if (state.pagination && state.pagination.page !== 0) {
                // Explicitly update config and FORCE render to ensure pipeline re-runs
                console.log('[Grid] Sorting detected on inner page, resetting to Page 1');
                setTimeout(() => {
                    grid.updateConfig({
                        pagination: { page: 0, limit: 10 }
                    }).forceRender();
                }, 100);
            }
        });

        grid.render(el);






        // Register instance for global refreshes

        const gridId = el.id || `grid-control-${Math.random().toString(36).substr(2, 9)}`;
        window.gridInstances[gridId] = grid;
    });
}

export async function refreshGrids() {
    const grids = Object.values(window.gridInstances);
    if (grids.length > 0) {
        grids.forEach(grid => grid.forceRender());
    }
}

window.refreshGrids = refreshGrids;

// Robust Copy Function (Works on HTTP/Non-Secure)
window.copyDebugInfo = function (id) {
    const el = document.getElementById(id);
    if (!el) return;
    const text = el.innerText;

    // Method 1: Modern API (Try first)
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => showFeedback(id)).catch(() => useFallback(text, id));
    } else {
        useFallback(text, id);
    }
};

function useFallback(text, id) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-9999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        const successful = document.execCommand('copy');
        if (successful) showFeedback(id);
        else console.error('Fallback copy command failed');
    } catch (err) {
        console.error('Fallback copy failed', err);
    }
    document.body.removeChild(textArea);
}

function showFeedback(id) {
    const parent = document.getElementById(id).parentElement;
    const btn = parent ? parent.querySelector('button') : null;
    if (btn) {
        const original = btn.innerText;
        btn.innerText = 'COPIED!';
        setTimeout(() => btn.innerText = original, 2000);
    }
}
