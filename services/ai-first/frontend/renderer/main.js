/**
 * AI-First SDUI Renderer Engine (Modular Version)
 * Strictly follows 'visual_dictionary.json' and 'catalog_context.json'
 */

import { renderContent, renderComponent } from './modules/registry.js';
export { renderContent, renderComponent };
import { hydrateGrids, hydrateLeadsControlGrid } from './modules/hydration.js';
import './modules/actions.js'; // Attaches handlers to window

import { LinkAppShell } from '../components/layout/AppShell.js';
import { LinkSidebar } from '../components/layout/Sidebar.js';
import { LinkNavbar } from '../components/layout/Navbar.js';
import { LinkProjectBanner } from '../components/layout/ProjectBanner.js';

const API_BASE_URL = window.AppConfig.API_BASE_URL;
const RENDERER_VERSION = "63";
console.log(`[Renderer] v${RENDERER_VERSION} Modular Initializing...`);

window.appState = { currentPath: null };

async function init() {
    const appRoot = document.getElementById('app-root');
    try {
        const token = localStorage.getItem('access_token');
        const headers = token ? { 'Authorization': `Bearer ${token}` } : {};
        const response = await fetch(`${API_BASE_URL}/app-init`, { headers });

        if (response.status === 401) {
            window.location.href = 'login.html';
            return;
        }
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const appData = await response.json();

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
        } else if (appRoot) {
            appRoot.innerHTML = renderContent(appData.components);
            hydrateGrids();
            hydrateLeadsControlGrid();
        }
    } catch (error) {
        console.error('Render Error:', error);
        if (appRoot) appRoot.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
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
