
export function LinkModalForm(id, title, formHtml, saveActionUrl, method = 'POST') {
    return `
    <div class="modal fade" id="${id}" tabindex="-1" aria-labelledby="${id}Label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="${id}Label">${title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="${id}-form" action="${saveActionUrl}" method="${method}" onsubmit="return false;">
                        ${formHtml}
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitModalForm('${id}-form', '${saveActionUrl}', '${method}')">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    `;
}

// Helper to render a single input
export function renderInput(label, name, value = '', type = 'text', required = false, validation = {}) {
    const isRequired = required ? 'required' : '';
    const minLength = validation.min_length ? `minlength="${validation.min_length}"` : '';
    const maxLength = validation.max_length ? `maxlength="${validation.max_length}"` : '';
    const pattern = validation.pattern ? `pattern="${validation.pattern}"` : '';

    if (type === 'textarea') {
        return `
            <div class="mb-3">
                <label for="${name}" class="form-label">${label}</label>
                <textarea class="form-control" id="${name}" name="${name}" rows="${validation.rows || 3}" 
                    ${isRequired} ${minLength} ${maxLength}>${value}</textarea>
            </div>
        `;
    }

    if (type === 'switch' || type === 'checkbox') {
        const isChecked = (value === true || value === 'true' || value === '1');
        const checkedStr = isChecked ? 'checked' : '';
        const roleSwitch = type === 'switch' ? 'role="switch"' : '';
        // Dynamic Color: Success if checked, Danger if unchecked
        const colorClass = isChecked ? 'form-switch-success' : 'form-switch-danger';

        // Toggle Logic script
        const toggleScript = `this.closest('.form-check').classList.remove('form-switch-success', 'form-switch-danger'); this.closest('.form-check').classList.add(this.checked ? 'form-switch-success' : 'form-switch-danger');`;

        return `
            <div class="mb-3 form-check form-switch ${colorClass}">
                <input class="form-check-input" type="checkbox" ${roleSwitch} id="${name}" name="${name}" ${checkedStr} ${isRequired} onchange="${toggleScript}">
                <label class="form-check-label" for="${name}">${label}</label>
            </div>
        `;
    }

    if (type === 'select') {
        const sourceUrl = validation.source || '';
        const optionsHtml = (validation.options || []).map(opt => `<option value="${opt.value}" ${opt.value == value ? 'selected' : ''}>${opt.label}</option>`).join('');

        // If source is provided, we mark it for hydration
        const dataSourceAttr = sourceUrl ? `data-source="${sourceUrl}"` : '';
        const dataValueAttr = value ? `data-value="${value}"` : '';

        return `
            <div class="mb-3">
                <label for="${name}" class="form-label">${label}</label>
                <select class="form-select" id="${name}" name="${name}" ${isRequired} ${dataSourceAttr} ${dataValueAttr}>
                    <option value="">Select...</option>
                    ${optionsHtml}
                </select>
            </div>
        `;
    }

    return `
        <div class="mb-3">
            <label for="${name}" class="form-label">${label}</label>
            <input type="${type}" class="form-control" id="${name}" name="${name}" value="${value}" 
                ${isRequired} ${minLength} ${maxLength} ${pattern}>
        </div>
    `;
}

// NEW: Generic Form Renderer
export function renderFormFromSchema(schema, data = {}) {
    if (!Array.isArray(schema)) return '';
    return schema.map(field => {
        const val = data[field.name] || '';
        // Pass validation rules if present in field definition
        const validation = {
            min_length: field.min_length,
            max_length: field.max_length,
            pattern: field.pattern,
            rows: field.rows,
            source: field.source,
            options: field.options
        };
        return renderInput(field.label, field.name, val, field.type, field.required, validation);
    }).join('');
}
