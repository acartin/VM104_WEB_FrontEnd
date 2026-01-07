import { LinkSidebar } from './Sidebar.js';
import { renderContent } from '../renderer/main.js';

export function LinkAppShell(data) {
    const sidebarHtml = LinkSidebar(data.sidebar);
    const contentHtml = renderContent(data.content);

    // Official Velzon Structure
    return `
        <div id="layout-wrapper">
            
            <header id="page-topbar">
                <div class="layout-width">
                    <div class="navbar-header">
                        <div class="d-flex">
                            <!-- LOGO -->
                            <div class="navbar-brand-box horizontal-logo">
                                <a href="index.html" class="logo logo-dark">
                                    <span class="logo-sm">
                                        <img src="themes/images/logo-sm.png" alt="" height="22">
                                    </span>
                                    <span class="logo-lg">
                                        <img src="themes/images/logo-dark.png" alt="" height="17">
                                    </span>
                                </a>
                            </div>

                            <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
                                <span class="hamburger-icon">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </span>
                            </button>
                        </div>

                        <div class="d-flex align-items-center">
                            
                            <!-- Light/Dark Mode Toggle -->
                            <div class="ms-1 header-item d-none d-sm-flex">
                                <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle light-dark-mode">
                                    <i class='bx bx-moon fs-22'></i>
                                </button>
                            </div>

                            <!-- User Dropdown (Mocked) -->
                             <div class="dropdown ms-sm-3 header-item topbar-user">
                                <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="d-flex align-items-center">
                                        <img class="rounded-circle header-profile-user" src="themes/images/users/avatar-1.jpg" alt="Header Avatar">
                                        <span class="text-start ms-xl-2">
                                            <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">Admin</span>
                                            <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">Founder</span>
                                        </span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            ${sidebarHtml}

            <!-- Vertical Overlay-->
            <div class="vertical-overlay"></div>

            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid" id="page-root">
                        ${contentHtml}
                    </div>
                </div>
                
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6">
                                ${new Date().getFullYear()} © AI First.
                            </div>
                            <div class="col-sm-6">
                                <div class="text-sm-end d-none d-sm-block">
                                    Design & Develop by Themesbrand
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    `;
}
