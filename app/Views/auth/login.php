<?php
$pageTitle = 'Iniciar Sesión | Control de Estudios UPT Valencia';
require __DIR__ . '/../partials/header.php';

$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);
?>
<body>

    <div class="ce-auth-split">
        <!-- Panel Izquierdo Institucional (UPT Valencia) -->
        <div class="col-lg-6 ce-auth-hero d-none d-lg-flex">
            <div>
                <div class="ce-auth-logo-box">
                    <img src="<?= BASE_URL ?>assets/img/logo.jpg" alt="Logo UPT Valencia">
                    <div>
                        <div class="fw-bold text-white" style="font-size: 1.05rem;">UPT Valencia</div>
                        <div class="small" style="color: #fbbf24;">Universidad Politécnica Territorial de Valencia</div>
                    </div>
                </div>
            </div>

            <div class="my-auto py-4" style="max-width: 520px; z-index: 1;">
                <span class="ce-badge ce-badge-warning mb-3">
                    <span class="ce-badge-dot"></span>
                    Plataforma Académica Institucional
                </span>
                <h1 class="display-6 fw-bold text-white mb-3" style="letter-spacing: -0.02em;">
                    Sistema de Control de Estudios
                </h1>
                <p class="text-white-50 fs-6 mb-4" style="line-height: 1.65;">
                    Gestiona períodos académicos, asignaturas, planes de evaluación, inscripción de estudiantes, carga de calificaciones y reportes oficiales en un entorno unificado y seguro.
                </p>

                <div class="row g-3 pt-2">
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                            <div class="fw-bold text-white mb-1">100% Ponderado</div>
                            <div class="small text-white-50">Cálculo automático de notas finales y actas en PDF/Excel.</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                            <div class="fw-bold text-white mb-1">Acceso por Roles</div>
                            <div class="small text-white-50">Paneles dedicados para Administración y Personal Docente.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="small text-white-50 d-flex justify-content-between align-items-center" style="z-index: 1;">
                <span>&copy; <?= date('Y') ?> UPT Valencia · Control de Estudios</span>
                <span>Valencia, Estado Carabobo</span>
            </div>
        </div>

        <!-- Panel Derecho: Formulario de Acceso -->
        <div class="col-12 col-lg-6 ce-auth-form-panel">
            <div class="ce-auth-card">
                <div class="d-flex d-lg-none align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <img src="<?= BASE_URL ?>assets/img/logo.jpg" alt="Logo UPT Valencia" style="width: 46px; height: 46px; object-fit: contain;">
                    <div>
                        <div class="fw-bold text-dark">Control de Estudios</div>
                        <div class="small text-muted">UPT Valencia</div>
                    </div>
                </div>

                <div class="mb-4">
                    <h2 class="fw-bold text-dark mb-1" style="font-size: 1.6rem; letter-spacing: -0.02em;">Iniciar Sesión</h2>
                    <p class="text-muted small mb-0">Ingresa tus credenciales institucionales para acceder al panel.</p>
                </div>

                <form action="<?= BASE_URL ?>auth/authenticate" method="POST" novalidate>
                    <!-- Token CSRF de proteccion (SEC-01) -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Campo correo electrónico -->
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </span>
                            <input type="email"
                                   class="form-control"
                                   id="correo"
                                   name="correo"
                                   placeholder="correo@ejemplo.com"
                                   required
                                   value="<?= htmlspecialchars($old['correo'] ?? '') ?>"
                                   maxlength="150"
                                   autocomplete="email">
                        </div>
                    </div>

                    <!-- Campo contraseña con botón mostrar/ocultar -->
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   placeholder="Ingresa tu contraseña"
                                   required
                                   autocomplete="current-password">
                            <button class="btn btn-outline-secondary border-start-0" type="button" id="toggleLoginPassword" title="Mostrar u ocultar contraseña" style="border-color: #cbd5e1;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Botón de envío -->
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-upt-primary py-2 fs-6">
                            <span>Ingresar al Sistema</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </button>
                    </div>
                </form>

                <!-- Enlace al registro público de profesores -->
                <div class="text-center pt-3 border-top">
                    <span class="text-muted small">¿Eres profesor y aún no tienes cuenta?</span>
                    <a href="<?= BASE_URL ?>auth/register" class="fw-semibold text-decoration-none ms-1" style="color: var(--upt-blue-600);">
                        Regístrate aquí
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const btnToggle = document.getElementById('toggleLoginPassword');
            const inputPwd = document.getElementById('password');
            if (btnToggle && inputPwd) {
                btnToggle.addEventListener('click', function () {
                    inputPwd.type = inputPwd.type === 'password' ? 'text' : 'password';
                });
            }
        })();
    </script>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>