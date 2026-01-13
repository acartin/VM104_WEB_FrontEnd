/**
 * AI-First SDUI Renderer Engine (Modular Version)
 * Strictly follows 'visual_dictionary.json' and 'catalog_context.json'
 */

import { renderContent, renderComponent } from './engine/registry.js';
export { renderContent, renderComponent };
import { hydrateGrids, hydrateLeadsControlGrid } from './engine/hydration.js';
import './engine/actions.js'; // Attaches handlers to window

import { LinkAppShell } from '../components/layout/AppShell.js';
import { LinkSidebar } from '../components/layout/Sidebar.js';
import { LinkNavbar } from '../components/layout/Navbar.js';
import { LinkProjectBanner } from '../components/layout/ProjectBanner.js';

const API_BASE_URL = window.AppConfig.API_BASE_URL;
const RENDERER_VERSION = "68";
console.log(`[Renderer] v${RENDERER_VERSION} Modular Initializing... (REGISTRY FIX)`);

window.appState = { currentPath: null };

async function init() {
    const appRoot = document.getElementById('app-root');
    try {
        const token = localStorage.getItem('access_token');
        const headers = token ? { 'Authorization': `Bearer ${token}` } : {};

        // Timeout Promise
        const timeout = new Promise((_, reject) =>
            setTimeout(() => reject(new Error('Connection timed out. Backend is unresponsive.')), 5000)
        );

        // Race fetch against timeout
        const response = await Promise.race([
            fetch(`${API_BASE_URL}/app-init`, { headers }),
            timeout
        ]);

        if (response.status === 401) {
            window.location.href = 'login.html';
            return;
        }
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const appData = await response.json();

        // Normal Boot Process...
        if (appData.layout === 'dashboard-shell') {
            if (appData.content) appData.contentHtml = renderContent(appData.content);
            const shellHtml = LinkAppShell(appData);

            const existingWrapper = document.getElementById('layout-wrapper');
            if (existingWrapper) existingWrapper.outerHTML = shellHtml;
            else document.body.insertAdjacentHTML('afterbegin', shellHtml);

            setupThemeSwitcher();
            updateHeaderProfile();
            setupNavigation();

            const currentPath = window.location.pathname;
            if (currentPath && currentPath !== '/' && currentPath !== '/index.html') {
                navigateTo(currentPath);
            } else {
                hydrateGrids();
                hydrateLeadsControlGrid();
            }
        }
    } catch (error) {
        console.error('Render Error:', error);
        // EMERGENCY MODE UI
        document.body.innerHTML = `
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100vh; background:#222; color:#fff; font-family:sans-serif;">
                <h1 style="color:#ff6b6b">⚠️ EMERGENCY MODE</h1>
                <p>The Application failed to initialize.</p>
                <div style="background:#334; padding:15px; border-radius:5px; margin:20px 0; width: 80%; max-width: 800px;">
                    <textarea readonly style="width:100%; height:150px; background:#111; color:#ffeb3b; border:1px solid #555; padding:10px; font-family:monospace; font-size:12px; resize:none;">${(error.stack || error.message || JSON.stringify(error) || "Unknown Error")}</textarea>
                    <button type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value); this.innerText='COPIED!'" style="display:block; width:100%; margin-top:10px; padding:10px; background:#00bd9d; color:#fff; font-weight:bold; border:none; border-radius:4px; cursor:pointer;">COPY ERROR TO CLIPBOARD</button>
                </div>
                <button onclick="window.location.reload()" style="padding:10px 20px; background:#4b38b3; color:white; border:none; border-radius:4px; cursor:pointer;">
                    Retry Connection
                </button>
            </div>
        `;
    }
}

export async function navigateTo(href, pushState = true) {
    if (!href || href === '#' || href.startsWith('#')) return;
    window.appState.currentPath = href;
    const pageRoot = document.getElementById('page-root');
    if (!pageRoot) return;

    pageRoot.innerHTML = `<div class="text-center mt-5"><div class="spinner-border text-primary" role="status"></div></div>`;

    try {
        const token = localStorage.getItem('access_token');
        const headers = token ? { 'Authorization': `Bearer ${token}` } : {};
        const response = await fetch(`${API_BASE_URL}${href}`, { headers });
        if (!response.ok) throw new Error(`View not found (${response.status})`);

        const viewData = await response.json();

        if (viewData.layout === 'dashboard-project-overview') {
            const bannerHtml = LinkProjectBanner(viewData);
            let tabsContentHtml = '';
            if (viewData.tabs) {
                tabsContentHtml = viewData.tabs.map(tab => {
                    const activeClass = tab.active ? 'show active' : '';
                    return `<div class="tab-pane fade ${activeClass}" id="${tab.id}" role="tabpanel">${renderContent(tab.components)}</div>`;
                }).join('');
            } else {
                tabsContentHtml = `<div class="tab-pane fade show active" id="project-overview" role="tabpanel">${renderContent(viewData.components)}</div>`;
            }
            pageRoot.innerHTML = `${bannerHtml}<div class="tab-content text-muted mt-3">${tabsContentHtml}</div>`;
        } else if (viewData.components) {
            pageRoot.innerHTML = renderContent(viewData.components);
        }

        hydrateGrids();
        hydrateLeadsControlGrid();

        if (pushState) history.pushState(null, '', href);
        document.body.classList.remove('vertical-sidebar-enable');
    } catch (error) {
        console.error('Navigation Error:', error);
        pageRoot.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    }
}

function setupThemeSwitcher() {
    const btn = document.querySelector('.light-dark-mode');
    if (!btn) return;
    btn.addEventListener('click', () => {
        const html = document.documentElement;
        const currentMode = html.getAttribute('data-bs-theme') || 'light';
        const newMode = currentMode === 'light' ? 'dark' : 'light';
        html.setAttribute('data-bs-theme', newMode);
        html.setAttribute('data-layout-mode', newMode);
        localStorage.setItem('theme-mode', newMode);
    });
    const savedMode = localStorage.getItem('theme-mode');
    if (savedMode) {
        document.documentElement.setAttribute('data-bs-theme', savedMode);
        document.documentElement.setAttribute('data-layout-mode', savedMode);
    }
}

function setupNavigation() {
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a.nav-link') || e.target.closest('.js-navigate');
        if (!link) return;
        const href = link.getAttribute('href') || link.dataset.url;
        if (!href || href === '#' || href.startsWith('#')) return;
        e.preventDefault();
        navigateTo(href, true);
    });
    window.addEventListener('popstate', () => {
        const path = window.location.pathname;
        if (path) navigateTo(path, false);
    });
}

function updateHeaderProfile() {
    try {
        const profileStr = localStorage.getItem('user_profile');
        if (!profileStr) return;
        const profile = JSON.parse(profileStr);
        const nameEl = document.getElementById('header-user-name');
        if (nameEl && profile.name) nameEl.textContent = profile.name;
        const tenantEl = document.getElementById('header-tenant-name');
        if (tenantEl) {
            if (profile.is_superuser) tenantEl.textContent = 'Global Administrator';
            else if (profile.tenants?.length > 0) {
                const tenant = profile.tenants[0];
                tenantEl.textContent = tenant.client?.name ? `Client Admin - ${tenant.client.name}` : 'Client Admin';
            }
        }
    } catch (e) {
        console.warn('Profile update fail:', e);
    }
}

document.addEventListener('DOMContentLoaded', init);
window.navigateTo = navigateTo; // For global access if needed
