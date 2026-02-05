import { LitElement, html, css } from 'https://cdn.jsdelivr.net/gh/lit/dist@3/core/lit-core.min.js';

export class PropertyMap extends LitElement {
    static properties = {
        center: { type: Object },
        zoom: { type: Number },
        pois: { type: Array }
    };

    static styles = css`
        :host {
            display: block;
            height: 200px;
            background: #1e293b;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    `;

    render() {
        return html`
            <div>
                <p>Mapa Interactivo Placeholder</p>
                <small>Lat: ${this.center?.lat}, Lng: ${this.center?.lng}</small>
            </div>
        `;
    }
}

customElements.define('property-map', PropertyMap);
