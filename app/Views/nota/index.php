<?php
// app/Views/nota/index.php
// Vista de Resumen y Carga de Notas de una Seccion
// ACTUALIZACION FUNCIONAL:
// - Si el periodo academico esta cerrado (`periodo_estado = 'cerrado'`), muestra banner de bloqueo
//   y cambia el modal a modo de solo lectura (sin permitir guardar modificaciones).
// - Incluye boton directo para descargar el Boletin / Kardex PDF individual de cada estudiante.
$pageTitle = 'Notas de la Sección | Control de Estudios UPT Valencia';
$activeModule = 'nota';
$pageHeading = 'Calificaciones · ' . ($seccion['materia_codigo'] ?? '') . ' Sec ' . ($seccion['codigo_seccion'] ?? '');
require __DIR__ . '/../partials/header.php';

// Calcular estadísticas rápidas de la sección para las 4 tarjetas KPI superiores
$totalInscritos = count($resumen);
$totalAprobados = 0;
$totalReprobados = 0;
$totalEnCurso = 0;
$totalActividadesPlan = count($actividades);
$periodoCerrado = ($seccion['periodo_estado'] ?? 'activo') === 'cerrado';

foreach ($resumen as $itemResumen) {
    if ($itemResumen['estado'] === 'aprobado') {
        $totalAprobados++;
    } elseif ($itemResumen['estado'] === 'reprobado') {
        $totalReprobados++;
    } else {
        $totalEnCurso++;
    }
}
?>
<body>
<div class="ce-app-wrapper">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="ce-content">
        <div class="ce-page-header">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="<?= BASE_URL ?>seccion/index" class="btn btn-sm btn-outline-secondary ce-action-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        <span>Volver a Secciones</span>
                    </a>
                    <span class="ce-code-pill"><?= htmlspecialchars($seccion['materia_codigo'] ?? '') ?></span>
                    <?php if ($periodoCerrado): ?>
                        <span class="ce-badge ce-badge-danger">Período Cerrado · Solo Lectura</span>
                    <?php endif; ?>
                </div>
                <h1 class="ce-page-title mb-0">
                    Control de Notas: <?= htmlspecialchars($seccion['materia_nombre'] ?? '') ?> (Sección <?= htmlspecialchars($seccion['codigo_seccion'] ?? '') ?>)
                </h1>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="<?= BASE_URL ?>seccion/estudiantes/<?= $seccion['id'] ?>" class="btn btn-outline-secondary ce-action-btn bg-white px-3">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <span>Ver Estudiantes</span>
                </a>
                <a href="<?= BASE_URL ?>reporte/notasPdf/<?= $seccion['id'] ?>" target="_blank" class="btn btn-danger ce-action-btn px-3">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>Notas PDF</span>
                </a>
                <a href="<?= BASE_URL ?>reporte/notasExcel/<?= $seccion['id'] ?>" target="_blank" class="btn btn-success ce-action-btn px-3">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Notas Excel</span>
                </a>
            </div>
        </div>

        <?php if ($periodoCerrado): ?>
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-2 mb-4" style="border-radius: 12px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <span><strong>Acta Cerrada (Período <?= htmlspecialchars($seccion['periodo_nombre'] ?? '') ?>):</strong> Este período académico ya fue cerrado. Puedes consultar el desglose de calificaciones y descargar reportes, pero no se permite modificar notas.</span>
            </div>
        <?php endif; ?>

        <!-- Banner Contextual de la Sección y Plan Evaluativo -->
        <div class="ce-context-banner">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="ce-meta-item">
                        <div class="ce-meta-label">Asignatura y Período</div>
                        <p class="ce-meta-value">
                            <?= htmlspecialchars($seccion['materia_nombre'] ?? '') ?> · <?= htmlspecialchars($seccion['periodo_nombre'] ?? '') ?>
                            (<?= $periodoCerrado ? 'Cerrado' : 'Activo' ?>)
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ce-meta-item">
                        <div class="ce-meta-label">Profesor Responsable</div>
                        <p class="ce-meta-value"><?= htmlspecialchars($seccion['profesor_nombre'] ?? '') ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ce-meta-item">
                        <div class="ce-meta-label">Ocupación / Regla de Aprobación</div>
                        <p class="ce-meta-value"><?= $totalInscritos ?> / <?= intval($seccion['cupo_maximo'] ?? 30) ?> cupos · Mínimo 13.00 / 20 pts</p>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 pt-1">
                <span class="small text-white-50 fw-semibold me-1">Plan de Evaluación (100%):</span>
                <?php foreach ($actividades as $actividad): ?>
                    <span class="badge rounded-pill bg-light text-dark px-3 py-1 fw-semibold">
                        <?= htmlspecialchars($actividad['nombre_actividad']) ?>: <?= number_format($actividad['porcentaje'], 0) ?>%
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 4 Tarjetas KPI de Estadísticas de la Sección -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="ce-kpi-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="ce-kpi-label">Total Inscritos</span>
                        <span class="ce-kpi-icon blue" style="width: 36px; height: 36px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        </span>
                    </div>
                    <div class="ce-kpi-value mb-0 mt-2"><?= $totalInscritos ?> <small class="text-muted fs-6">/ <?= intval($seccion['cupo_maximo'] ?? 30) ?></small></div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="ce-kpi-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="ce-kpi-label">Aprobados (&ge; 13)</span>
                        <span class="ce-kpi-icon emerald" style="width: 36px; height: 36px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                    </div>
                    <div class="ce-kpi-value text-success mb-0 mt-2"><?= $totalAprobados ?></div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="ce-kpi-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="ce-kpi-label">Reprobados (&lt; 13)</span>
                        <span class="ce-kpi-icon rose" style="width: 36px; height: 36px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </span>
                    </div>
                    <div class="ce-kpi-value text-danger mb-0 mt-2"><?= $totalReprobados ?></div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="ce-kpi-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="ce-kpi-label">En Curso / Sin Notas</span>
                        <span class="ce-kpi-icon amber" style="width: 36px; height: 36px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </span>
                    </div>
                    <div class="ce-kpi-value text-warning mb-0 mt-2"><?= $totalEnCurso ?></div>
                </div>
            </div>
        </div>

        <!-- Tabla de Resumen de Calificaciones -->
        <div class="ce-card">
            <div class="ce-card-header">
                <div>
                    <h5 class="ce-card-title">Registro de Calificaciones por Estudiante</h5>
                    <small class="text-muted">La nota final ponderada y el estado definitivo se calculan al completar el 100% de las actividades.</small>
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <span class="ce-badge ce-badge-success"><span class="ce-badge-dot"></span>Aprobado (&ge; 13)</span>
                    <span class="ce-badge ce-badge-danger"><span class="ce-badge-dot"></span>Reprobado (&lt; 13)</span>
                    <span class="ce-badge ce-badge-warning"><span class="ce-badge-dot"></span>En curso</span>
                    <span class="ce-badge ce-badge-neutral"><span class="ce-badge-dot"></span>Sin notas</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table ce-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cédula</th>
                            <th>Estudiante</th>
                            <th>Actividades Evaluadas</th>
                            <th class="text-center">Nota Final (0-20)</th>
                            <th class="text-center">Estado Académico</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resumen)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    No hay estudiantes inscritos en esta sección.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($resumen as $fila): ?>
                                <?php
                                $iniNota = strtoupper(substr($fila['nombres'] ?? 'E', 0, 1) . substr($fila['apellidos'] ?? '', 0, 1));
                                $notasCargadasEst = intval($fila['total_notas'] ?? 0);
                                $pctEst = $totalActividadesPlan > 0 ? round(($notasCargadasEst / $totalActividadesPlan) * 100) : 0;
                                ?>
                                <tr>
                                    <td class="tabular-nums text-muted">#<?= htmlspecialchars($fila['estudiante_id']) ?></td>
                                    <td>
                                        <span class="ce-code-pill tabular-nums"><?= htmlspecialchars($fila['cedula']) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="ce-avatar ce-avatar-sm"><?= htmlspecialchars($iniNota) ?></span>
                                            <span class="fw-bold text-dark">
                                                <?= htmlspecialchars($fila['nombres'] . ' ' . $fila['apellidos']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td style="min-width: 160px;">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted"><?= $notasCargadasEst ?> de <?= $totalActividadesPlan ?></span>
                                            <span class="fw-semibold tabular-nums"><?= $pctEst ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 999px;">
                                            <div class="progress-bar <?= $pctEst >= 100 ? 'bg-success' : 'bg-primary' ?>" style="width: <?= $pctEst ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="text-center tabular-nums">
                                        <?php if ($fila['estado'] === 'sin_notas' || $fila['estado'] === 'en_curso'): ?>
                                            <span class="text-muted fw-semibold">---</span>
                                        <?php else: ?>
                                            <span class="fs-6 fw-bold <?= $fila['estado'] === 'aprobado' ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($fila['nota_final'], 2) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($fila['estado'] === 'aprobado'): ?>
                                            <span class="ce-badge ce-badge-success"><span class="ce-badge-dot"></span>Aprobado</span>
                                        <?php elseif ($fila['estado'] === 'reprobado'): ?>
                                            <span class="ce-badge ce-badge-danger"><span class="ce-badge-dot"></span>Reprobado</span>
                                        <?php elseif ($fila['estado'] === 'en_curso'): ?>
                                            <span class="ce-badge ce-badge-warning"><span class="ce-badge-dot"></span>En curso</span>
                                        <?php else: ?>
                                            <span class="ce-badge ce-badge-neutral"><span class="ce-badge-dot"></span>Sin notas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex flex-wrap justify-content-center gap-1">
                                            <button type="button"
                                                    class="btn btn-sm <?= $periodoCerrado ? 'btn-outline-secondary' : 'btn-upt-primary' ?> ce-action-btn js-ver-notas"
                                                    data-estudiante-id="<?= htmlspecialchars($fila['estudiante_id']) ?>"
                                                    data-seccion-id="<?= htmlspecialchars($seccion['id']) ?>"
                                                    data-nombre="<?= htmlspecialchars($fila['nombres'] . ' ' . $fila['apellidos']) ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                <span><?= $periodoCerrado ? 'Consultar Notas' : 'Calificar / Editar' ?></span>
                                            </button>
                                            <a href="<?= BASE_URL ?>reporte/kardexPdf/<?= htmlspecialchars($fila['estudiante_id']) ?>"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-danger ce-action-btn"
                                               title="Descargar Boletín / Kárdex Individual en PDF">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                <span>Boletín PDF</span>
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
    </main>
</div>

<!-- Modal para ver y editar notas de un estudiante específico -->
<div class="modal fade" id="modalNotas" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalNotas">Notas del Estudiante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNotas">
                <div class="modal-body">
                    <input type="hidden" name="estudiante_id" id="notaEstudianteId" value="0">
                    <input type="hidden" name="seccion_id" id="notaSeccionId" value="0">

                    <div class="p-3 mb-3 rounded-3 border bg-light d-flex align-items-center justify-content-between" id="infoEstudiante">
                        Cargando datos del estudiante...
                    </div>

                    <div class="table-responsive rounded-3 border">
                        <table class="table ce-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Actividad del Plan</th>
                                    <th class="text-center">Ponderación</th>
                                    <th class="text-center" style="width: 190px;">Calificación (0 - 20)</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoActividades">
                                <!-- Se llena dinámicamente con JavaScript -->
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th class="fw-bold text-dark">NOTA FINAL PONDERADA</th>
                                    <th class="text-center tabular-nums">100%</th>
                                    <th class="text-center fs-5 fw-bold tabular-nums" id="notaFinalCalculada">---</th>
                                </tr>
                                <tr>
                                    <th class="fw-bold text-dark">ESTADO ACADÉMICO</th>
                                    <th colspan="2" class="text-center" id="estadoEstudiante">---</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <?php if (!$periodoCerrado): ?>
                        <button type="submit" class="btn btn-upt-primary" id="btnGuardarNotasModal">Guardar Calificaciones</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
<script src="<?= BASE_URL ?>assets/js/nota.js"></script>
</body>
</html>