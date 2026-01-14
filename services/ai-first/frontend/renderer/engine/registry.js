/**
 * Component Registry for SDUI Renderer
 * Maps component types to their respective Link functions.
 */

import { LinkMetricCard } from '../../components/cards/MetricCard.js';
import { LinkGridContainer } from '../../components/grids/Grid.js';
import { LinkTypography } from '../../components/ui/Typography.js';
import { LinkButtonGroup } from '../../components/ui/ButtonGroup.js';
import { LinkGridVisual } from '../../components/grids/GridVisual.js';
import { LinkLeadControlGrid } from '../../components/grids/LeadControlGrid.js';
import { LinkTabs } from '../../components/ui/Tabs.js';
import { LinkModalForm } from '../../components/forms/ModalForm.js';
import { LinkRow, LinkCol } from '../../components/layout/Layout.js';
import { LinkProjectBanner } from '../../components/layout/ProjectBanner.js';
import { LinkCard } from '../../components/ui/Card.js';
import { LinkMemberListCard, LinkGenericCard, LinkFileGrid, LinkContactListDetailed } from '../../components/cards/DashboardWidgets.js';

// Simple Wrapper for Custom Grid Container
export function LinkCustomGridContainer(component) {
    const props = component.properties || {};
    return `
        <div id="grid-custom-${Math.random().toString(36).substr(2, 9)}"
             class="card h-100 shadow-sm"
             data-type="custom-leads-grid"
             data-grid-id="${props.grid_id || 'default'}"
             data-url="${props.data_url}"
             data-columns='${JSON.stringify(props.columns || [])}'
             data-actions='${JSON.stringify(props.actions || [])}'
             data-enable-filters="${props.enableFilters ? 'true' : 'false'}"
             data-filter-config='${JSON.stringify(props.filterConfig || {})}'>
             <div class="card-body p-3">
                <!-- Engine will hydrate here -->
                <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                    <span class="spinner-border text-primary me-2"></span> Initializing Beta Engine...
                </div>
             </div>
        </div>
    `;
}

const registry = {
    'custom-leads-grid': LinkCustomGridContainer, // Beta Engine
    'card': LinkCard,
    'card-metric': LinkMetricCard,
    'grid': LinkGridContainer,
    'typography': LinkTypography,
    'button-group': LinkButtonGroup,
    'grid-visual': LinkGridVisual,
    'grid-leads-control': LinkLeadControlGrid,
    'tabs': LinkTabs,
    'modal-form': LinkModalForm,
    'row': LinkRow,
    'col': LinkCol,
    'layout-row': LinkRow,
    'layout-col': LinkCol,
    'project-banner': LinkProjectBanner,
    'member-list': LinkMemberListCard,
    'member-list-card': LinkMemberListCard,
    'generic-card': LinkGenericCard,
    'file-grid': LinkFileGrid,
    'contact-list-detailed': LinkContactListDetailed
};

export function renderComponent(component) {
    if (!component || !component.type) return '';

    const LinkFn = registry[component.type];
    if (LinkFn) {
        return LinkFn(component);
    }

    console.warn(`[Renderer] Missing component type: ${component.type}`);
    return `<div class="alert alert-warning">Unknown component: ${component.type}</div>`;
}

export function renderContent(components) {
    if (!components) return '';
    if (!Array.isArray(components)) return renderComponent(components);
    return components.map(c => renderComponent(c)).join('');
}
