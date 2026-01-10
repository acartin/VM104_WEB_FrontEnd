import { renderComponent } from '../../renderer/main.js';

export function LinkRow(data) {
    const childrenHtml = (data.components || [])
        .map(c => renderComponent(c))
        .join('');

    return `
        <div class="row ${data.class || ''}">
            ${childrenHtml}
        </div>
    `;
}

export function LinkCol(data) {
    const size = data.size || 12;
    const childrenHtml = (data.components || [])
        .map(c => renderComponent(c))
        .join('');

    return `
        <div class="col-xl-${size} col-lg-${size} ${data.class || ''}">
            ${childrenHtml}
        </div>
    `;
}
