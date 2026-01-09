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
                <div class="table-card js-grid-visual-parent">
                    <div id="${gridId}" 
                         class="js-grid-visual"
                         data-url="${dataUrl}"
                         data-schema='${formSchema.replace(/'/g, "&apos;")}'
                         data-columns='${columnsJson.replace(/'/g, "&apos;")}'
                         data-actions='${actionsJson.replace(/'/g, "&apos;")}'
                         data-filters='${filtersJson.replace(/'/g, "&apos;")}'
                         >
                         <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderHeaderAction(btn, globalSchema) {
    if (btn.action === 'modal-form') {
        // Prioritize action-level schema, fallback to global grid schema
        const schema = btn.schema || globalSchema;
        // If it's already a string (like base64 from backend), use it directly. 
        // If it's an object, stringify it.
        const schemaVal = typeof schema === 'string' ? schema : JSON.stringify(schema || []);
        const schemaAttr = schemaVal ? `data-schema='${schemaVal.replace(/'/g, "&apos;")}'` : '';

        return `
            <button type="button" class="btn btn-${btn.color || 'primary'} btn-sm" 
                onclick="window.handleGenericAction(this)"
                data-action="${btn.action}"
                data-url="${btn.action_url}"
                data-title="${btn.modal_title || btn.label}"
                data-method="POST"
                ${schemaAttr}>
                <i class="${btn.icon} fs-5 align-middle me-1"></i> ${btn.label}
            </button>
        `;
    }
    return '';
}
