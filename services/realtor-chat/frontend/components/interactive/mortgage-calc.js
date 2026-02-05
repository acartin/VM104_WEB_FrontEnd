import { LitElement, html, css } from 'https://cdn.jsdelivr.net/gh/lit/dist@3/core/lit-core.min.js';

export class MortgageCalculator extends LitElement {
    static properties = {
        propertyPrice: { type: Number, attribute: 'property-price' },
        defaultInterest: { type: Number, attribute: 'default-interest' }
    };

    static styles = css`
        :host {
            display: block;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px;
            color: white;
        }
        .title { font-weight: bold; margin-bottom: 10px; display: block; }
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
