<?php
$pageTitle = 'Materias | Control de Estudios UPT Valencia';
$activeModule = 'materia';
$pageHeading = 'Catálogo de Materias';
require __DIR__ . '/../partials/header.php';
?>
<body>
<div class="ce-app-wrapper">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="ce-content">
        <div class="ce-page-header">
            <div>
                <h1 class="ce-page-title">Catálogo de Materias</h1>
                <p class="ce-page-subtitle">
                    <?= $rol === 'admin'
                        ? 'Administra las asignaturas del pensum y configura sus planes de evaluación al 100%.'
                        : 'Consulta las asignaturas que dictas y configura sus actividades de evaluación.' ?>
                </p>
            </div>
            <?php if ($rol === 'admin'): ?>
                <button type="button" class="btn btn-upt-primary" id="btnNuevaMateria">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Nueva Materia</span>
                </button>
            <?php endif; ?>
        </div>

        <?php if ($rol === 'profesor'): ?>
            <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center gap-2 mb-4" style="border-radius: 12px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span>Estás visualizando únicamente las materias donde tienes secciones asignadas. Puedes gestionar el <strong>Plan de Evaluación</strong> de cada una.</span>
            </div>
        <?php endif; ?>

        <div class="ce-card">
            <div class="ce-card-header">
                <h5 class="ce-card-title">Asignaturas Registradas</h5>
                <span class="ce-badge ce-badge-neutral tabular-nums"><?= count($materias) ?> materia(s)</span>
            </div>
            <div class="table-responsive">
                <table class="table ce-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Asignatura y Descripción</th>
                            <th class="text-center">Unidades de Crédito</th>
                            <th>Estado del Plan (100%)</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($materias)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <?= $rol === 'admin'
                                        ? 'No hay materias registradas en el catálogo.'
                                        : 'No tienes materias asignadas actualmente. Contacta al administrador.' ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($materias as $materia): ?>
                                <?php $porcentajePlan = min(100, max(0, floatval($materia['suma_porcentajes']))); ?>
                                <tr>
                                    <td class="tabular-nums text-muted">#<?= htmlspecialchars($materia['id']) ?></td>
                                    <td>
                                        <span class="ce-code-pill"><?= htmlspecialchars($materia['codigo']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($materia['nombre']) ?></div>
                                        <?php if (!empty($materia['descripcion'])): ?>
                                            <div class="text-muted small"><?= htmlspecialchars($materia['descripcion']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="ce-badge ce-badge-info tabular-nums"><?= htmlspecialchars($materia['creditos']) ?> UC</span>
                                    </td>
                                    <td style="min-width: 190px;">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <?php if ($materia['plan_completo']): ?>
                                                <span class="ce-badge ce-badge-success">
                                                    <span class="ce-badge-dot"></span>
                                                    Completo (<?= number_format($materia['suma_porcentajes'], 0) ?>%)
                                                </span>
                                            <?php else: ?>
                                                <span class="ce-badge ce-badge-warning">
                                                    <span class="ce-badge-dot"></span>
                                                    Incompleto (<?= number_format($materia['suma_porcentajes'], 0) ?>%)
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 999px;">
                                            <div class="progress-bar <?= $materia['plan_completo'] ? 'bg-success' : 'bg-warning' ?>"
                                                 style="width: <?= $porcentajePlan ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <a href="<?= BASE_URL ?>planEvaluacion/index/<?= $materia['id'] ?>"
                                               class="btn btn-sm btn-upt-amber ce-action-btn">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                                <span>Plan Evaluación</span>
                                            </a>
                                            <?php if ($rol === 'admin'): ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary ce-action-btn js-editar-materia"
                                                        data-id="<?= htmlspecialchars($materia['id']) ?>"
                                                        data-codigo="<?= htmlspecialchars($materia['codigo']) ?>"
                                                        data-nombre="<?= htmlspecialchars($materia['nombre']) ?>"
                                                        data-creditos="<?= htmlspecialchars($materia['creditos']) ?>"
                                                        data-descripcion="<?= htmlspecialchars($materia['descripcion'] ?? '') ?>">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    <span>Editar</span>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger ce-action-btn js-eliminar-materia"
                                                        data-id="<?= htmlspecialchars($materia['id']) ?>"
                                                        data-nombre="<?= htmlspecialchars($materia['nombre']) ?>">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                    <span>Eliminar</span>
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

<?php if ($rol === 'admin'): ?>
<!-- Modal para crear o editar materia (solo admin) -->
<div class="modal fade" id="modalMateria" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalMateria">Nueva Materia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMateria">
                <div class="modal-body">
                    <input type="hidden" name="id" id="materiaId" value="0">

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label for="codigo" class="form-label">Código de la materia *</label>
                            <input type="text"
                                   class="form-control"
                                   id="codigo"
                                   name="codigo"
                                   placeholder="Ej: PROGII-01"
                                   required
                                   maxlength="20">
                        </div>
                        <div class="col-md-5">
                            <label for="creditos" class="form-label">Unidades de Crédito *</label>
                            <input type="number"
                                   class="form-control tabular-nums"
                                   id="creditos"
                                   name="creditos"
                                   min="1"
                                   max="10"
                                   value="3"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre de la asignatura *</label>
                        <input type="text"
                               class="form-control"
                               id="nombre"
                               name="nombre"
                               placeholder="Ej: Programación II"
                               required
                               maxlength="100">
                    </div>

                    <div class="mb-2">
                        <label for="descripcion" class="form-label">Descripción o sinopsis</label>
                        <textarea class="form-control"
                                  id="descripcion"
                                  name="descripcion"
                                  rows="3"
                                  placeholder="Resumen opcional de los contenidos de la asignatura..."
                                  maxlength="255"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-upt-primary">Guardar Materia</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
<script src="<?= BASE_URL ?>assets/js/materia.js"></script>
</body>
</html>