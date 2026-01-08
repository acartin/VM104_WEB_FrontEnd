const API_BASE_URL = window.AppConfig.API_BASE_URL;

export function LinkGridVisual(component) {
    const props = component.properties || {};
    const columns = props.columns || [];
    const gridId = `grid-${Math.random().toString(36).substr(2, 9)}`;
    const dataUrl = props.data_url;

    // Generate Header
    const headers = columns.map(col => `
        <th scope="col" class="${col.sortable ? 'sortable' : ''}">${col.label}</th>
    `).join('');

    // Return HTML structure with data attributes for hydration
    // We add a script to run immediately after insertion (poor man's hydration) or rely on main.js
    // Let's rely on main.js finding .js-grid-visual

    // Serialize Schema to simple JSON string for hydration
    const formSchema = JSON.stringify(props.form_schema || []);
    const columnsJson = JSON.stringify(props.columns || []);
    const actionsJson = JSON.stringify(props.actions || []);
    const filtersJson = JSON.stringify(props.filters || []);

    return `
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">${component.label || 'Data Grid'}</h4>
                <div class="flex-shrink-0">
                    ${(props.header_actions || []).map(btn => renderHeaderAction(btn, props.form_schema)).join('')}
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Toolbar -->
                <div class="grid-filters-container row g-3 mb-3" id="${gridId}-filters"></div>

                <div class="table-responsive table-card" style="min-height: 350px;">
                    <table class="table table-nowrap table-sm align-middle table-striped-columns mb-0 js-grid-visual" 
                           id="${gridId}" 
                           data-url="${dataUrl}"
                           data-schema='${formSchema.replace(/'/g, "&apos;")}'
                           data-columns='${columnsJson.replace(/'/g, "&apos;")}'
                           data-actions='${actionsJson.replace(/'/g, "&apos;")}'
                           data-filters='${filtersJson.replace(/'/g, "&apos;")}'
                           >
                        <thead class="table-light">
                            <tr>
                                ${headers}
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="${columns.length + 1}" class="text-center p-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

function renderHeaderAction(btn, schema) {
    if (btn.action === 'modal-form') {
        const schemaAttr = schema ? `data-schema='${JSON.stringify(schema).replace(/'/g, "&apos;")}'` : '';
        return `
            <button type="button" class="btn btn-${btn.color || 'primary'} btn-sm" 
                onclick="window.handleGenericAction(this)"
                data-action="${btn.action}"
                data-url="${btn.action_url}"
                data-title="${btn.modal_title}"
                data-method="POST"
                ${schemaAttr}>
                <i class="${btn.icon} fs-5 align-middle me-1"></i> ${btn.label}
            </button>
        `;
    }
    return '';
}
