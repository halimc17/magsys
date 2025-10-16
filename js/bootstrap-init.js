/**
 * Bootstrap Initialization and Custom Scripts for ERP Mill
 * Maintains backward compatibility with existing JavaScript
 */

// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize all popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Enhanced dropdown menu with hover support for multi-level menus
    initDropdownHover();

    // Add Bootstrap table classes to existing tables automatically
    var tables = document.querySelectorAll('table.sortable, table.data');
    tables.forEach(function(table) {
        if (!table.classList.contains('table')) {
            table.classList.add('table', 'table-striped', 'table-hover', 'table-sm');
        }
    });

    // Convert old button classes to Bootstrap buttons
    var oldButtons = document.querySelectorAll('.mybutton');
    oldButtons.forEach(function(btn) {
        if (!btn.classList.contains('btn')) {
            btn.classList.add('btn', 'btn-primary', 'btn-sm');
        }
    });

    // Convert old input classes to Bootstrap form controls
    var oldInputs = document.querySelectorAll('.myinputtext, .mytextbox');
    oldInputs.forEach(function(input) {
        if (!input.classList.contains('form-control')) {
            input.classList.add('form-control', 'form-control-sm');
        }
    });

    // Convert old select elements
    var oldSelects = document.querySelectorAll('select:not(.form-select)');
    oldSelects.forEach(function(select) {
        select.classList.add('form-select', 'form-select-sm');
    });

    // Add responsive table wrapper
    var dataTables = document.querySelectorAll('table.data, table.sortable');
    dataTables.forEach(function(table) {
        if (!table.parentElement.classList.contains('table-responsive')) {
            var wrapper = document.createElement('div');
            wrapper.classList.add('table-responsive');
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
});

// Legacy compatibility functions
function showProgress() {
    var progressDiv = document.getElementById('progress');
    if (progressDiv) {
        progressDiv.style.display = 'block';
    }
}

function hideProgress() {
    var progressDiv = document.getElementById('progress');
    if (progressDiv) {
        progressDiv.style.display = 'none';
    }
}

// Helper function for Bootstrap alerts
function showAlert(message, type = 'info') {
    var alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-' + type + ' alert-dismissible fade show';
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = message +
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';

    var container = document.querySelector('.container, .container-fluid') || document.body;
    container.insertBefore(alertDiv, container.firstChild);

    // Auto dismiss after 5 seconds
    setTimeout(function() {
        var alert = bootstrap.Alert.getOrCreateInstance(alertDiv);
        alert.close();
    }, 5000);
}

// Helper function for Bootstrap modals
function showModal(title, content, size = '') {
    var modalId = 'dynamicModal_' + Date.now();
    var sizeClass = size ? 'modal-' + size : '';

    var modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ${sizeClass}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    var modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();

    // Remove modal from DOM after it's hidden
    document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Initialize dropdown hover functionality for multi-level menus
function initDropdownHover() {
    // Get all dropdown elements including nested ones
    const dropdowns = document.querySelectorAll('.navbar .dropdown, .navbar .dropend');

    dropdowns.forEach(function(dropdown) {
        let hideTimeout;

        // Show dropdown on hover
        dropdown.addEventListener('mouseenter', function(e) {
            clearTimeout(hideTimeout);

            // Get the direct toggle and menu of this dropdown
            // For nested menus without data-bs-toggle, look for any direct child link
            let toggle = this.querySelector(':scope > [data-bs-toggle="dropdown"]');
            if (!toggle) {
                toggle = this.querySelector(':scope > a.dropdown-toggle');
            }
            const menu = this.querySelector(':scope > .dropdown-menu');

            if (toggle && menu) {
                // Show the dropdown menu
                toggle.classList.add('show');
                menu.classList.add('show');
                toggle.setAttribute('aria-expanded', 'true');

                // CRITICAL: Force overflow visible on ALL parent menus
                // This prevents scrollbar and allows nested menus to escape
                // Apply to both top-level (nav-item) and nested (dropend)
                if (this.classList.contains('nav-item') || this.classList.contains('dropend')) {
                    menu.style.setProperty('overflow', 'visible', 'important');
                    menu.style.setProperty('overflow-x', 'visible', 'important');
                    menu.style.setProperty('overflow-y', 'visible', 'important');
                    menu.style.setProperty('max-height', 'none', 'important');
                }

                // For dropend, CSS handles positioning (position: absolute; left: 100%)
                // No JavaScript positioning needed - pure CSS solution
            }

            e.stopPropagation();
        });

        // Hide dropdown on mouse leave
        dropdown.addEventListener('mouseleave', function(e) {
            let toggle = this.querySelector(':scope > [data-bs-toggle="dropdown"]');
            if (!toggle) {
                toggle = this.querySelector(':scope > a.dropdown-toggle');
            }
            const menu = this.querySelector(':scope > .dropdown-menu');

            hideTimeout = setTimeout(function() {
                if (toggle && menu) {
                    // Don't hide if mouse is over a child menu
                    if (!menu.matches(':hover')) {
                        toggle.classList.remove('show');
                        menu.classList.remove('show');
                        toggle.setAttribute('aria-expanded', 'false');

                        // Also close all child dropdowns
                        const childMenus = menu.querySelectorAll('.dropdown-menu.show');
                        childMenus.forEach(function(childMenu) {
                            childMenu.classList.remove('show');
                            const childToggle = childMenu.previousElementSibling;
                            if (childToggle) {
                                childToggle.classList.remove('show');
                                childToggle.setAttribute('aria-expanded', 'false');
                            }
                        });
                    }
                }
            }, 200); // Small delay to allow moving to submenu

            e.stopPropagation();
        });

        // Handle click to toggle dropdown
        const toggle = dropdown.querySelector(':scope > [data-bs-toggle="dropdown"]');
        if (toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const menu = this.nextElementSibling;
                const isShown = menu && menu.classList.contains('show');

                if (isShown) {
                    // Hide this menu only if it's not inside another open dropdown
                    // or if we're in the top level
                    const isTopLevel = this.closest('.navbar-nav') && !this.closest('.dropdown-menu');

                    if (isTopLevel) {
                        // Top level - close everything
                        this.classList.remove('show');
                        menu.classList.remove('show');
                        this.setAttribute('aria-expanded', 'false');

                        // Close all child dropdowns
                        const childMenus = menu.querySelectorAll('.dropdown-menu.show');
                        childMenus.forEach(function(childMenu) {
                            childMenu.classList.remove('show');
                            const childToggle = childMenu.previousElementSibling;
                            if (childToggle) {
                                childToggle.classList.remove('show');
                                childToggle.setAttribute('aria-expanded', 'false');
                            }
                        });
                    } else {
                        // Nested menu - just toggle this level
                        this.classList.remove('show');
                        menu.classList.remove('show');
                        this.setAttribute('aria-expanded', 'false');
                    }
                } else {
                    // Close sibling menus at the same level
                    const parentUl = this.closest('ul');
                    if (parentUl) {
                        const siblingItems = parentUl.querySelectorAll(':scope > li > .dropdown-menu.show');
                        siblingItems.forEach(function(siblingMenu) {
                            if (siblingMenu !== menu) {
                                siblingMenu.classList.remove('show');
                                const siblingToggle = siblingMenu.previousElementSibling;
                                if (siblingToggle) {
                                    siblingToggle.classList.remove('show');
                                    siblingToggle.setAttribute('aria-expanded', 'false');
                                }
                            }
                        });
                    }

                    // Show this menu
                    this.classList.add('show');
                    menu.classList.add('show');
                    this.setAttribute('aria-expanded', 'true');
                }
            });
        }
    });

    // Handle clicking outside to close all dropdowns
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.navbar')) {
            const openDropdowns = document.querySelectorAll('.navbar .dropdown-menu.show');
            openDropdowns.forEach(function(menu) {
                const toggle = menu.previousElementSibling;
                if (toggle) {
                    toggle.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                }
                menu.classList.remove('show');
            });
        }
    });
}
