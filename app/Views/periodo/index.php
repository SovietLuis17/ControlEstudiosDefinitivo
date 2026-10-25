<?php
$pageTitle = 'Períodos Académicos | Control de Estudios UPT Valencia';
$activeModule = 'periodo';
$pageHeading = 'Períodos Académicos';
require __DIR__ . '/../partials/header.php';
?>
<body>
<div class="ce-app-wrapper">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="ce-content">
        <div class="ce-page-header">
            <div>
                <h1 class="ce-page-title">Períodos Académicos</h1>
                <p class="ce-page-subtitle">Administra los lapsos lectivos activos y cerrados de la institución.</p>
            </div>
            <button type="button" class="btn btn-upt-primary" id="btnNuevoPeriodo">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Nuevo Período</span>
            </button>
        </div>

        <div class="ce-card">
            <div class="ce-card-header">
                <h5 class="ce-card-title">Listado de Períodos Registrados</h5>
                <span class="ce-badge ce-badge-neutral tabular-nums"><?= count($periodos) ?> período(s)</span>
            </div>
            <div class="table-responsive">
                <table class="table ce-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Período</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($periodos)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    No hay períodos académicos registrados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($periodos as $periodo): ?>
                                <tr>
                                    <td class="tabular-nums text-muted">#<?= htmlspecialchars($periodo['id']) ?></td>
                                    <td>
                                        <span class="fw-bold text-dark fs-6 tabular-nums"><?= htmlspecialchars($periodo['nombre']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($periodo['estado'] === 'activo'): ?>
                                            <span class="ce-badge ce-badge-success">
                                                <span class="ce-badge-dot"></span>
                                                Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="ce-badge ce-badge-neutral">
                                                <span class="ce-badge-dot"></span>
                                                Cerrado
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary ce-action-btn js-editar-periodo"
                                                    data-id="<?= htmlspecialchars($periodo['id']) ?>"
                                                    data-nombre="<?= htmlspecialchars($periodo['nombre']) ?>"
                                                    data-estado="<?= htmlspecialchars($periodo['estado']) ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                <span>Editar</span>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger ce-action-btn js-eliminar-periodo"
                                                    data-id="<?= htmlspecialchars($periodo['id']) ?>"
                                                    data-nombre="<?= htmlspecialchars($periodo['nombre']) ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                <span>Eliminar</span>
                                            </button>
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

<!-- Modal para crear o editar período -->
<div class="modal fade" id="modalPeriodo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalPeriodo">Nuevo Período</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPeriodo">
                <div class="modal-body">
                    <input type="hidden" name="id" id="periodoId" value="0">

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del período *</label>
                        <input type="text"
                               class="form-control"
                               id="nombre"
                               name="nombre"
                               placeholder="Ej: 2026-1"
                               required
                               maxlength="20">
                        <small class="form-text text-muted">Formato sugerido: AAAA-Lapso (ej. 2026-1).</small>
                    </div>

                    <div class="mb-2">
                        <label for="estado" class="form-label">Estado del período *</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="activo">Activo (Habilitado para nuevas secciones)</option>
                            <option value="cerrado">Cerrado (Histórico)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-upt-primary">Guardar Período</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
<script src="<?= BASE_URL ?>assets/js/periodo.js"></script>
</body>
</html>