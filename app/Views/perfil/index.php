<?php
// app/Views/perfil/index.php
// Vista del modulo de autogestion "Mi Perfil y Cambio de Contrasena"
// Permite tanto a Administradores como a Profesores actualizar sus datos personales
// y cambiar su contrasena con verificacion de clave actual y medidor de fortaleza en vivo.
$pageTitle = 'Mi Perfil y Seguridad | Control de Estudios UPT Valencia';
$activeModule = 'perfil';
$pageHeading = 'Mi Perfil y Seguridad';
require __DIR__ . '/../partials/header.php';

$inicialesPerfil = strtoupper(substr($usuario['nombres'] ?? 'U', 0, 1) . substr($usuario['apellidos'] ?? '', 0, 1));
$esAdminPerfil = ($usuario['rol'] ?? 'profesor') === 'admin';
?>
<body>
<div class="ce-app-wrapper">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="ce-content">
        <div class="ce-page-header">
            <div>
                <h1 class="ce-page-title">Mi Perfil y Seguridad de la Cuenta</h1>
                <p class="ce-page-subtitle">Actualiza tu información personal de contacto y gestiona tu contraseña de acceso al sistema.</p>
            </div>
        </div>

        <!-- Banner de Identidad del Usuario Autenticado -->
        <div class="ce-context-banner mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="ce-avatar <?= $esAdminPerfil ? 'ce-avatar-amber' : '' ?>" style="width: 56px; height: 56px; font-size: 1.25rem;">
                        <?= htmlspecialchars($inicialesPerfil) ?>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="text-white fw-bold mb-0">
                                <?= htmlspecialchars(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellidos'] ?? '')) ?>
                            </h4>
                            <span class="ce-badge <?= $esAdminPerfil ? 'ce-badge-warning' : 'ce-badge-info' ?>">
                                <?= $esAdminPerfil ? 'Administrador del Sistema' : 'Profesor Docente' ?>
                            </span>
                        </div>
                        <div class="text-white-50 small mt-1">
                            Cédula: <strong class="text-white tabular-nums"><?= htmlspecialchars($usuario['cedula'] ?? '') ?></strong>
                            <span class="mx-2">·</span>
                            Correo: <strong class="text-white"><?= htmlspecialchars($usuario['correo'] ?? '') ?></strong>
                            <span class="mx-2">·</span>
                            Especialidad: <strong class="text-white"><?= htmlspecialchars(!empty($usuario['especialidad']) ? $usuario['especialidad'] : 'General') ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Columna Izquierda: Formulario de Datos Personales y de Contacto -->
            <div class="col-lg-7">
                <div class="ce-card h-100">
                    <div class="ce-card-header">
                        <h5 class="ce-card-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Información Personal y de Contacto
                        </h5>
                        <span class="ce-badge ce-badge-neutral">Datos de Perfil</span>
                    </div>
                    <div class="ce-card-body">
                        <form id="formPerfilDatos">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Cédula de Identidad (No editable)</label>
                                    <input type="text"
                                           class="form-control bg-light tabular-nums"
                                           value="<?= htmlspecialchars($usuario['cedula'] ?? '') ?>"
                                           disabled
                                           readonly>
                                    <small class="form-text text-muted">Para modificar la cédula o el rol contacta a otro administrador.</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="perfil_especialidad" class="form-label">Especialidad / Área Académica</label>
                                    <input type="text"
                                           class="form-control"
                                           id="perfil_especialidad"
                                           name="especialidad"
                                           value="<?= htmlspecialchars($usuario['especialidad'] ?? '') ?>"
                                           placeholder="Ej: Programación, Redes, Matemáticas"
                                           maxlength="100">
                                </div>

                                <div class="col-md-6">
                                    <label for="perfil_nombres" class="form-label">Nombres *</label>
                                    <input type="text"
                                           class="form-control"
                                           id="perfil_nombres"
                                           name="nombres"
                                           value="<?= htmlspecialchars($usuario['nombres'] ?? '') ?>"
                                           required
                                           maxlength="100">
                                </div>

                                <div class="col-md-6">
                                    <label for="perfil_apellidos" class="form-label">Apellidos *</label>
                                    <input type="text"
                                           class="form-control"
                                           id="perfil_apellidos"
                                           name="apellidos"
                                           value="<?= htmlspecialchars($usuario['apellidos'] ?? '') ?>"
                                           required
                                           maxlength="100">
                                </div>

                                <div class="col-md-6">
                                    <label for="perfil_fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                                    <input type="date"
                                           class="form-control"
                                           id="perfil_fecha_nacimiento"
                                           name="fecha_nacimiento"
                                           value="<?= htmlspecialchars($usuario['fecha_nacimiento'] ?? '') ?>"
                                           required>
                                </div>

                                <div class="col-md-6">
                                    <label for="perfil_genero" class="form-label">Género *</label>
                                    <select class="form-select" id="perfil_genero" name="genero" required>
                                        <option value="M" <?= ($usuario['genero'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                        <option value="F" <?= ($usuario['genero'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                                        <option value="Otro" <?= ($usuario['genero'] ?? '') === 'Otro' ? 'selected' : '' ?>>Otro</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="perfil_telefono" class="form-label">Teléfono de Contacto</label>
                                    <input type="text"
                                           class="form-control tabular-nums"
                                           id="perfil_telefono"
                                           name="telefono"
                                           value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>"
                                           placeholder="Ej: 04141234567"
                                           maxlength="20">
                                </div>

                                <div class="col-md-6">
                                    <label for="perfil_correo" class="form-label">Correo Electrónico (Login) *</label>
                                    <input type="email"
                                           class="form-control"
                                           id="perfil_correo"
                                           name="correo"
                                           value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>"
                                           required
                                           maxlength="150">
                                </div>

                                <div class="col-12">
                                    <label for="perfil_direccion" class="form-label">Dirección de Habitación / Sede</label>
                                    <input type="text"
                                           class="form-control"
                                           id="perfil_direccion"
                                           name="direccion"
                                           value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>"
                                           placeholder="Dirección completa"
                                           maxlength="200">
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-upt-primary px-4">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    <span>Guardar Datos Personales</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Cambio Seguro de Contraseña -->
            <div class="col-lg-5">
                <div class="ce-card h-100">
                    <div class="ce-card-header">
                        <h5 class="ce-card-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Cambiar Contraseña
                        </h5>
                        <span class="ce-badge ce-badge-warning">Seguridad</span>
                    </div>
                    <div class="ce-card-body">
                        <form id="formPerfilPassword">
                            <div class="mb-3">
                                <label for="password_actual" class="form-label">Contraseña Actual *</label>
                                <input type="password"
                                       class="form-control"
                                       id="password_actual"
                                       name="password_actual"
                                       placeholder="Ingresa tu contraseña vigente"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="password_nueva" class="form-label">Nueva Contraseña *</label>
                                <input type="password"
                                       class="form-control"
                                       id="password_nueva"
                                       name="password_nueva"
                                       placeholder="Mínimo 8 caracteres seguros"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña *</label>
                                <input type="password"
                                       class="form-control"
                                       id="password_confirmar"
                                       name="password_confirmar"
                                       placeholder="Repite la nueva contraseña"
                                       required>
                            </div>

                            <!-- Indicadores en vivo de fortaleza de contraseña -->
                            <div class="p-3 rounded-3 bg-light border mb-4">
                                <div class="small fw-bold text-dark mb-2">Requisitos de seguridad de la nueva clave:</div>
                                <div class="row g-1 small" id="requisitosPasswordPerfil">
                                    <div class="col-6 text-muted" data-req="len">○ Mínimo 8 caracteres</div>
                                    <div class="col-6 text-muted" data-req="upper">○ Al menos 1 mayúscula</div>
                                    <div class="col-6 text-muted" data-req="lower">○ Al menos 1 minúscula</div>
                                    <div class="col-6 text-muted" data-req="num">○ Al menos 1 número</div>
                                    <div class="col-12 text-muted" data-req="sym">○ Al menos 1 símbolo especial (*, #, $, @, -, etc.)</div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-upt-amber justify-content-center py-2">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    <span>Actualizar Contraseña</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
<script src="<?= BASE_URL ?>assets/js/perfil.js"></script>
</body>
</html>
