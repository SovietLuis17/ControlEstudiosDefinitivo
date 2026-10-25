<?php
// app/Views/seccion/estudiantes.php
// Vista de estudiantes inscritos en una seccion especifica (`estudiantes.seccion_id`)
// Compatible al 100% con el esquema de base de datos en produccion.
// Incluye:
// - Visualizacion de ocupacion de cupo (`inscritos / cupo_maximo`) y bloqueo si el cupo se agota.
// - Modo de solo lectura automatico cuando el periodo academico esta cerrado (`periodo_estado = 'cerrado'`).
// - Descarga directa del Boletin Individual de Calificaciones del Estudiante en PDF (`reporte/kardexPdf`).
$pageTitle = 'Estudiantes de la Sección | Control de Estudios UPT Valencia';
$activeModule = 'seccion';
$pageHeading = 'Inscritos · ' . ($seccion['materia_codigo'] ?? '') . ' Sec ' . ($seccion['codigo_seccion'] ?? '');
require __DIR__ . '/../partials/header.php';

$inscritosActuales = count($estudiantes);
$cupoMaximo = max(1, intval($seccion['cupo_maximo'] ?? 30));
$cupoAgotado = $inscritosActuales >= $cupoMaximo;
$periodoCerrado = ($seccion['periodo_estado'] ?? 'activo') === 'cerrado';
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
                    <?= htmlspecialchars($seccion['materia_nombre'] ?? '') ?> · Sección <?= htmlspecialchars($seccion['codigo_seccion'] ?? '') ?>
                </h1>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="<?= BASE_URL ?>nota/index/<?= $seccion['id'] ?>" class="btn btn-upt-amber">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>Ver / Cargar Notas</span>
                </a>
                <a href="<?= BASE_URL ?>reporte/inscritosExcel/<?= $seccion['id'] ?>" target="_blank" class="btn btn-outline-success ce-action-btn bg-white px-3">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Inscritos Excel</span>
                </a>
                <?php if (!$periodoCerrado): ?>
                    <button type="button"
                            class="btn btn-upt-primary"
                            id="btnInscribirEstudiante"
                            <?= $cupoAgotado ? 'disabled title="El cupo máximo de la sección está agotado"' : '' ?>>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span><?= $cupoAgotado ? 'Cupo Completo (' . $cupoMaximo . ')' : 'Inscribir Estudiante' ?></span>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($periodoCerrado): ?>
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-2 mb-4" style="border-radius: 12px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <span><strong>Período Académico Cerrado (<?= htmlspecialchars($seccion['periodo_nombre'] ?? '') ?>):</strong> La nómina de esta sección se encuentra bloqueada en modo de solo lectura. No se permiten nuevas inscripciones ni retiros.</span>
            </div>
        <?php elseif ($cupoAgotado): ?>
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2 mb-4" style="border-radius: 12px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span><strong>Cupo máximo alcanzado:</strong> Esta sección ya cuenta con <?= $inscritosActuales ?> de <?= $cupoMaximo ?> estudiantes permitidos.</span>
            </div>
        <?php endif; ?>

        <!-- Banner Contextual de la Sección -->
        <div class="ce-context-banner">
            <div class="row g-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="ce-meta-item">
                        <div class="ce-meta-label">Asignatura</div>
                        <p class="ce-meta-value"><?= htmlspecialchars($seccion['materia_nombre'] ?? '') ?></p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="ce-meta-item">
                        <div class="ce-meta-label">Docente Responsable</div>
                        <p class="ce-meta-value"><?= htmlspecialchars($seccion['profesor_nombre'] ?? '') ?></p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="ce-meta-item">
                        <div class="ce-meta-label">Período Académico</div>
                        <p class="ce-meta-value">
                            <?= htmlspecialchars($seccion['periodo_nombre'] ?? '') ?>
                            (<?= $periodoCerrado ? 'Cerrado' : 'Activo' ?>)
                        </p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="ce-meta-item">
                        <div class="ce-meta-label">Aula / Ocupación de Cupo</div>
                        <p class="ce-meta-value tabular-nums">
                            <?= htmlspecialchars($seccion['aula'] ?? 'Sin asignar') ?> · <?= $inscritosActuales ?> / <?= $cupoMaximo ?> cupos
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Estudiantes Inscritos en la Sección -->
        <div class="ce-card">
            <div class="ce-card-header">
                <h5 class="ce-card-title">Nómina de Estudiantes Inscritos</h5>
                <span class="ce-badge <?= $cupoAgotado ? 'ce-badge-danger' : 'ce-badge-neutral' ?> tabular-nums">
                    Ocupación: <?= $inscritosActuales ?> / <?= $cupoMaximo ?> estudiante(s)
                </span>
            </div>
            <div class="table-responsive">
                <table class="table ce-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cédula</th>
                            <th>Estudiante</th>
                            <th>Edad</th>
                            <th>Carrera</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($estudiantes)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    No hay estudiantes inscritos en esta sección todavía.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($estudiantes as $estudiante): ?>
                                <?php
                                $iniInsc = strtoupper(substr($estudiante['nombres'] ?? 'E', 0, 1) . substr($estudiante['apellidos'] ?? '', 0, 1));
                                ?>
                                <tr>
                                    <td class="tabular-nums text-muted">#<?= htmlspecialchars($estudiante['id']) ?></td>
                                    <td>
                                        <span class="ce-code-pill tabular-nums"><?= htmlspecialchars($estudiante['cedula']) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="ce-avatar ce-avatar-sm"><?= htmlspecialchars($iniInsc) ?></span>
                                            <div>
                                                <div class="fw-bold text-dark">
                                                    <?= htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?>
                                                </div>
                                                <?php if (!empty($estudiante['correo'])): ?>
                                                    <div class="text-muted small"><?= htmlspecialchars($estudiante['correo']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="tabular-nums"><?= htmlspecialchars($estudiante['edad'] ?? '—') ?> años</td>
                                    <td>
                                        <span class="ce-badge ce-badge-neutral"><?= htmlspecialchars($estudiante['carrera'] ?? 'Sin asignar') ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex flex-wrap justify-content-center gap-1">
                                            <a href="<?= BASE_URL ?>reporte/kardexPdf/<?= htmlspecialchars($estudiante['id']) ?>"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-primary ce-action-btn"
                                               title="Descargar Boletín Individual del Estudiante en PDF">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                <span>Boletín PDF</span>
                                            </a>

                                            <?php if (!$periodoCerrado): ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger ce-action-btn js-quitar-estudiante"
                                                        data-id="<?= htmlspecialchars($estudiante['id']) ?>"
                                                        data-nombre="<?= htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?>">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                                                    <span>Retirar de Sección</span>
                                                </button>
                                            <?php endif; ?>
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

<!-- Modal para inscribir un estudiante en la sección -->
<div class="modal fade" id="modalInscripcion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Inscribir Estudiante en Sección</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formInscripcion">
                <div class="modal-body">
                    <input type="hidden" name="seccion_id" value="<?= htmlspecialchars($seccion['id']) ?>">

                    <div class="mb-2">
                        <label for="estudiante_id" class="form-label">Selecciona un estudiante disponible (sin sección) *</label>
                        <?php if (empty($estudiantesDisponibles)): ?>
                            <div class="alert alert-warning mb-0">
                                No hay estudiantes disponibles para inscribir. Todos los estudiantes registrados ya pertenecen a una sección.
                            </div>
                        <?php else: ?>
                            <input type="text"
                                   id="filtroEstudianteModal"
                                   class="form-control form-control-sm mb-2"
                                   placeholder="Filtrar rápido por cédula o nombre...">
                            <select class="form-select" id="estudiante_id" name="estudiante_id" required>
                                <option value="">Selecciona un estudiante...</option>
                                <?php foreach ($estudiantesDisponibles as $disponible): ?>
                                    <option value="<?= htmlspecialchars($disponible['id']) ?>">
                                        <?= htmlspecialchars($disponible['cedula']) ?> -
                                        <?= htmlspecialchars($disponible['nombres'] . ' ' . $disponible['apellidos']) ?>
                                        (<?= htmlspecialchars($disponible['carrera'] ?? 'Sin carrera') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                Cupos disponibles en esta sección: <strong><?= max(0, $cupoMaximo - $inscritosActuales) ?></strong> de <?= $cupoMaximo ?>.
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <?php if (!empty($estudiantesDisponibles) && !$cupoAgotado && !$periodoCerrado): ?>
                        <button type="submit" class="btn btn-upt-primary">Inscribir en Sección</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const inputFiltro = document.getElementById('filtroEstudianteModal');
        const selectEst = document.getElementById('estudiante_id');
        if (inputFiltro && selectEst) {
            const opcionesOriginales = Array.from(selectEst.options);
            inputFiltro.addEventListener('input', function () {
                const termino = inputFiltro.value.toLowerCase().trim();
                selectEst.innerHTML = '';
                opcionesOriginales.forEach(function (opt) {
                    if (opt.value === '' || opt.textContent.toLowerCase().includes(termino)) {
                        selectEst.appendChild(opt);
                    }
                });
            });
        }
    })();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
<script src="<?= BASE_URL ?>assets/js/seccion_estudiantes.js"></script>
</body>
</html>