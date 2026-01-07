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
import { LinkModalForm, renderInput, renderFormFromSchema } from '../components/forms/ModalForm.js';

const API_BASE_URL = window.AppConfig.API_BASE_URL; // Loaded from config.js

// --- STATE MANAGEMENT ---
window.appState = {
    currentPath: null
};

async function init() {
    // We target body for full shell replacement, or app-root for partials
    const appRoot = document.getElementById('app-root');

    try {
        // Fetch UI Schema from Backend
        const response = await fetch(`${API_BASE_URL}/app-init`);

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
            }

        } else if (appRoot) {
            // Partial Render logic (likely unused if we always do full shell init check)
            appRoot.innerHTML = renderContent(appData.components);
            hydrateGrids();
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
        const response = await fetch(`${API_BASE_URL}${href}`);

        if (!response.ok) throw new Error(`View not found (${response.status})`);

        const viewData = await response.json();

        // Render New Content
        if (viewData.components) {
            pageRoot.innerHTML = renderContent(viewData.components);
            hydrateGrids();
        }

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
        case 'typography':
            return LinkTypography(component);
        case 'button-group':
            return LinkButtonGroup(component);
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
        const res = await fetch(`${API_BASE_URL}/countries/${id}`);
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
    const checkboxes = form.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => {
        payload[cb.name] = cb.checked;
    });

    try {
        const res = await fetch(`${API_BASE_URL}${actionUrl}`, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            // Success
            const modalEl = form.closest('.modal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            // Refresh Grid
            hydrateGrids();

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
// Delete Action
window.deleteItem = async (url, confirmMsg) => {
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
                const res = await fetch(`${API_BASE_URL}${url}`, { method: 'DELETE' });
                if (res.ok) {
                    hydrateGrids();
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


// Generic Modal Opener
window.openGenericModal = async (schema, url, method, title, data = {}) => {
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
};

// Generic Edit Handler (Fetch + Open Modal)
window.handleEditAction = async (id, urlPattern, schemaStr) => {
    const fetchUrl = urlPattern.replace('{id}', id);

    // Robust Schema Decoding (Base64 or Raw JSON)
    let schema = [];
    try {
        // Try decoding Base64 first
        schema = JSON.parse(atob(schemaStr));
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
        const res = await fetch(`${API_BASE_URL}${fetchUrl}`);
        if (!res.ok) throw new Error("Failed to fetch data");
        const data = await res.json();

        const updateUrl = fetchUrl; // Standard REST: GET /items/1 -> PUT /items/1
        window.openGenericModal(schema, updateUrl, 'PUT', 'Edit Item', data);
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Could not fetch data for editing.' });
    }
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
            const schema = JSON.parse(schemaStr);
            openGenericModal(schema, url, method, title);
        } catch (e) {
            console.error('Schema Parse Error:', e);
            alert('Error parsing form schema');
        }
    } else {
        console.warn('Missing action or schema');
    }
};
export async function hydrateGrids() {
    const grids = document.querySelectorAll('.js-grid-visual');

    grids.forEach(async (table) => {
        const url = table.dataset.url;
        if (!url) return;

        // Metadata from DOM
        const schemaStr = table.dataset.schema || '[]';
        const columnsStr = table.dataset.columns || '[]';
        const actionsStr = table.dataset.actions || '[]';

        const columns = JSON.parse(columnsStr);
        const actions = JSON.parse(actionsStr);

        try {
            const res = await fetch(`${API_BASE_URL}${url}`);
            if (!res.ok) throw new Error('Failed');
            const data = await res.json();

            const tbody = table.querySelector('tbody');
            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="100%" class="text-center">No data found</td></tr>';
                return;
            }

            const rowsHtml = data.map(row => {
                // Generically render columns
                const colsHtml = columns.map(col => {
                    let val = row[col.key];
                    if (val === undefined || val === null) val = '-';

                    if (col.type === 'badge') {
                        // Badge Map lookup (convert bool to string key)
                        const mapKey = String(val); // "true" or "false"
                        const badgeColor = (col.badge_map && col.badge_map[mapKey])
                            ? col.badge_map[mapKey]
                            : (col.color || 'primary');
                        return `<td><span class="badge bg-${badgeColor}">${val}</span></td>`;
                    }
                    if (col.truncate && typeof val === 'string' && val.length > col.truncate) {
                        val = val.substring(0, col.truncate) + '...';
                    }
                    return `<td>${val}</td>`;
                }).join('');

                // Generically render actions as Dropdown
                let actionsHtml = '';
                if (actions && actions.length > 0) {
                    const dropdownItems = actions.map(act => {
                        let itemContent = '';
                        if (act.action === 'modal-form') {
                            itemContent = `
                                <a class="dropdown-item edit-item-btn" href="javascript:void(0);"
                                   onclick="window.handleEditAction('${row.id}', '${act.action_url}', '${btoa(schemaStr)}')">
                                    <i class="${act.icon} align-bottom me-2 text-muted"></i> ${act.label}
                                </a>`;
                        } else if (act.action === 'api-call' && act.method === 'DELETE') {
                            itemContent = `
                                <a class="dropdown-item remove-item-btn" href="javascript:void(0);"
                                   onclick="window.deleteItem('${act.action_url.replace('{id}', row.id)}', '${act.confirm_message || ''}')">
                                    <i class="${act.icon} align-bottom me-2 text-muted"></i> ${act.label}
                                </a>`;
                        }
                        return `<li>${itemContent}</li>`;
                    }).join('');

                    actionsHtml = `
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-more-fill fs-5 align-middle"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ${dropdownItems}
                            </ul>
                        </div>
                    `;
                }

                return `
                    <tr>
                        ${colsHtml}
                        <td class="py-1">
                            ${actionsHtml}
                        </td>
                    </tr>
                `;

            }).join('');

            tbody.innerHTML = rowsHtml;

        } catch (e) {
            console.error(e);
            const tbody = table.querySelector('tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="100%" class="text-danger">Error loading data</td></tr>';
        }
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

// Start Engine
document.addEventListener('DOMContentLoaded', init);
