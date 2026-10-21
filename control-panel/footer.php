    </main>

    <!-- Global Page Loading Overlay -->
    <div class="page-loading-overlay" id="pageLoadingOverlay">
        <div class="loading-spinner"></div>
        <div class="loading-text" id="loadingText">Loading...</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingOverlay = document.getElementById('pageLoadingOverlay');
            const loadingText = document.getElementById('loadingText');
            if (!loadingOverlay) return;

            // 1. Handle standard link clicks that cause page reload
            document.body.addEventListener('click', function(e) {
                // Skip if default behavior was already prevented by local AJAX handlers
                if (e.defaultPrevented) return;

                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                const target = link.getAttribute('target');
                const classList = link.classList;

                // Skip non-navigational links
                if (!href || href === '#' || href === '' || href.startsWith('javascript:') || href.startsWith('#')) return;
                if (target === '_blank') return;

                // Check if this is a pagination, sidebar menu link, tab, or action button
                const isPagination = link.closest('.pagination') || classList.contains('page-link');
                const isSidebar = link.closest('.sidebar') || classList.contains('sidebar-link');
                const isActionButton = classList.contains('btn-action') || classList.contains('btn-primary-adm') || classList.contains('btn-secondary-adm');
                const isTab = classList.contains('tab-btn') || classList.contains('cms-tab-btn');

                if (isPagination || isSidebar || isActionButton || isTab) {
                    // Try to show suspense table loader first if a table is present on the page
                    const table = document.querySelector('table.admin-table');
                    if (table && (isPagination || isTab)) {
                        const tableContainer = table.closest('.table-responsive') || table.closest('[style*="overflow-x"]') || table.parentElement;
                        if (tableContainer) {
                            let loader = tableContainer.querySelector('.table-loading-overlay');
                            if (!loader) {
                                tableContainer.style.position = 'relative';
                                const loaderHtml = `
                                    <div class="table-loading-overlay" id="tableLocalLoader">
                                        <div class="suspense-spinner">
                                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                                            <span>Loading Data...</span>
                                        </div>
                                    </div>
                                `;
                                tableContainer.insertAdjacentHTML('afterbegin', loaderHtml);
                                loader = tableContainer.querySelector('.table-loading-overlay');
                            }
                            if (loader) {
                                loader.classList.add('active');
                            }
                        }
                    } else {
                        // Fallback to full-screen page loader for sidebar and major action buttons
                        if (loadingText) {
                            if (isSidebar) loadingText.innerText = "Loading View...";
                            else if (isTab) loadingText.innerText = "Switching View...";
                            else loadingText.innerText = "Loading Detail View...";
                        }
                        loadingOverlay.classList.add('active');
                    }
                }
            });

            // 2. Handle filter forms submit
            document.body.addEventListener('submit', function(e) {
                const form = e.target.closest('form');
                if (!form) return;

                const action = form.getAttribute('action') || '';
                const target = form.getAttribute('target') || '';

                // Skip forms that submit via AJAX or have target="_blank"
                if (action.includes('api.php') || target === '_blank') return;

                // Avoid forms that are marked as having manual JS submits
                if (form.id === 'locationForm' || form.id === 'blogForm' || form.id === 'websiteLeadFilterForm' || form.id === 'masterFilterForm') return;

                // Try to show suspense table loader first if a table is present on the page
                const table = document.querySelector('table.admin-table');
                if (table) {
                    const tableContainer = table.closest('.table-responsive') || table.closest('[style*="overflow-x"]') || table.parentElement;
                    if (tableContainer) {
                        let loader = tableContainer.querySelector('.table-loading-overlay');
                        if (!loader) {
                            tableContainer.style.position = 'relative';
                            const loaderHtml = `
                                <div class="table-loading-overlay" id="tableLocalLoader">
                                    <div class="suspense-spinner">
                                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                                        <span>Filtering Records...</span>
                                    </div>
                                </div>
                            `;
                            tableContainer.insertAdjacentHTML('afterbegin', loaderHtml);
                            loader = tableContainer.querySelector('.table-loading-overlay');
                        }
                        if (loader) {
                            loader.classList.add('active');
                        }
                        return;
                    }
                }

                if (loadingText) loadingText.innerText = "Filtering Records...";
                loadingOverlay.classList.add('active');
            });
        });

        function confirmAdminLogout() {
            Swal.fire({
                title: 'Logout Confirmation',
                text: "Are you sure you want to log out of the admin panel?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#475569',
                confirmButtonText: 'Yes, Logout',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }
    </script>

</body>
</html>
