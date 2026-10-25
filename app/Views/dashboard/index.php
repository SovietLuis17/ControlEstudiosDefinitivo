<?php
$pageTitle = 'Dashboard | Control de Estudios UPT Valencia';
$activeModule = 'dashboard';
$pageHeading = $datos['rol'] === 'admin' ? 'Panel de Administración' : 'Panel Docente';
require __DIR__ . '/../partials/header.php';
?>
<body>
<div class="ce-app-wrapper">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="ce-content">
        <?php if ($datos['rol'] === 'admin'): ?>
            <!-- ============================================ -->
            <!-- DASHBOARD DEL ADMINISTRADOR                  -->
            <!-- ============================================ -->
            <div class="ce-page-header">
                <div>
                    <h1 class="ce-page-title">Panel de Administración General</h1>
                    <p class="ce-page-subtitle">Resumen ejecutivo de períodos, catálogo de asignaturas, planta docente, matrícula y secciones.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>reporte/index" class="btn btn-outline-secondary ce-action-btn bg-white py-2 px-3">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span>Generar Reportes</span>
                    </a>
                    <a href="<?= BASE_URL ?>seccion/index" class="btn btn-upt-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Gestionar Secciones</span>
                    </a>
                </div>
            </div>

            <!-- 5 Tarjetas KPI Simétricas con acceso directo -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3 mb-4">
                <div class="col">
                    <a href="<?= BASE_URL ?>periodo/index" class="ce-kpi-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="ce-kpi-icon amber">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </span>
                            <span class="ce-badge ce-badge-warning">Lapsos</span>
                        </div>
                        <div>
                            <div class="ce-kpi-value"><?= intval($datos['totalPeriodos']) ?></div>
                            <p class="ce-kpi-label">Períodos Académicos</p>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="<?= BASE_URL ?>materia/index" class="ce-kpi-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="ce-kpi-icon emerald">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            </span>
                            <span class="ce-badge ce-badge-success">Catálogo</span>
                        </div>
                        <div>
                            <div class="ce-kpi-value"><?= intval($datos['totalMaterias']) ?></div>
                            <p class="ce-kpi-label">Materias Registradas</p>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="<?= BASE_URL ?>profesor/index" class="ce-kpi-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="ce-kpi-icon blue">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </span>
                            <span class="ce-badge ce-badge-info">Docentes</span>
                        </div>
                        <div>
                            <div class="ce-kpi-value"><?= intval($datos['totalProfesores']) ?></div>
                            <p class="ce-kpi-label">Profesores y Admins</p>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="<?= BASE_URL ?>estudiante/index" class="ce-kpi-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="ce-kpi-icon purple">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </span>
                            <span class="ce-badge ce-badge-purple">Matrícula</span>
                        </div>
                        <div>
                            <div class="ce-kpi-value"><?= intval($datos['totalEstudiantes']) ?></div>
                            <p class="ce-kpi-label">Estudiantes Inscritos</p>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="<?= BASE_URL ?>seccion/index" class="ce-kpi-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="ce-kpi-icon rose">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                            </span>
                            <span class="ce-badge ce-badge-danger">Cursos</span>
                        </div>
                        <div>
                            <div class="ce-kpi-value"><?= intval($datos['totalSecciones']) ?></div>
                            <p class="ce-kpi-label">Secciones Abiertas</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Tabla de Últimas Secciones Registradas -->
            <div class="ce-card">
                <div class="ce-card-header">
                    <h5 class="ce-card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                        Últimas Secciones Registradas
                    </h5>
                    <a href="<?= BASE_URL ?>seccion/index" class="btn btn-sm btn-outline-primary ce-action-btn">
                        <span>Ver todas las secciones</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table ce-table">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Sección</th>
                                <th>Profesor Asignado</th>
                                <th>Período</th>
                                <th>Aula / Horario</th>
                                <th class="text-end">Accesos Rápidos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($datos['secciones'])): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        No hay secciones registradas actualmente.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($datos['secciones'], 0, 6) as $seccion): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="ce-code-pill"><?= htmlspecialchars($seccion['materia_codigo'] ?? '') ?></span>
                                                <span class="fw-semibold text-dark"><?= htmlspecialchars($seccion['materia_nombre'] ?? '') ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="ce-badge ce-badge-info">Sección <?= htmlspecialchars($seccion['codigo_seccion'] ?? '') ?></span>
                                        </td>
                                        <td>
                                            <span class="fw-medium"><?= htmlspecialchars($seccion['profesor_nombre'] ?? '') ?></span>
                                        </td>
                                        <td>
                                            <span class="ce-badge ce-badge-success">
                                                <span class="ce-badge-dot"></span>
                                                <?= htmlspecialchars($seccion['periodo_nombre'] ?? '') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted small"><?= htmlspecialchars($seccion['aula'] ?? 'Sin asignar') ?></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <a href="<?= BASE_URL ?>seccion/estudiantes/<?= $seccion['id'] ?>" class="btn btn-sm btn-outline-secondary ce-action-btn">
                                                    Inscritos
                                                </a>
                                                <a href="<?= BASE_URL ?>nota/index/<?= $seccion['id'] ?>" class="btn btn-sm btn-outline-primary ce-action-btn">
                                                    Notas
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <!-- ============================================ -->
            <!-- DASHBOARD DEL PROFESOR                       -->
            <!-- ============================================ -->
            <?php
            $totalSeccionesProf = count($datos['secciones']);
            $totalEstudiantesProf = 0;
            $sumaAvanceProf = 0;
            foreach ($datos['secciones'] as $secItem) {
                $totalEstudiantesProf += intval($secItem['total_estudiantes'] ?? 0);
                $sumaAvanceProf += intval($secItem['avance'] ?? 0);
            }
            $promedioAvanceProf = $totalSeccionesProf > 0 ? round($sumaAvanceProf / $totalSeccionesProf) : 0;
            ?>
            <div class="ce-page-header">
                <div>
                    <h1 class="ce-page-title">Bienvenido, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Profesor') ?></h1>
                    <p class="ce-page-subtitle">Consulta tus secciones asignadas, gestiona planes de evaluación y registra calificaciones.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>estudiante/index" class="btn btn-outline-secondary ce-action-btn bg-white py-2 px-3">
                        <span>Padrón de Estudiantes</span>
                    </a>
                    <a href="<?= BASE_URL ?>materia/index" class="btn btn-upt-primary">
                        <span>Mis Planes de Evaluación</span>
                    </a>
                </div>
            </div>

            <!-- 3 KPI Cards de Resumen Docente -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <a href="<?= BASE_URL ?>seccion/index" class="ce-kpi-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="ce-kpi-icon blue">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                            </span>
                            <span class="ce-badge ce-badge-info">Asignadas</span>
                        </div>
                        <div>
                            <div class="ce-kpi-value"><?= $totalSeccionesProf ?></div>
                            <p class="ce-kpi-label">Mis Secciones Activas</p>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="<?= BASE_URL ?>estudiante/index" class="ce-kpi-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="ce-kpi-icon purple">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            </span>
                            <span class="ce-badge ce-badge-purple">Inscritos</span>
                        </div>
                        <div>
                            <div class="ce-kpi-value"><?= $totalEstudiantesProf ?></div>
                            <p class="ce-kpi-label">Estudiantes en mis Secciones</p>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="<?= BASE_URL ?>reporte/index" class="ce-kpi-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="ce-kpi-icon emerald">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            </span>
                            <span class="ce-badge ce-badge-success">Progreso</span>
                        </div>
                        <div>
                            <div class="ce-kpi-value"><?= $promedioAvanceProf ?>%</div>
                            <p class="ce-kpi-label">Avance Promedio de Notas</p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Mis Secciones Asignadas</h5>
            </div>

            <?php if (empty($datos['secciones'])): ?>
                <div class="ce-card p-5 text-center">
                    <p class="text-muted mb-0">No tienes secciones asignadas actualmente. Contacta al administrador del sistema.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($datos['secciones'] as $seccion): ?>
                        <?php $avance = intval($seccion['avance'] ?? 0); ?>
                        <div class="col-md-6">
                            <div class="ce-card h-100 d-flex flex-column">
                                <div class="ce-card-header">
                                    <div>
                                        <span class="ce-code-pill me-2"><?= htmlspecialchars($seccion['materia_codigo'] ?? '') ?></span>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($seccion['materia_nombre'] ?? '') ?></span>
                                    </div>
                                    <span class="ce-badge ce-badge-info">
                                        Sección <?= htmlspecialchars($seccion['codigo_seccion'] ?? '') ?>
                                    </span>
                                </div>
                                <div class="ce-card-body flex-grow-1 d-flex flex-column justify-content-between">
                                    <div class="row g-2 mb-3 small">
                                        <div class="col-6">
                                            <span class="text-muted d-block">Período Académico</span>
                                            <strong class="text-dark"><?= htmlspecialchars($seccion['periodo_nombre'] ?? '') ?></strong>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block">Estudiantes Inscritos</span>
                                            <strong class="text-dark tabular-nums"><?= intval($seccion['total_estudiantes'] ?? 0) ?> alumno(s)</strong>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block">Aula</span>
                                            <strong class="text-dark"><?= htmlspecialchars($seccion['aula'] ?? 'Sin asignar') ?></strong>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block">Horario</span>
                                            <strong class="text-dark"><?= htmlspecialchars($seccion['horario'] ?? 'Sin asignar') ?></strong>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="d-flex justify-content-between align-items-center small mb-1">
                                            <span class="fw-semibold text-secondary">Avance de calificaciones</span>
                                            <span class="fw-bold tabular-nums <?= $avance >= 100 ? 'text-success' : 'text-dark' ?>">
                                                <?= $avance ?>% (<?= intval($seccion['total_notas'] ?? 0) ?> / <?= intval($seccion['total_esperado'] ?? 0) ?>)
                                            </span>
                                        </div>
                                        <div class="progress mb-3" style="height: 8px; border-radius: 999px; background-color: #e2e8f0;">
                                            <div class="progress-bar <?= $avance >= 100 ? 'bg-success' : 'bg-primary' ?>"
                                                 role="progressbar"
                                                 style="width: <?= $avance ?>%; border-radius: 999px;"></div>
                                        </div>

                                        <div class="d-flex gap-2 pt-2 border-top">
                                            <a href="<?= BASE_URL ?>seccion/estudiantes/<?= $seccion['id'] ?>" class="btn btn-sm btn-outline-secondary ce-action-btn flex-fill justify-content-center py-2">
                                                <span>Ver Estudiantes</span>
                                            </a>
                                            <a href="<?= BASE_URL ?>nota/index/<?= $seccion['id'] ?>" class="btn btn-sm btn-upt-primary flex-fill justify-content-center py-2">
                                                <span>Cargar / Ver Notas</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>