export function LinkButtonGroup(data) {
    const buttonsHtml = (data.buttons || []).map(btn => `
        <button class="btn btn-${btn.variant || 'primary'}">
            ${btn.label}
        </button>
    `).join('');

    return `
        <div class="col-12 gap-2 d-flex mb-4">
            ${buttonsHtml}
        </div>
    `;
}
