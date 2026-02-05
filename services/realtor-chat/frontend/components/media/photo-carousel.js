import { LitElement, html, css } from 'https://cdn.jsdelivr.net/gh/lit/dist@3/core/lit-core.min.js';

export class PhotoCarousel extends LitElement {
    static properties = {
        images: { type: Array },
        showThumbnails: { type: Boolean, attribute: 'show-thumbnails' }
    };

    static styles = css`
        :host {
            display: block;
            width: 100%;
            height: 150px;
            background: #334155;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
    `;

    render() {
        return html`
            <div>
                <p>Carrusel de Fotos (${this.images?.length || 0} imágenes)</p>
            </div>
        `;
    }
}

customElements.define('photo-carousel', PhotoCarousel);
