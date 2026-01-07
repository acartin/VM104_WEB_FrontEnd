import { renderComponent } from '../../renderer/main.js';

export function LinkGridContainer(data) {
    const childrenHtml = (data.components || [])
        .map(c => renderComponent(c))
        .join('');

    return `
        <div class="row mb-4">
            ${childrenHtml}
        </div>
    `;
}
