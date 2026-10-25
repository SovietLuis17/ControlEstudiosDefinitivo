<?php
$pageTitle = 'Plan de Evaluación | Control de Estudios UPT Valencia';
$activeModule = 'materia';
$pageHeading = 'Plan de Evaluación · ' . ($materia['codigo'] ?? '');
require __DIR__ . '/../partials/header.php';

$porcentajeAcumulado = min(100, max(0, floatval($sumaPorcentajes)));
$porcentajeRestante = max(0, round(100 - floatval($sumaPorcentajes), 2));
?>
<body>
<div class="ce-app-wrapper">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="ce-content">
        <div class="ce-page-header">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="<?= BASE_URL ?>materia/index" class="btn btn-sm btn-outline-secondary ce-action-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        <span>Volver a Materias</span>
                    </a>
                    <span class="ce-code-pill"><?= htmlspecialchars($materia['codigo']) ?></span>
                </div>
                <h1 class="ce-page-title mb-0">Plan de Evaluación: <?= htmlspecialchars($materia['nombre']) ?></h1>
            </div>

            <button type="button" class="btn btn-upt-primary" id="btnNuevaActividad">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Agregar Actividad</span>
            </button>
        </div>

        <!-- Banner Contextual con Barra de Progreso al 100% -->
        <div class="ce-context-banner">
            <div class="row g-3 align-items-center mb-3">
                <div class="col-md-4">
                    <div class="ce-meta-item">
                        <div class="ce-meta-label">Total Ponderado Acumulado</div>
                        <p class="ce-meta-value fs-4 tabular-nums"><?= number_format($sumaPorcentajes, 2) ?>% <span class="fs-6 fw-normal text-white-50">/ 100%</span></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ce-meta-item">
                        <div class="ce-meta-label">Porcentaje Restante Disponible</div>
                        <p class="ce-meta-value fs-4 tabular-nums"><?= number_format($porcentajeRestante, 2) ?>%</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ce-meta-item">
                        <div class="ce-meta-label">Estado para Carga de Notas</div>
                        <p class="ce-meta-value">
                            <?php if ($planCompleto): ?>
                                <span class="ce-badge ce-badge-success">● Plan 100% Completo y Habilitado</span>
                            <?php else: ?>
                                <span class="ce-badge ce-badge-warning">● Incompleto (Requiere sumar 100%)</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="progress" style="height: 10px; border-radius: 999px; background: rgba(255,255,255,0.15);">
                <div class="progress-bar <?= $planCompleto ? 'bg-success' : 'bg-warning' ?>"
                     role="progressbar"
                     style="width: <?= $porcentajeAcumulado ?>%; border-radius: 999px;"></div>
            </div>
        </div>

        <!-- Tabla de Actividades del Plan -->
        <div class="ce-card">
            <div class="ce-card-header">
                <h5 class="ce-card-title">Actividades Evaluativas Configuradas</h5>
                <span class="ce-badge ce-badge-neutral tabular-nums"><?= count($actividades) ?> actividad(es)</span>
            </div>
            <div class="table-responsive">
                <table class="table ce-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre de la Evaluación / Actividad</th>
                            <th style="width: 260px;">Ponderación (%)</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($actividades)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    No hay actividades registradas en el plan de esta asignatura.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($actividades as $actividad): ?>
                                <tr>
                                    <td class="tabular-nums text-muted">#<?= htmlspecialchars($actividad['id']) ?></td>
                                    <td>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($actividad['nombre_actividad']) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="ce-badge ce-badge-info tabular-nums" style="min-width: 68px; justify-content: center;">
                                                <?= number_format($actividad['porcentaje'], 2) ?>%
                                            </span>
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-primary" style="width: <?= min(100, floatval($actividad['porcentaje'])) ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary ce-action-btn js-editar-actividad"
                                                    data-id="<?= htmlspecialchars($actividad['id']) ?>"
                                                    data-nombre="<?= htmlspecialchars($actividad['nombre_actividad']) ?>"
                                                    data-porcentaje="<?= htmlspecialchars($actividad['porcentaje']) ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                <span>Editar</span>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger ce-action-btn js-eliminar-actividad"
                                                    data-id="<?= htmlspecialchars($actividad['id']) ?>"
                                                    data-nombre="<?= htmlspecialchars($actividad['nombre_actividad']) ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                <span>Eliminar</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-light fw-bold">
                                <td colspan="2" class="text-end text-uppercase small text-secondary">Total Acumulado:</td>
                                <td colspan="2" class="tabular-nums fs-6 <?= $planCompleto ? 'text-success' : 'text-warning' ?>">
                                    <?= number_format($sumaPorcentajes, 2) ?>%
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Modal para crear o editar actividad -->
<div class="modal fade" id="modalActividad" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalActividad">Nueva Actividad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formActividad">
                <div class="modal-body">
                    <input type="hidden" name="id" id="actividadId" value="0">
                    <input type="hidden" name="materia_id" value="<?= htmlspecialchars($materia['id']) ?>">

                    <div class="mb-3">
                        <label for="nombre_actividad" class="form-label">Nombre de la actividad evaluativa *</label>
                        <input type="text"
                               class="form-control"
                               id="nombre_actividad"
                               name="nombre_actividad"
                               placeholder="Ej: Parcial 1, Proyecto Final, Laboratorio"
                               required
                               maxlength="100">
                    </div>

                    <div class="mb-2">
                        <label for="porcentaje" class="form-label">Ponderación (%) *</label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control tabular-nums"
                                   id="porcentaje"
                                   name="porcentaje"
                                   min="1"
                                   max="100"
                                   step="0.01"
                                   placeholder="Ej: 25"
                                   required>
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="form-text text-muted">
                            Porcentaje disponible actualmente en el plan: <strong><?= number_format($porcentajeRestante, 2) ?>%</strong>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-upt-primary">Guardar Actividad</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
<script src="<?= BASE_URL ?>assets/js/plan_evaluacion.js"></script>
</body>
</html>