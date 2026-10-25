<?php
$pageTitle = 'Profesores | Control de Estudios UPT Valencia';
$activeModule = 'profesor';
$pageHeading = 'Planta Docente y Administradores';
require __DIR__ . '/../partials/header.php';
?>
<body>
<div class="ce-app-wrapper">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="ce-content">
        <div class="ce-page-header">
            <div>
                <h1 class="ce-page-title">Gestión de Profesores</h1>
                <p class="ce-page-subtitle">Administra el personal docente y las cuentas con privilegios de administración del sistema.</p>
            </div>
            <button type="button" class="btn btn-upt-primary" id="btnNuevoProfesor">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Nuevo Profesor</span>
            </button>
        </div>

        <div class="ce-card">
            <div class="ce-card-header">
                <h5 class="ce-card-title">Directorio de Profesores Registrados</h5>
                <span class="ce-badge ce-badge-neutral tabular-nums"><?= count($profesores) ?> registro(s)</span>
            </div>
            <div class="table-responsive">
                <table class="table ce-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cédula</th>
                            <th>Docente y Correo</th>
                            <th>Especialidad</th>
                            <th>Teléfono</th>
                            <th>Rol de Acceso</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($profesores)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    No hay profesores registrados en el sistema.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($profesores as $profesor): ?>
                                <?php
                                $iniProf = strtoupper(substr($profesor['nombres'] ?? 'P', 0, 1) . substr($profesor['apellidos'] ?? '', 0, 1));
                                ?>
                                <tr>
                                    <td class="tabular-nums text-muted">#<?= htmlspecialchars($profesor['id']) ?></td>
                                    <td>
                                        <span class="ce-code-pill tabular-nums"><?= htmlspecialchars($profesor['cedula']) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="ce-avatar ce-avatar-sm <?= $profesor['rol'] === 'admin' ? 'ce-avatar-amber' : '' ?>">
                                                <?= htmlspecialchars($iniProf) ?>
                                            </span>
                                            <div>
                                                <div class="fw-bold text-dark">
                                                    <?= htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']) ?>
                                                </div>
                                                <div class="text-muted small"><?= htmlspecialchars($profesor['correo']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ce-badge ce-badge-neutral">
                                            <?= htmlspecialchars(!empty($profesor['especialidad']) ? $profesor['especialidad'] : 'Sin asignar') ?>
                                        </span>
                                    </td>
                                    <td class="tabular-nums small text-muted">
                                        <?= htmlspecialchars(!empty($profesor['telefono']) ? $profesor['telefono'] : '—') ?>
                                    </td>
                                    <td>
                                        <?php if ($profesor['rol'] === 'admin'): ?>
                                            <span class="ce-badge ce-badge-warning">
                                                <span class="ce-badge-dot"></span>
                                                Administrador
                                            </span>
                                        <?php else: ?>
                                            <span class="ce-badge ce-badge-info">
                                                <span class="ce-badge-dot"></span>
                                                Profesor
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary ce-action-btn js-editar-profesor"
                                                    data-id="<?= htmlspecialchars($profesor['id']) ?>"
                                                    data-cedula="<?= htmlspecialchars($profesor['cedula']) ?>"
                                                    data-nombres="<?= htmlspecialchars($profesor['nombres']) ?>"
                                                    data-apellidos="<?= htmlspecialchars($profesor['apellidos']) ?>"
                                                    data-fecha="<?= htmlspecialchars($profesor['fecha_nacimiento']) ?>"
                                                    data-genero="<?= htmlspecialchars($profesor['genero']) ?>"
                                                    data-direccion="<?= htmlspecialchars($profesor['direccion'] ?? '') ?>"
                                                    data-telefono="<?= htmlspecialchars($profesor['telefono'] ?? '') ?>"
                                                    data-correo="<?= htmlspecialchars($profesor['correo']) ?>"
                                                    data-especialidad="<?= htmlspecialchars($profesor['especialidad'] ?? '') ?>"
                                                    data-rol="<?= htmlspecialchars($profesor['rol']) ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                <span>Editar</span>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger ce-action-btn js-eliminar-profesor"
                                                    data-id="<?= htmlspecialchars($profesor['id']) ?>"
                                                    data-nombre="<?= htmlspecialchars($profesor['nombres'] . ' ' . $profesor['apellidos']) ?>">
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

<!-- Modal para crear o editar profesor -->
<div class="modal fade" id="modalProfesor" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalProfesor">Nuevo Profesor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formProfesor">
                <div class="modal-body">
                    <input type="hidden" name="id" id="profesorId" value="0">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="cedula" class="form-label">Cédula *</label>
                            <input type="text"
                                   class="form-control tabular-nums"
                                   id="cedula"
                                   name="cedula"
                                   placeholder="Ej: 12345678"
                                   pattern="[0-9]{6,20}"
                                   inputmode="numeric"
                                   title="Solo números, entre 6 y 20 dígitos"
                                   required
                                   maxlength="20">
                            <small class="form-text text-muted">Solo números, entre 6 y 20 dígitos.</small>
                        </div>

                        <div class="col-md-6">
                            <label for="rol" class="form-label">Rol en el sistema *</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="profesor">Profesor (Docente)</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="nombres" class="form-label">Nombres *</label>
                            <input type="text"
                                   class="form-control"
                                   id="nombres"
                                   name="nombres"
                                   placeholder="Nombres del profesor"
                                   pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+"
                                   title="Solo letras y espacios, sin números"
                                   required
                                   maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">Apellidos *</label>
                            <input type="text"
                                   class="form-control"
                                   id="apellidos"
                                   name="apellidos"
                                   placeholder="Apellidos del profesor"
                                   pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+"
                                   title="Solo letras y espacios, sin números"
                                   required
                                   maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento *</label>
                            <input type="date"
                                   class="form-control"
                                   id="fecha_nacimiento"
                                   name="fecha_nacimiento"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label for="genero" class="form-label">Género *</label>
                            <select class="form-select" id="genero" name="genero" required>
                                <option value="">Selecciona...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text"
                                   class="form-control tabular-nums"
                                   id="telefono"
                                   name="telefono"
                                   placeholder="Ej: 04141234567"
                                   pattern="[0-9+\-\s]{7,20}"
                                   inputmode="tel"
                                   title="Solo números, guiones y el signo +"
                                   maxlength="20">
                        </div>

                        <div class="col-md-6">
                            <label for="correo" class="form-label">Correo electrónico *</label>
                            <input type="email"
                                   class="form-control"
                                   id="correo"
                                   name="correo"
                                   placeholder="correo@ejemplo.com"
                                   required
                                   maxlength="150">
                        </div>

                        <div class="col-12">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text"
                                   class="form-control"
                                   id="direccion"
                                   name="direccion"
                                   placeholder="Dirección del profesor"
                                   maxlength="200">
                        </div>

                        <div class="col-md-6">
                            <label for="especialidad" class="form-label">Especialidad</label>
                            <input type="text"
                                   class="form-control"
                                   id="especialidad"
                                   name="especialidad"
                                   placeholder="Ej: Programación"
                                   pattern="[A-Za-z0-9ÁÉÍÓÚáéíóúÑñÜü\s\.\-]+"
                                   title="Solo letras, números, espacios, puntos y guiones"
                                   maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label" id="labelPassword">Contraseña *</label>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   placeholder="Mínimo 8 caracteres"
                                   minlength="8">
                            <small class="form-text text-muted">
                                Mínimo 8 caracteres (mayúscula, minúscula, número y símbolo). En edición, déjala vacía para conservar la actual.
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-upt-primary">Guardar Profesor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
<script src="<?= BASE_URL ?>assets/js/profesor.js"></script>
</body>
</html>