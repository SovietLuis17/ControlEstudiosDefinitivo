<?php
// app/Views/partials/sidebar.php
// Layout Maestro: Barra lateral fija institucional + Topbar superior contextual con Logout seguro via POST (SEC-01)
// Incluye acceso directo al modulo de autogestion "Mi Perfil y Seguridad" (`perfil/index`)
$rolActual = $_SESSION['rol'] ?? 'profesor';
$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';
$activeModule = $activeModule ?? 'dashboard';
$pageHeading = $pageHeading ?? 'Panel Principal';
$csrfToken = $_SESSION['csrf_token'] ?? '';

// Calcular iniciales del usuario para el avatar
$partesNombre = preg_split('/\s+/', trim($nombreUsuario));
$iniciales = strtoupper(substr($partesNombre[0] ?? 'U', 0, 1) . substr($partesNombre[1] ?? '', 0, 1));
?>
<!-- Fondo oscuro para menu movil -->
<div class="ce-sidebar-backdrop" id="ceSidebarBackdrop"></div>

<!-- Barra lateral institucional UPT Valencia -->
<aside class="ce-sidebar" id="ceSidebar">
    <a href="<?= BASE_URL ?>dashboard/index" class="ce-sidebar-brand">
        <img src="<?= BASE_URL ?>assets/img/logo.jpg" alt="Logo UPT Valencia" class="ce-brand-logo">
        <div class="ce-brand-text">
            <span class="ce-brand-title">Control de Estudios</span>
            <span class="ce-brand-subtitle">UPT Valencia</span>
        </div>
    </a>

    <nav class="ce-sidebar-nav">
        <div class="ce-nav-section-label">Principal</div>
        <a href="<?= BASE_URL ?>dashboard/index" class="ce-nav-link <?= $activeModule === 'dashboard' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
            <span>Dashboard</span>
        </a>

        <?php if ($rolActual === 'admin'): ?>
            <div class="ce-nav-section-label">Gestión Académica</div>
            <a href="<?= BASE_URL ?>periodo/index" class="ce-nav-link <?= $activeModule === 'periodo' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Períodos Académicos</span>
            </a>
            <a href="<?= BASE_URL ?>materia/index" class="ce-nav-link <?= $activeModule === 'materia' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                <span>Materias y Planes</span>
            </a>
            <a href="<?= BASE_URL ?>profesor/index" class="ce-nav-link <?= $activeModule === 'profesor' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Profesores</span>
            </a>
            <a href="<?= BASE_URL ?>estudiante/index" class="ce-nav-link <?= $activeModule === 'estudiante' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Estudiantes</span>
            </a>
            <a href="<?= BASE_URL ?>seccion/index" class="ce-nav-link <?= ($activeModule === 'seccion' || $activeModule === 'nota') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                <span>Secciones y Notas</span>
            </a>
        <?php else: ?>
            <div class="ce-nav-section-label">Gestión Docente</div>
            <a href="<?= BASE_URL ?>seccion/index" class="ce-nav-link <?= ($activeModule === 'seccion' || $activeModule === 'nota') ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                <span>Mis Secciones y Notas</span>
            </a>
            <a href="<?= BASE_URL ?>materia/index" class="ce-nav-link <?= $activeModule === 'materia' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                <span>Mis Materias y Planes</span>
            </a>
            <a href="<?= BASE_URL ?>estudiante/index" class="ce-nav-link <?= $activeModule === 'estudiante' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Padrón de Estudiantes</span>
            </a>
        <?php endif; ?>

        <div class="ce-nav-section-label">Exportación y Cuenta</div>
        <a href="<?= BASE_URL ?>reporte/index" class="ce-nav-link <?= $activeModule === 'reporte' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <span>Centro de Reportes</span>
        </a>
        <a href="<?= BASE_URL ?>perfil/index" class="ce-nav-link <?= $activeModule === 'perfil' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><circle cx="12" cy="10" r="3"/></svg>
            <span>Mi Perfil y Contraseña</span>
        </a>
    </nav>

    <div class="ce-sidebar-footer">
        <div class="d-flex align-items-center justify-content-between">
            <a href="<?= BASE_URL ?>perfil/index" class="d-flex align-items-center gap-2 overflow-hidden text-decoration-none" title="Ir a Mi Perfil">
                <div class="ce-avatar ce-avatar-sm <?= $rolActual === 'admin' ? 'ce-avatar-amber' : '' ?>">
                    <?= htmlspecialchars($iniciales) ?>
                </div>
                <div class="text-truncate">
                    <div class="text-white fw-semibold small text-truncate"><?= htmlspecialchars($nombreUsuario) ?></div>
                    <div class="text-white-50" style="font-size: 0.72rem;">
                        <?= $rolActual === 'admin' ? 'Administrador' : 'Profesor Docente' ?>
                    </div>
                </div>
            </a>
            <form action="<?= BASE_URL ?>auth/logout" method="POST" class="m-0">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-sm btn-outline-light border-0 p-1" title="Cerrar sesión">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Contenedor Principal -->
<div class="ce-main-wrapper">
    <!-- Barra Superior Contextual -->
    <header class="ce-topbar">
        <div class="ce-topbar-left">
            <button type="button" class="btn btn-sm btn-light border d-lg-none" id="ceSidebarToggle" aria-label="Abrir menú">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <p class="ce-breadcrumb-text">
                UPT Valencia <span class="mx-1">/</span> <strong><?= htmlspecialchars($pageHeading) ?></strong>
            </p>
        </div>

        <div class="ce-topbar-right">
            <span class="ce-badge ce-badge-success d-none d-md-inline-flex">
                <span class="ce-badge-dot"></span>
                Sistema Activo
            </span>

            <a href="<?= BASE_URL ?>perfil/index" class="ce-user-chip text-decoration-none" title="Configurar Mi Perfil y Contraseña">
                <span class="ce-avatar ce-avatar-sm <?= $rolActual === 'admin' ? 'ce-avatar-amber' : '' ?>">
                    <?= htmlspecialchars($iniciales) ?>
                </span>
                <span class="d-none d-sm-inline fw-semibold small text-dark">
                    <?= htmlspecialchars($nombreUsuario) ?>
                </span>
                <span class="ce-badge <?= $rolActual === 'admin' ? 'ce-badge-warning' : 'ce-badge-info' ?> py-1 px-2" style="font-size: 0.7rem;">
                    <?= htmlspecialchars(ucfirst($rolActual)) ?>
                </span>
            </a>

            <form action="<?= BASE_URL ?>auth/logout" method="POST" class="m-0">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger ce-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span class="d-none d-md-inline">Salir</span>
                </button>
            </form>
        </div>
    </header>
