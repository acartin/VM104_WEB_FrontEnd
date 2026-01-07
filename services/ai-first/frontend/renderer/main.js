/**
 * AI-First Dumb Renderer Engine
 * Strictly follows 'visual_dictionary.json' and 'catalog_context.json'
 */

import { LinkAppShell } from '../components/AppShell.js';
import { LinkMetricCard } from '../components/MetricCard.js';
import { LinkGridContainer } from '../components/Grid.js';
import { LinkTypography } from '../components/Typography.js';
import { LinkButtonGroup } from '../components/ButtonGroup.js';

const API_BASE_URL = 'http://192.168.0.34:8084'; // Updated to Server IP

async function init() {
    // We target body for full shell replacement, or app-root for partials
    const appRoot = document.getElementById('app-root');

    try {
        // Fetch UI Schema from Backend
        const response = await fetch(`${API_BASE_URL}/app-init`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const appData = await response.json();
        console.log('App Data received:', appData);

        if (appData.layout === 'dashboard-shell') {
            // Full Shell Render: Replace existing layout-wrapper
            const shellHtml = LinkAppShell(appData);

            // If explicit layout-wrapper exists, replace it. Otherwise append to body.
            const existingWrapper = document.getElementById('layout-wrapper');
            if (existingWrapper) {
                existingWrapper.outerHTML = shellHtml;
            } else {
                document.body.insertAdjacentHTML('afterbegin', shellHtml);
            }

            // Initialize Theme Switcher Logic
            setupThemeSwitcher();

            // Initialize SPA Navigation
            setupNavigation();

        } else {
            // Partial Render: Inject into app-root
            if (appRoot) {
                appRoot.innerHTML = renderContent(appData.components);
            }
        }

    } catch (error) {
        console.error('Render Error:', error);
        if (appRoot) {
            appRoot.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <strong>System Error:</strong> Failed to load application.<br>
                    <small>${error.message}</small>
                </div>
            `;
        }
    }
}

export function renderComponent(component) {
    console.log('Rendering component:', component.type); // DEBUG

    switch (component.type) {
        case 'card-metric':
            return LinkMetricCard(component);
        case 'grid':
            return LinkGridContainer(component);
        case 'typography':
            return LinkTypography(component);
        case 'button-group':
            return LinkButtonGroup(component);
        default:
            console.warn(`Unknown component type: ${component.type}`);
            return `<!-- Unknown component: ${component.type} -->`;
    }
}

// Start Engine
document.addEventListener('DOMContentLoaded', init);

export function renderContent(components) {
    if (components && Array.isArray(components)) {
        return `
            <div class="row">
                ${components.map(c => renderComponent(c)).join('')}
            </div>
        `;
    }
    return '';
}

function setupThemeSwitcher() {
    const btn = document.querySelector('.light-dark-mode');
    if (!btn) return;

    btn.addEventListener('click', () => {
        const html = document.documentElement;
        // Check current attribute (Velzon uses data-bs-theme or data-layout-mode)
        const currentMode = html.getAttribute('data-bs-theme') || 'light';
        const newMode = currentMode === 'light' ? 'dark' : 'light';

        // Set attributes for Bootstrap 5.3+ and Velzon
        html.setAttribute('data-bs-theme', newMode);
        html.setAttribute('data-layout-mode', newMode); // Legacy support

        // Adjust Header/Sidebar for contrast if needed
        if (newMode === 'dark') {
            html.setAttribute('data-topbar', 'dark');
            html.setAttribute('data-sidebar', 'dark');
        } else {
            html.setAttribute('data-topbar', 'light');
            html.setAttribute('data-sidebar', 'dark'); // Sidebar usually stays dark in light mode for Velzon logic
        }

        // Update Icon
        const icon = btn.querySelector('i');
        if (newMode === 'dark') {
            icon.classList.replace('bx-moon', 'bx-sun');
        } else {
            icon.classList.replace('bx-sun', 'bx-moon');
        }

        // Save preference
        localStorage.setItem('theme-mode', newMode);
    });

    // Load preference on init
    const savedMode = localStorage.getItem('theme-mode');
    if (savedMode) {
        document.documentElement.setAttribute('data-bs-theme', savedMode);
        document.documentElement.setAttribute('data-layout-mode', savedMode);

        if (savedMode === 'dark') {
            document.documentElement.setAttribute('data-topbar', 'dark');
            const icon = btn.querySelector('i');
            if (icon) icon.classList.replace('bx-moon', 'bx-sun');
        }
    }
}

function setupNavigation() {
    document.addEventListener('click', async (e) => {
        const link = e.target.closest('a.nav-link');
        if (!link) return;

        const href = link.getAttribute('href');
        // Ignore toggles or empty links
        if (!href || href === '#' || href.startsWith('#')) return;

        e.preventDefault();
        console.log('Navigating to:', href);

        const pageRoot = document.getElementById('page-root');
        if (!pageRoot) return;

        // Show Loading
        pageRoot.innerHTML = `
             <div class="text-center mt-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        `;

        try {
            // Fetch View Data (assuming API endpoint matches route path: /clients -> API/clients)
            const response = await fetch(`${API_BASE_URL}${href}`);

            if (!response.ok) throw new Error('View not found');

            const viewData = await response.json();

            // Render New Content
            if (viewData.components) {
                pageRoot.innerHTML = renderContent(viewData.components);
            }

            // Update URL without reload (History API)
            history.pushState(null, '', href);

            // Close sidebar on mobile
            document.body.classList.remove('vertical-sidebar-enable');

        } catch (error) {
            console.error('Navigation Error:', error);
            pageRoot.innerHTML = `
                <div class="alert alert-danger">
                    Error loading content: ${error.message}
                </div>
            `;
        }
    });
}
