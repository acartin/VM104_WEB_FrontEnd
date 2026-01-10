/**
 * AI-First Dumb Renderer Engine
 * Strictly follows 'visual_dictionary.json' and 'catalog_context.json'
 */

import { LinkAppShell } from '../components/layout/AppShell.js';
import { LinkMetricCard } from '../components/cards/MetricCard.js';
import { LinkGridContainer } from '../components/grids/Grid.js';
import { LinkTypography } from '../components/ui/Typography.js';
import { LinkButtonGroup } from '../components/ui/ButtonGroup.js';
import { LinkSidebar } from '../components/layout/Sidebar.js';
import { LinkNavbar } from '../components/layout/Navbar.js';
import { LinkGridVisual } from '../components/grids/GridVisual.js';
import { LinkLeadControlGrid } from '../components/grids/LeadControlGrid.js';
import { LinkTabs } from '../components/ui/Tabs.js';
import { LinkModalForm, renderInput, renderFormFromSchema } from '../components/forms/ModalForm.js';
import { LinkRow, LinkCol } from '../components/layout/Layout.js';
import { LinkProjectBanner } from '../components/layout/ProjectBanner.js';
import { LinkMemberListCard, LinkGenericCard, LinkFileGrid, LinkContactListDetailed } from '../components/cards/DashboardWidgets.js?v=52';
import { safeBtoa, safeAtob } from '../utils/base64.js';

const API_BASE_URL = window.AppConfig.API_BASE_URL; // Loaded from config.js

const RENDERER_VERSION = "62";
console.log(`[Renderer] v${RENDERER_VERSION} Initializing...`);


window.appState = {
    currentPath: null
};

