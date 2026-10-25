<?php
// app/Views/seccion/index.php
// Vista principal de Secciones
// Compatible al 100% con el esquema de base de datos en produccion.
// Muestra el indicador visual de ocupacion (`total_estudiantes` / `cupo_maximo`) y el estado del periodo.
$pageTitle = 'Secciones | Control de Estudios UPT Valencia';
$activeModule = 'seccion';
$pageHeading = 'Gestión de Secciones';
require __DIR__ . '/../partials/header.php';
?>
<body>
<div class="ce-app-wrapper">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="ce-content">
        <div class="ce-page-header">
            <div>
                <h1 class="ce-page-title"><?= $rol === 'admin' ? 'Gestión de Secciones' : 'Mis Secciones Asignadas' ?></h1>
                <p class="ce-page-subtitle">
                    <?= $rol === 'admin'
                        ? 'Administra la oferta académica de secciones por asignatura, docente, período, aula, horario y control de cupo.'
                        : 'Accede a la nómina de estudiantes inscritos y a la carga de calificaciones de tus secciones.' ?>
                </p>
            </div>
            <?php if ($rol === 'admin'): ?>
                <button type="button" class="btn btn-upt-primary" id="btnNuevaSeccion">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Nueva Sección</span>
                </button>
            <?php endif; ?>
        </div>

        <div class="ce-card">
            <div class="ce-card-header">
                <h5 class="ce-card-title">Listado de Secciones</h5>
                <span class="ce-badge ce-badge-neutral tabular-nums"><?= count($secciones) ?> sección(es)</span>
            </div>
            <div class="table-responsive">
                <table class="table ce-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asignatura</th>
                            <th>Sección</th>
                            <?php if ($rol === 'admin'): ?>
                                <th>Profesor</th>
                            <?php endif; ?>
                            <th>Período</th>
                            <th>Matrícula / Cupo</th>
                            <th>Aula y Horario</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($secciones)): ?>
                            <tr>
                                <td colspan="<?= $rol === 'admin' ? 8 : 7 ?>" class="text-center text-muted py-5">
                                    No hay secciones registradas actualmente.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($secciones as $seccion): ?>
                                <?php
                                $inscritosSec = intval($seccion['total_estudiantes'] ?? 0);
                                $cupoMaxSec = max(1, intval($seccion['cupo_maximo'] ?? 30));
                                $pctCupo = min(100, round(($inscritosSec / $cupoMaxSec) * 100));
                                $periodoCerrado = ($seccion['periodo_estado'] ?? 'activo') === 'cerrado';
                                ?>
                                <tr>
                                    <td class="tabular-nums text-muted">#<?= htmlspecialchars($seccion['id']) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="ce-code-pill"><?= htmlspecialchars($seccion['materia_codigo'] ?? '') ?></span>
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($seccion['materia_nombre'] ?? '') ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ce-badge ce-badge-info">Sec <?= htmlspecialchars($seccion['codigo_seccion'] ?? '') ?></span>
                                    </td>
                                    <?php if ($rol === 'admin'): ?>
                                        <td>
                                            <span class="fw-medium"><?= htmlspecialchars($seccion['profesor_nombre'] ?? '') ?></span>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <?php if ($periodoCerrado): ?>
                                            <span class="ce-badge ce-badge-neutral" title="Período cerrado (Solo lectura)">
                                                <span class="ce-badge-dot"></span>
                                                <?= htmlspecialchars($seccion['periodo_nombre'] ?? '') ?> · Cerrado
                                            </span>
                                        <?php else: ?>
                                            <span class="ce-badge ce-badge-success">
                                                <span class="ce-badge-dot"></span>
                                                <?= htmlspecialchars($seccion['periodo_nombre'] ?? '') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="min-width: 140px;">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="fw-bold tabular-nums <?= $inscritosSec >= $cupoMaxSec ? 'text-danger' : 'text-dark' ?>">
                                                <?= $inscritosSec ?> / <?= $cupoMaxSec ?>
                                            </span>
                                            <span class="text-muted tabular-nums"><?= $pctCupo ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 999px;">
                                            <div class="progress-bar <?= $inscritosSec >= $cupoMaxSec ? 'bg-danger' : ($pctCupo >= 80 ? 'bg-warning' : 'bg-primary') ?>"
                                                 style="width: <?= $pctCupo ?>%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small"><?= htmlspecialchars(!empty($seccion['aula']) ? $seccion['aula'] : 'Aula sin asignar') ?></div>
                                        <div class="text-muted" style="font-size: 0.78rem;"><?= htmlspecialchars(!empty($seccion['horario']) ? $seccion['horario'] : 'Horario sin asignar') ?></div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex flex-wrap justify-content-center gap-1">
                                            <a href="<?= BASE_URL ?>seccion/estudiantes/<?= $seccion['id'] ?>"
                                               class="btn btn-sm btn-outline-secondary ce-action-btn">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                                <span>Estudiantes</span>
                                            </a>
                                            <a href="<?= BASE_URL ?>nota/index/<?= $seccion['id'] ?>"
                                               class="btn btn-sm btn-upt-amber ce-action-btn">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                                <span>Notas</span>
                                            </a>
                                            <?php if ($rol === 'admin'): ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary ce-action-btn js-editar-seccion"
                                                        data-id="<?= htmlspecialchars($seccion['id']) ?>"
                                                        data-materia="<?= htmlspecialchars($seccion['materia_id'] ?? '') ?>"
                                                        data-profesor="<?= htmlspecialchars($seccion['profesor_id'] ?? '') ?>"
                                                        data-periodo="<?= htmlspecialchars($seccion['periodo_id'] ?? '') ?>"
                                                        data-codigo="<?= htmlspecialchars($seccion['codigo_seccion'] ?? '') ?>"
                                                        data-aula="<?= htmlspecialchars($seccion['aula'] ?? '') ?>"
                                                        data-horario="<?= htmlspecialchars($seccion['horario'] ?? '') ?>">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    <span>Editar</span>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger ce-action-btn js-eliminar-seccion"
                                                        data-id="<?= htmlspecialchars($seccion['id']) ?>"
                                                        data-codigo="<?= htmlspecialchars($seccion['materia_codigo'] ?? '') ?> - <?= htmlspecialchars($seccion['codigo_seccion'] ?? '') ?>">
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
<!-- Modal para crear o editar sección (solo admin) -->
<div class="modal fade" id="modalSeccion" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalSeccion">Nueva Sección</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSeccion">
                <div class="modal-body">
                    <input type="hidden" name="id" id="seccionId" value="0">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="materia_id" class="form-label">Materia *</label>
                            <select class="form-select" id="materia_id" name="materia_id" required>
                                <option value="">Selecciona una materia...</option>
                                <?php foreach ($materias as $materia): ?>
                                    <option value="<?= htmlspecialchars($materia['id']) ?>">
                                        <?= htmlspecialchars($materia['codigo']) ?> - <?= htmlspecialchars($materia['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="profesor_id" class="form-label">Profesor asignado *</label>
                            <select class="form-select" id="profesor_id" name="profesor_id" required>
                                <option value="">Selecciona un profesor...</option>
                                <?php foreach ($profesores as $profesor): ?>
                                    <option value="<?= htmlspecialchars($profesor['id']) ?>">
                                        <?= htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="periodo_id" class="form-label">Período académico *</label>
                            <select class="form-select" id="periodo_id" name="periodo_id" required>
                                <option value="">Selecciona un período...</option>
                                <?php foreach ($periodos as $periodo): ?>
                                    <option value="<?= htmlspecialchars($periodo['id']) ?>">
                                        <?= htmlspecialchars($periodo['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="codigo_seccion" class="form-label">Código de sección *</label>
                            <input type="text"
                                   class="form-control"
                                   id="codigo_seccion"
                                   name="codigo_seccion"
                                   placeholder="Ej: A, B, 01"
                                   required
                                   maxlength="10">
                        </div>

                        <div class="col-md-6">
                            <label for="aula" class="form-label">Aula o Laboratorio</label>
                            <input type="text"
                                   class="form-control"
                                   id="aula"
                                   name="aula"
                                   placeholder="Ej: Aula 301"
                                   maxlength="30">
                        </div>

                        <div class="col-md-6">
                            <label for="horario" class="form-label">Horario de clases</label>
                            <input type="text"
                                   class="form-control"
                                   id="horario"
                                   name="horario"
                                   placeholder="Ej: Lunes-Miércoles 8:00-10:00"
                                   maxlength="100">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-upt-primary">Guardar Sección</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
<script src="<?= BASE_URL ?>assets/js/seccion.js"></script>
</body>
</html>