import { LitElement, html, css } from 'https://cdn.jsdelivr.net/gh/lit/dist@3/core/lit-core.min.js';

class PropertyCard extends LitElement {
  static properties = {
    title: { type: String },
    price: { type: Number },
    location: { type: String },
    imageUrl: { type: String }
  };

  static styles = css`
    :host {
      display: block;
      margin-bottom: 1rem;
    }
    .card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 16px;
      overflow: hidden;
      color: white;
      transition: transform 0.3s ease;
    }
    .card:hover {
      transform: translateY(-5px);
      border-color: rgba(255, 255, 255, 0.3);
    }
    img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }
    .content {
      padding: 1rem;
    }
    h3 {
      margin: 0;
      font-size: 1.1rem;
      font-weight: 600;
    }
    .price {
      color: #6366f1;
      font-size: 1.25rem;
      font-weight: 700;
      margin: 0.5rem 0;
    }
    .location {
      font-size: 0.85rem;
      opacity: 0.7;
      display: flex;
      align-items: center;
      gap: 4px;
      margin-bottom: 1rem;
    }
    .badge {
      background: #4b38b3;
      font-size: 0.7rem;
      padding: 4px 8px;
      border-radius: 6px;
      text-transform: uppercase;
    }
    .btn-action {
      width: 100%;
      background: rgba(99, 102, 241, 0.2);
      border: 1px solid rgba(99, 102, 241, 0.4);
      color: #a5b4fc;
      padding: 10px;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 0.9rem;
      text-align: center;
    }
    .btn-action:hover {
      background: rgba(99, 102, 241, 0.4);
      color: white;
    }
  `;

  _handleMapClick() {
    alert(`📍 Abriendo mapa interactivo para: ${this.title}\n(Aquí inyectaremos el componente PropertyMap en la siguiente fase)`);
  }

  render() {
    return html`
      <div class="card">
        ${this.imageUrl ? html`<img src="${this.imageUrl}" alt="${this.title}">` : ''}
        <div class="content">
          <span class="badge">Propiedad Destacada</span>
          <h3>${this.title || 'Propiedad sin título'}</h3>
          <div class="price">
            ${new Intl.NumberFormat('es-CR', { style: 'currency', currency: 'USD' }).format(this.price || 0)}
          </div>
          <div class="location">
             📍 ${this.location || 'Ubicación no disponible'}
          </div>
          <button class="btn-action" @click="${this._handleMapClick}">
            📍 Abrir Mapa
          </button>
        </div>
      </div>
    `;
  }
}

customElements.define('property-card', PropertyCard);
