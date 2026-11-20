    </main>

    <!-- Global Page Loading Overlay -->
    <div class="page-loading-overlay" id="pageLoadingOverlay">
        <div class="loading-spinner"></div>
        <div class="loading-text" id="loadingText">Loading...</div>
    </div>

    <script>
        // When the server ends an idle session, any AJAX call gets 401 SESSION_EXPIRED: send the user to login
        // instead of showing a confusing error.
        (function () {
            if (!window.fetch || window.__waqtFetchWrapped) return;
            window.__waqtFetchWrapped = true;
            const originalFetch = window.fetch.bind(window);
            window.fetch = function () {
                return originalFetch.apply(null, arguments).then(function (response) {
                    if (response.status === 401) {
                        response.clone().json().then(function (body) {
                            if (body && body.code === 'SESSION_EXPIRED') {
                                window.location.href = 'login.php?reason=timeout';
                            }
                        }).catch(function () {});
                    }
                    return response;
                });
            };
        })();
        document.addEventListener('DOMContentLoaded', function() {
            const loadingOverlay = document.getElementById('pageLoadingOverlay');
            const loadingText = document.getElementById('loadingText');
            if (!loadingOverlay) return;

            // 1. Handle standard link clicks that cause page reload
            document.body.addEventListener('click', function(e) {
                // Skip if default behavior was already prevented by local AJAX handlers
                if (e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                const target = link.getAttribute('target');
                const classList = link.classList;

                // Skip non-navigational links
                if (!href || href === '#' || href === '' || href.startsWith('javascript:') || href.startsWith('#')) return;
                if ((target && target !== '_self') || link.hasAttribute('download')) return;
                const destination = new URL(link.href, location.href);
                if (destination.origin !== location.origin || !/^https?:$/.test(destination.protocol)) return;

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

            function clearLoaders() { document.querySelectorAll('.page-loading-overlay, .table-loading-overlay').forEach(el => el.classList.remove('active')); }
            window.addEventListener('pageshow', clearLoaders);
            window.addEventListener('focus', clearLoaders);
            // A cancelled beforeunload prompt keeps the current page active.
            window.addEventListener('beforeunload', () => setTimeout(clearLoaders, 1000));

            // 2. Handle filter forms submit
            document.body.addEventListener('submit', function(e) {
                if (e.defaultPrevented) return;
                const form = e.target.closest('form');
                if (!form) return;

                const action = form.getAttribute('action') || '';
                const target = form.getAttribute('target') || '';

                // Skip forms that submit via AJAX or have target="_blank"
                if (action.includes('api.php') || target === '_blank' || form.hasAttribute('data-ajax-form')) return;

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

        /**
         * Shared toast for quick, non-blocking confirmations.
         * adminToast('Saved', 'success', { actionText: 'Undo', onAction: fn })
         * Stays 4s (6s with an action), pauses while hovered, and has a close button.
         */
        window.adminToast = function (message, type = 'success', opts = {}) {
            if (typeof Swal === 'undefined') return Promise.resolve();
            const hasAction = !!(opts.actionText && typeof opts.onAction === 'function');
            return Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                // Plain glyphs: SweetAlert's animated icons distort at toast size.
                iconHtml: '<i class="fa-solid ' + ({ success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' }[type] || 'fa-circle-info') + '"></i>',
                title: message,
                showConfirmButton: hasAction,
                confirmButtonText: opts.actionText || '',
                showCloseButton: true,
                closeButtonAriaLabel: 'Dismiss',
                timer: opts.timer || (hasAction ? 6000 : 4000),
                timerProgressBar: true,
                customClass: { popup: 'toast-' + type },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            }).then(result => {
                if (hasAction && result.isConfirmed) opts.onAction();
                return result;
            });
        };

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
                    const form = document.createElement('form');
                    form.method = 'post'; form.action = 'logout.php';
                    const token = document.createElement('input');
                    token.type = 'hidden'; token.name = 'csrf_token';
                    token.value = <?php echo json_encode($_SESSION['csrf_token'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                    form.appendChild(token); document.body.appendChild(form); form.submit();
                }
            });
        }
    </script>

</body>
</html>
