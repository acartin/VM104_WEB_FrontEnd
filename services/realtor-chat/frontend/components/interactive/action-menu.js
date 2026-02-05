import { LitElement, html, css } from 'https://cdn.jsdelivr.net/gh/lit/dist@3/core/lit-core.min.js';

export class ActionMenu extends LitElement {
    static properties = {
        title: { type: String },
        options: { type: Array }
    };

    static styles = css`
        :host {
            display: block;
            margin-top: 10px;
        }
        .menu-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        button {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }
        button:hover {
            background: var(--accent, #4b38b3);
            border-color: transparent;
        }
    `;

    render() {
        return html`
            <div class="menu-container">
                ${this.options?.map(opt => html`
                    <button @click="${() => this._handleAction(opt.payload)}">
                        ${opt.label}
                    </button>
                `)}
            </div>
        `;
    }

    _handleAction(payload) {
        console.log("Action triggered:", payload);
        // Aquí emitiremos un evento para que el orquestador lo capture
    }
}

customElements.define('action-menu', ActionMenu);