async function init() {
    // We target body for full shell replacement, or app-root for partials
    const appRoot = document.getElementById('app-root');

    try {
        // Fetch UI Schema from Backend
        const token = localStorage.getItem('access_token');
        const headers = {};
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const response = await fetch(`${API_BASE_URL}/app-init`, {
            headers: headers
        });

        if (response.status === 401) {
            // Token expired or invalid
            window.location.href = 'login.html';
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const appData = await response.json();

        if (appData.layout === 'dashboard-shell') {
            // Full Shell Render: Replace existing layout-wrapper

            // Pre-process content to avoid circular dependencies
            if (appData.content) {
                appData.contentHtml = renderContent(appData.content);
            }

            const shellHtml = LinkAppShell(appData);

            // If explicit layout-wrapper exists, replace it. Otherwise append to body.
            const existingWrapper = document.getElementById('layout-wrapper');
            if (existingWrapper) {
                existingWrapper.outerHTML = shellHtml;
            } else {
                document.body.insertAdjacentHTML('afterbegin', shellHtml);
            }

            // Initialize Theme Switcher Logic
            setupThemeSwitcher();

            // Populate Sidebar/Header Profile (New)
            updateHeaderProfile();

            // Initialize SPA Navigation
            setupNavigation();

            // Check for Initial Route (e.g. /countries on refresh)
            const currentPath = window.location.pathname;
            if (currentPath && currentPath !== '/' && currentPath !== '/index.html') {
                // Trigger navigation manually
                navigateTo(currentPath);
            } else {
                // If root, maybe load dashboard default content? 
                // Currently app-init returns default content, so we are fine.
                hydrateGrids();
                hydrateLeadsControlGrid();
            }

        } else if (appRoot) {
            // Partial Render logic (likely unused if we always do full shell init check)
            appRoot.innerHTML = renderContent(appData.components);
            hydrateGrids();
            hydrateLeadsControlGrid();
        }

    } catch (error) {
        console.error('Render Error:', error);
        if (appRoot) {
            appRoot.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        }
    }
}

/**
 * Handles navigation to a specific path using the Backend SDUI API.
 * @param {string} href - The path to navigate to (e.g. /countries)
 * @param {boolean} pushState - Whether to push state to history (default true)
 */
export async function navigateTo(href, pushState = true) {
    if (!href || href === '#' || href.startsWith('#')) return;

    window.appState.currentPath = href;
    const pageRoot = document.getElementById('page-root');
    if (!pageRoot) return; // Should exist after Shell Render

    // Show Loading
    pageRoot.innerHTML = `
            <div class="text-center mt-5">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    `;

    try {
        // Fetch View Data
        const token = localStorage.getItem('access_token');
        const headers = {};
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const response = await fetch(`${API_BASE_URL}${href}`, {
            headers: headers
        });

        if (!response.ok) throw new Error(`View not found (${response.status})`);

        const viewData = await response.json();

        // Render New Content
        if (viewData.layout === 'dashboard-project-overview') {
            const bannerHtml = LinkProjectBanner(viewData);

            let tabsContentHtml = '';
            if (viewData.tabs) {
                tabsContentHtml = viewData.tabs.map(tab => {
                    const activeClass = tab.active ? 'show active' : '';
                    const content = renderContent(tab.components);
                    return `
                        <div class="tab-pane fade ${activeClass}" id="${tab.id}" role="tabpanel">
                            ${content}
                        </div>
                    `;
                }).join('');
            } else {
                // Fallback for legacy single-view
                const contentHtml = renderContent(viewData.components);
                tabsContentHtml = `
                    <div class="tab-pane fade show active" id="project-overview" role="tabpanel">
                        ${contentHtml}
                    </div>
                `;
            }

            pageRoot.innerHTML = `
                ${bannerHtml}
                <div class="tab-content text-muted mt-3">
                    ${tabsContentHtml}
                </div>
            `;
        } else if (viewData.components) {
            pageRoot.innerHTML = renderContent(viewData.components);
        }
        hydrateGrids();
        hydrateLeadsControlGrid();

        // Update URL without reload (History API)
        if (pushState) {
            history.pushState(null, '', href);
        }

        // Close sidebar on mobile
        document.body.classList.remove('vertical-sidebar-enable');

    } catch (error) {
        console.error('Navigation Error:', error);
        pageRoot.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    }
}

export function renderComponent(component) {
    switch (component.type) {
        case 'card-metric':
            return LinkMetricCard(component);
        case 'grid':
            return LinkGridContainer(component);
        case 'grid-visual':
            return LinkGridVisual(component);
        case 'grid-leads-control':
            return LinkLeadControlGrid(component);
        case 'typography':
            return LinkTypography(component);
        case 'button-group':
            return LinkButtonGroup(component);
        case 'tabs':
            return LinkTabs(component);
        case 'layout-row':
            return LinkRow(component);
        case 'layout-col':
            return LinkCol(component);
        case 'contact-list-detailed':
            return LinkContactListDetailed(component);
        case 'member-list-card':
            return LinkMemberListCard(component);
        case 'file-grid':
            return LinkFileGrid(component);
        case 'card':
            // Recursive rendering for generic card content
            const contentHtml = renderContent(component.components);
            return LinkGenericCard({ ...component, contentHtml });
        default:
            return `<!-- Unknown: ${component.type} -->`;
    }
}

export function renderContent(components) {
    if (components && Array.isArray(components)) {
        return `<div class="row">${components.map(c => renderComponent(c)).join('')}</div>`;
    }
    return '';
}

// --- CRUD ACTION HANDLERS ---

// Open Modal for New Country (Hardcoded schema for now)
window.openCreateCountryModal = () => {
    const formFields = `
        ${renderInput('Country Name', 'name', '', 'text')}
        ${renderInput('ISO Code', 'iso_code', '', 'text', true)}
    `;
    const modalHtml = LinkModalForm('createCountryModal', 'New Country', formFields, '/countries', 'POST');

    // Inject Modal
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHtml;
    document.body.appendChild(modalContainer);

    // Bootstrap Modal Instance
    const modalEl = document.getElementById('createCountryModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // Cleanup on hide
    modalEl.addEventListener('hidden.bs.modal', () => {
        modalContainer.remove();
    });
};

// Open Modal for Edit Country
window.openEditCountryModal = async (id) => {
    try {
        // Fetch current data
        const token = localStorage.getItem('access_token');
        const headers = token ? { 'Authorization': `Bearer ${token}` } : {};
        const res = await fetch(`${API_BASE_URL}/countries/${id}`, { headers });
        const data = await res.json();

        const formFields = `
             ${renderInput('Country Name', 'name', data.name, 'text')}
             ${renderInput('ISO Code', 'iso_code', data.iso_code, 'text', true)}
        `;
        const modalHtml = LinkModalForm('editCountryModal', 'Edit Country', formFields, `/countries/${id}`, 'PUT');

        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = modalHtml;
        document.body.appendChild(modalContainer);

        const modal = new bootstrap.Modal(document.getElementById('editCountryModal'));
        modal.show();

        document.getElementById('editCountryModal').addEventListener('hidden.bs.modal', () => modalContainer.remove());

    } catch (e) {
        alert('Error fetching data');
    }
};

// Handle Form Submission
// Handle Form Submission
window.submitModalForm = async (formId, actionUrl, method) => {
    const form = document.getElementById(formId);
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const payload = {};

    // Standard fields
    for (const [key, value] of formData.entries()) {
        payload[key] = value;
    }

    // Explicitly handle checkboxes (booleans)
    // Because unchecked checkboxes are missing from FormData
    const checkboxes = form.querySelectorAll('input[type="checkbox"]:not(.primary-radio)');
    checkboxes.forEach(cb => {
        payload[cb.name] = cb.checked;
    });

    // Handle Repeater Fields
    const repeaters = form.querySelectorAll('.repeater-container');
    repeaters.forEach(rep => {
        const name = rep.dataset.name;
        const items = [];
        const rows = rep.querySelectorAll('.repeater-item');
        rows.forEach(row => {
            const catId = row.querySelector('.category-select').value;
            const val = row.querySelector('.value-input').value;
            const isPrimary = row.querySelector('.primary-radio').checked;
            if (catId && val) {
                items.push({
                    category_id: parseInt(catId),
                    value: val,
                    is_primary: isPrimary
                });
            }
        });
        payload[name] = items;
    });

    try {
        const res = await fetch(`${API_BASE_URL}${actionUrl}`, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('access_token')}`
            },
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            // Success
            const modalEl = form.closest('.modal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            // Refresh Grid
            refreshGrids();

            // SweetAlert Success
            Swal.fire({
                title: "Good job!",
                text: "Data saved successfully!",
                icon: "success",
                showCancelButton: false,
                customClass: { confirmButton: 'btn btn-primary w-xs me-2 mt-2' },
                buttonsStyling: false,
                showCloseButton: true
            });

        } else {
            // Error Handling
            let errorTitle = "Oops...";
            let errorText = "Something went wrong saving data!";

            // Handle 422 Validation Errors
            if (res.status === 422) {
                const errorData = await res.json();
                console.warn('Validation Details:', errorData);

                if (errorData.detail && Array.isArray(errorData.detail)) {
                    // Extract messages from Pydantic structure
                    const messages = errorData.detail.map(err => {
                        const field = err.loc[err.loc.length - 1]; // "name" or "iso_code"
                        return `<b>${field}:</b> ${err.msg}`;
                    }).join('<br>');

                    errorTitle = "Validation Error";
                    errorText = messages; // We will use 'html' property in Swal
                }
            }

            Swal.fire({
                title: errorTitle,
                html: errorText, // Use html property to render <br>
                icon: "error",
                customClass: { confirmButton: 'btn btn-primary w-xs mt-2' },
                buttonsStyling: false,
                showCloseButton: true
            });
        }
    } catch (e) {
        console.error(e);
        Swal.fire({
            title: "System Error",
            text: e.message,
            icon: "error",
            customClass: { confirmButton: 'btn btn-primary w-xs mt-2' },
            buttonsStyling: false
        });
    }
};

// Delete Action
window.deleteItem = async (event, url, confirmMsg) => {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        customClass: {
            confirmButton: 'btn btn-primary w-xs me-2 mt-2',
            cancelButton: 'btn btn-danger w-xs mt-2'
        },
        confirmButtonText: "Yes, delete it!",
        buttonsStyling: false,
        showCloseButton: true
    }).then(async function (result) {
        if (result.value) {
            try {
                const token = localStorage.getItem('access_token');
                const headers = token ? { 'Authorization': `Bearer ${token}` } : {};
                const res = await fetch(`${API_BASE_URL}${url}`, { method: 'DELETE', headers });
                if (res.ok) {
                    refreshGrids();
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your file has been deleted.",
                        icon: "success",
                        customClass: { confirmButton: 'btn btn-primary w-xs mt-2' },
                        buttonsStyling: false
                    });
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: "Failed to delete item.",
                        icon: "error",
                        customClass: { confirmButton: 'btn btn-primary w-xs mt-2' },
                        buttonsStyling: false
                    });
                }
            } catch (e) {
                Swal.fire({
                    title: "System Error!",
                    text: "Could not connect to server.",
                    icon: "error",
                    customClass: { confirmButton: 'btn btn-primary w-xs mt-2' },
                    buttonsStyling: false
                });
            }
        }
    });
};


// (Moved to top)

// Generic Modal Opener
window.openGenericModal = async (schema, url, method, title, data = {}) => {
    console.log('Opening Generic Modal:', { title, url, method });
    const formFields = renderFormFromSchema(schema, data);
    const modalId = `modal-${Math.random().toString(36).substr(2, 9)}`;

    const modalHtml = LinkModalForm(modalId, title, formFields, url, method);

    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHtml;
    document.body.appendChild(modalContainer);

    const modalEl = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    modalEl.addEventListener('hidden.bs.modal', () => {
        modalContainer.remove();
    });

    // Initial Hydration
    await hydrateModalSelects(modalEl);
};

window.hydrateSelect = async (select) => {
    const url = select.dataset.source;
    if (!url) return;
    const initialValue = select.dataset.value;

    try {
        const token = localStorage.getItem('access_token');
        const headers = token ? { 'Authorization': `Bearer ${token}` } : {};
        const res = await fetch(`${API_BASE_URL}${url}`, { headers });
        const items = await res.json();

        // Clear and populate
        const placeholder = select.querySelector('option[value=""]') ? select.querySelector('option[value=""]').innerText : 'Select...';
        select.innerHTML = `<option value="">${placeholder}</option>`;

        items.forEach(item => {
            const selected = (String(item.id) === String(initialValue)) ? 'selected' : '';
            select.insertAdjacentHTML('beforeend', `<option value="${item.id}" ${selected}>${item.name || item.label}</option>`);
        });
    } catch (e) {
        console.error('Error hydrating select:', e);
    }
};

async function hydrateModalSelects(modalEl) {
    const selects = modalEl.querySelectorAll('select[data-source]');
    for (const select of selects) {
        await window.hydrateSelect(select);
    }
}

// Global helper for repeater items
window.addRepeaterItem = async (name, source) => {
    const container = document.querySelector(`#repeater-${name} .repeater-list`);
    const itemHtml = `
        <div class="repeater-item d-flex gap-2 mb-2 align-items-center">
            <select class="form-select form-select-sm category-select" style="width: 140px;" data-source="${source}">
                <option value="">Category...</option>
            </select>
            <input type="text" class="form-control form-control-sm value-input" placeholder="Value...">
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input primary-radio" type="radio" name="${name}_primary">
            </div>
            <button type="button" class="btn btn-ghost-danger btn-icon btn-sm remove-item" onclick="this.closest('.repeater-item').remove()">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
    const newSelect = container.lastElementChild.querySelector('.category-select');
    await window.hydrateSelect(newSelect);
};

// Generic Edit Handler (Fetch + Open Modal)
window.handleEditAction = async (event, id, urlPattern, schemaStr) => {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    console.log('Handle Edit Action:', { id, urlPattern });
    const fetchUrl = urlPattern.replace('{id}', id);

    // Robust Schema Decoding (Base64 or Raw JSON)
    let schema = [];
    try {
        // Try decoding Base64 first
        schema = JSON.parse(safeAtob(schemaStr));
    } catch (e) {
        // Fallback: Try parsing as raw JSON (legacy support)
        try {
            schema = JSON.parse(schemaStr);
        } catch (e2) {
            console.error("Invalid Schema Format:", e2);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Invalid Form Schema.' });
            return;
        }
    }

    try {
        const token = localStorage.getItem('access_token');
        const headers = token ? { 'Authorization': `Bearer ${token}` } : {};
        const res = await fetch(`${API_BASE_URL}${fetchUrl}`, { headers });
        if (!res.ok) throw new Error("Failed to fetch data");
        const data = await res.json();

        const updateUrl = fetchUrl; // Standard REST: GET /items/1 -> PUT /items/1
        window.openGenericModal(schema, updateUrl, 'PUT', 'Editar registro', data);
    } catch (e) {
        console.error('Edit Fetch Error:', e);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Could not fetch data for editing.' });
    }
};
// Generic Modal Action (Open Modal directly without GET)
window.handleActionModal = (event, id, urlPattern, schemaStr, method = 'POST', title = 'Action') => {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    console.log('Handle Action Modal:', { id, urlPattern, method });
    const actionUrl = urlPattern.replace('{id}', id);

    let schema = [];
    try {
        schema = JSON.parse(safeAtob(schemaStr));
    } catch (e) {
        schema = JSON.parse(schemaStr);
    }

    window.openGenericModal(schema, actionUrl, method, title);
};

// --- HYDRATION ---
// Handle Generic Actions (e.g. Header Buttons)
window.handleGenericAction = (btn) => {
    console.log('Generic Action Clicked', btn);
    const action = btn.dataset.action;
    const url = btn.dataset.url;
    const title = btn.dataset.title;
    const method = btn.dataset.method || 'POST';
    const schemaStr = btn.dataset.schema;

    console.log('Action Data:', { action, url, title, schemaStr });

    if (action === 'modal-form' && schemaStr) {
        try {
            // Check if it's base64 (common for dynamic SDUI) or raw JSON
            let schema = [];
            try {
                schema = JSON.parse(safeAtob(schemaStr));
            } catch (e) {
                schema = JSON.parse(schemaStr);
            }
            openGenericModal(schema, url, method, title);
        } catch (e) {
            console.error('Schema Parse Error:', e);
            alert('Error parsing form schema');
        }
    } else {
        console.warn('Missing action or schema');
    }
};
// Reusable Data Fetcher
// --- HYDRATION ---
window.gridInstances = {}; // Registry to allow refreshing

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
        const gridId = container.id;
        const columns = JSON.parse(container.dataset.columns || '[]');
        const actions = JSON.parse(container.dataset.actions || '[]');
        const schemaStr = container.dataset.schema || '[]';
        const filtersStr = container.dataset.filters || '[]';
        const currentParams = {};

        // Prepare Columns for Grid.js
        const gridColumns = [
            { id: 'id', name: 'ID', hidden: true }
        ];

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
                    // Find ID column by its 'id' property in the gridColumns definition
                    const idColIndex = gridColumns.findIndex(c => c.id === 'id');
                    const rowId = row.cells[idColIndex].data;

                    const dropdownItems = actions.map(act => {
                        // Support both 'modal-form' and 'edit' as action types
                        if (act.action === 'modal-form' || act.action === 'edit') {
                            // If schema is already provided in the action (base64 from backend), use it.
                            // Otherwise fallback to the main grid schema (which we encode to be safe)
                            let schemaToPass = "";
                            if (act.schema) {
                                schemaToPass = (typeof act.schema === 'string') ? act.schema : safeBtoa(JSON.stringify(act.schema));
                            } else {
                                schemaToPass = btoa(schemaStr);
                            }

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

                        // Support 'api-call' (standard) and 'delete' (shortcut)
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
                            <ul class="dropdown-menu dropdown-menu-end">
                                ${dropdownItems}
                            </ul>
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
            style: {
                table: { 'white-space': 'nowrap' }
            },
            className: {
                table: 'table table-nowrap table-sm align-middle mb-0',
                //thead: 'table-light' // Removed to allow Velzon theme to control headers
            },
            data: async () => {
                // 1. Try Base64 preloaded rows first (if they have data)
                if (container.dataset.rowsB64) {
                    try {
                        const preloadedRows = JSON.parse(safeAtob(container.dataset.rowsB64));
                        if (Array.isArray(preloadedRows) && preloadedRows.length > 0) {
                            return preloadedRows.map(item => gridColumns.map(col => item[col.id]));
                        }
                    } catch (e) {
                        console.error('Error decoding rows-b64:', e);
                    }
                }
                // 2. Try plain JSON preloaded rows (legacy/fallback if they have data)
                if (container.dataset.rows) { // Removed `&& container.dataset.rows !== '[]'` as Array.isArray and length check handles it
                    try {
                        const preloadedRows = JSON.parse(container.dataset.rows);
                        if (Array.isArray(preloadedRows) && preloadedRows.length > 0) {
                            return preloadedRows.map(item => gridColumns.map(col => item[col.id]));
                        }
                    } catch (e) {
                        console.error('Error parsing rows:', e);
                    }
                }
                // 3. Fallback to API Fetch if URL is defined
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

        // Setup Filters - Injected into Grid.js Header
        const filters = JSON.parse(filtersStr);

        // Wait a tick for Grid.js to create the .gridjs-head
        setTimeout(async () => {
            const gridHead = container.querySelector('.gridjs-head');
            if (gridHead && filters.length > 0) {
                // Style Header as Flex
                gridHead.style.display = 'flex';
                gridHead.style.justifyContent = 'space-between';
                gridHead.style.alignItems = 'center';
                gridHead.style.gap = '15px';
                gridHead.style.padding = '12px 12px';

                // Create Filter Wrapper (Left Side)
                const filterWrapper = document.createElement('div');
                filterWrapper.className = 'd-flex gap-2 flex-grow-1 align-items-center';
                gridHead.prepend(filterWrapper);

                for (const filter of filters) {
                    const headers = { 'Authorization': `Bearer ${localStorage.getItem('access_token')}` };
                    try {
                        const r = await fetch(`${API_BASE_URL}${filter.source}`, { headers });
                        const options = await r.json();

                        const select = document.createElement('select');
                        select.className = 'form-select form-select-sm';
                        select.style.width = 'auto'; // Width based on content or standard sm
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
                    } catch (e) {
                        console.error('Filter Hydration Error:', e);
                    }
                }
            }
        }, 0);
    });
}

function setupThemeSwitcher() {
    const btn = document.querySelector('.light-dark-mode');
    if (!btn) return;

    btn.addEventListener('click', () => {
        const html = document.documentElement;
        // Check current attribute (Velzon uses data-bs-theme or data-layout-mode)
        const currentMode = html.getAttribute('data-bs-theme') || 'light';
        const newMode = currentMode === 'light' ? 'dark' : 'light';

        // Set attributes for Bootstrap 5.3+ and Velzon
        html.setAttribute('data-bs-theme', newMode);
        html.setAttribute('data-layout-mode', newMode); // Legacy support

        // Save preference
        localStorage.setItem('theme-mode', newMode);
    });

    // Load preference on init
    const savedMode = localStorage.getItem('theme-mode');
    if (savedMode) {
        document.documentElement.setAttribute('data-bs-theme', savedMode);
        document.documentElement.setAttribute('data-layout-mode', savedMode);
    }
}

function setupNavigation() {
    // Click Interception
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a.nav-link');
        if (!link) return;

        const href = link.getAttribute('href');
        // Ignore toggles (starting with # usually)
        if (!href || href === '#' || href.startsWith('#')) return;

        e.preventDefault();
        navigateTo(href, true);
    });

    // Browser Back/Forward Handling
    window.addEventListener('popstate', () => {
        const path = window.location.pathname;
        if (path) navigateTo(path, false);
    });
}

/**
 * Reads user profile from localStorage and updates the Navbar.
 * Displays User Name and Client Name (Coca Cola).
 */
function updateHeaderProfile() {
    try {
        const profileStr = localStorage.getItem('user_profile');
        if (!profileStr) return;

        const profile = JSON.parse(profileStr);

        // Update User Name
        const nameEl = document.getElementById('header-user-name');
        if (nameEl && profile.name) {
            nameEl.textContent = profile.name; // e.g. "Alvaro Cartin"
        }

        // Update Tenant Name
        const tenantEl = document.getElementById('header-tenant-name');
        if (tenantEl) {
            if (profile.is_superuser) {
                tenantEl.textContent = 'Global Administrator';
            } else if (profile.tenants && profile.tenants.length > 0) {
                const tenant = profile.tenants[0];
                if (tenant.client && tenant.client.name) {
                    tenantEl.textContent = `Client Admin - ${tenant.client.name}`;
                } else {
                    tenantEl.textContent = 'Client Admin';
                }
            }
        }
    } catch (e) {
        console.warn('Failed to update header profile:', e);
    }
}

export async function refreshGrids() {
    const grids = Object.values(window.gridInstances);
    if (grids.length > 0) {
        grids.forEach(grid => grid.forceRender());
    } else {
        // If no grids (e.g. Dashboard View), reload the whole view
        await navigateTo(window.location.pathname);
    }
}

// Start Engine
document.addEventListener('DOMContentLoaded', init);

/**
 * Specialized hydration for the Leads Control Panel.
 * Uses Grid.js but with custom high-fidelity cell renderers.
 */
function hydrateLeadsControlGrid() {
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
            try {
                preloadedRows = JSON.parse(safeAtob(preloadedRowsB64));
            } catch (e) {
                console.error("Error decoding preloaded rows:", e);
            }
        }

        // Prepare Columns for Grid.js
        const gridColumns = [
            { id: 'id', name: 'ID', hidden: true }
        ];

        columns.forEach(col => {
            gridColumns.push({
                id: col.id || col.key,
                name: col.label,
                sort: col.sortable !== false,
                formatter: (cell) => {
                    if (cell === undefined || cell === null) return '-';

                    if (col.type === 'gauge-identity') {
                        const score = (typeof cell === 'object') ? (cell.score || 0) : (parseInt(cell) || 0);
                        const name = (typeof cell === 'object') ? (cell.name || 'S/N') : '';

                        // Thermal Color Logic
                        let color = '#475569';
                        if (score >= 90) color = '#ef4444';
                        else if (score >= 70) color = '#f97316';
                        else if (score >= 50) color = '#10b981';
                        else if (score >= 20) color = '#f59e0b';

                        const r = 18;
                        const c = 2 * Math.PI * r;
                        const offset = c - (score / 100) * c;

                        return gridjs.html(`
                            <div class="d-flex align-items-center">
                                <div class="me-3 position-relative" style="width: 44px; height: 44px;">
                                    <svg width="44" height="44" viewBox="0 0 44 44">
                                        <circle cx="22" cy="22" r="${r}" fill="none" stroke="#e9ebec" stroke-width="3"></circle>
                                        <circle cx="22" cy="22" r="${r}" fill="none" stroke="${color}" stroke-width="3" 
                                            stroke-dasharray="${c}" stroke-dashoffset="${offset}" 
                                            stroke-linecap="round" transform="rotate(-90 22 22)"></circle>
                                        <text x="50%" y="50%" text-anchor="middle" dy=".3em" font-size="11" font-weight="600" fill="#495057">${score}</text>
                                    </svg>
                                </div>
                                <div>
                                    <h6 class="mb-0 fs-14 fw-medium text-dark">${name}</h6>
                                </div>
                            </div>
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
                            <button class="btn btn-soft-secondary btn-sm" data-bs-toggle="dropdown">
                                <i class="ri-more-fill"></i>
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
                th: { 'background-color': '#f3f6f9', 'color': '#495057', 'font-weight': '600' }
            },
            data: async () => {
                // 1. Try preloaded rows
                if (preloadedRows.length > 0) {
                    return preloadedRows.map(item => gridColumns.map(col => item[col.id]));
                }
                // 2. Fetch from API
                if (dataUrl) {
                    const rawData = await fetchGridDataForGridJs(el); // Pass the element 'el'
                    return rawData.map(item => gridColumns.map(col => item[col.id]));
                }
                return [];
            }
        });

        el.innerHTML = '';
        grid.render(el);
    });
}
