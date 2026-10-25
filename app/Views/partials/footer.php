<?php
// app/Views/partials/footer.php
// Cierre unificado de scripts globales, interceptor CSRF automatico para Fetch API (SEC-01), manejo del sidebar movil y alertas SweetAlert2
?>
    <!-- Bootstrap JS Local -->
    <script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>

    <!-- Definición global de BASE_URL, Interceptor CSRF y toggle de Sidebar móvil -->
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
        window.CSRF_TOKEN = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

        (function () {
            // Interceptor transparente de Fetch API para adjuntar el token CSRF en toda peticion POST (SEC-01)
            const originalFetch = window.fetch;
            if (originalFetch) {
                window.fetch = function (input, init) {
                    init = init || {};
                    const method = (init.method || 'GET').toUpperCase();
                    if (method === 'POST' && window.CSRF_TOKEN) {
                        init.headers = Object.assign({}, init.headers || {}, {
                            'X-CSRF-Token': window.CSRF_TOKEN
                        });
                        if (init.body instanceof FormData && !init.body.has('csrf_token')) {
                            init.body.append('csrf_token', window.CSRF_TOKEN);
                        }
                    }
                    return originalFetch.call(this, input, init);
                };
            }

            // Toggle del Sidebar en dispositivos moviles
            const toggleBtn = document.getElementById('ceSidebarToggle');
            const sidebar = document.getElementById('ceSidebar');
            const backdrop = document.getElementById('ceSidebarBackdrop');

            if (toggleBtn && sidebar && backdrop) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('show');
                    backdrop.classList.toggle('show');
                });
                backdrop.addEventListener('click', function () {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                });
            }
        })();

        <?php if (isset($_SESSION['swal'])): ?>
            Swal.fire({
                icon: '<?= htmlspecialchars($_SESSION['swal']['icon'] ?? 'info') ?>',
                title: '<?= htmlspecialchars($_SESSION['swal']['title'] ?? '') ?>',
                text: '<?= htmlspecialchars($_SESSION['swal']['text'] ?? '') ?>',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#2563eb'
            });
            <?php unset($_SESSION['swal']); ?>
        <?php endif; ?>
    </script>
