import { LitElement, html, css } from 'https://cdn.jsdelivr.net/gh/lit/dist@3/core/lit-core.min.js';

export class MortgageCalculator extends LitElement {
    static properties = {
        propertyPrice: { type: Number, attribute: 'property-price' },
        defaultInterest: { type: Number, attribute: 'default-interest' }
    };

    static styles = css`
        :host {
            display: block;
            background: var(--brand-surface, rgba(255, 255, 255, 0.05));
            border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.1));
            border-radius: var(--border-radius, 12px);
            padding: 15px;
            color: var(--text-on-surface, white);
            box-shadow: var(--box-shadow, none);
            font-family: var(--font-body, sans-serif);
        }
        .title { 
            font-weight: 700;
            font-family: var(--font-heading, sans-serif);
            margin-bottom: 10px; 
            display: block;
            color: var(--brand-secondary, #6366f1);
        }
    `;

    render() {
        return html`
            <div class="calc-container">
                <span class="title">Calculadora para: $${this.propertyPrice?.toLocaleString()}</span>
                <p>Módulo de calculadora en construcción...</p>
            </div>
        `;
    }
}

customElements.define('mortgage-calculator', MortgageCalculator);
