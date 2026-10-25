<?php
$pageTitle = 'Registro de Profesor | Control de Estudios UPT Valencia';
require __DIR__ . '/../partials/header.php';

$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);
?>
<body>

    <div class="ce-auth-split">
        <!-- Panel Izquierdo Institucional -->
        <div class="col-lg-4 ce-auth-hero d-none d-lg-flex">
            <div>
                <div class="ce-auth-logo-box">
                    <img src="<?= BASE_URL ?>assets/img/logo.jpg" alt="Logo UPT Valencia">
                    <div>
                        <div class="fw-bold text-white">UPT Valencia</div>
                        <div class="small" style="color: #fbbf24;">Control de Estudios</div>
                    </div>
                </div>
            </div>

            <div class="my-auto py-4" style="z-index: 1;">
                <span class="ce-badge ce-badge-warning mb-3">
                    <span class="ce-badge-dot"></span>
                    Portal Docente
                </span>
                <h2 class="fw-bold text-white mb-3">Registro de Profesores</h2>
                <p class="text-white-50 small mb-4" style="line-height: 1.65;">
                    Crea tu cuenta docente para consultar tus secciones asignadas, gestionar los planes de evaluación de tus materias y registrar las calificaciones de tus estudiantes.
                </p>

                <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                    <div class="fw-semibold text-white small mb-2">Requisitos de seguridad:</div>
                    <div class="text-white-50" style="font-size: 0.8rem; line-height: 1.55;">
                        • Cédula de identidad numérica (6 a 20 dígitos).<br>
                        • Correo electrónico institucional o personal único.<br>
                        • Contraseña robusta verificada en tiempo real.
                    </div>
                </div>
            </div>

            <div class="small text-white-50" style="z-index: 1;">
                &copy; <?= date('Y') ?> Universidad Politécnica Territorial de Valencia
            </div>
        </div>

        <!-- Panel Derecho: Formulario de Registro -->
        <div class="col-12 col-lg-8 ce-auth-form-panel">
            <div class="ce-auth-card ce-auth-card-wide">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 pb-3 border-bottom">
                    <div>
                        <h3 class="fw-bold text-dark mb-1" style="font-size: 1.45rem;">Crear Cuenta de Profesor</h3>
                        <p class="text-muted small mb-0">Completa tus datos personales y académicos. Los campos con (*) son obligatorios.</p>
                    </div>
                    <a href="<?= BASE_URL ?>auth/login" class="btn btn-sm btn-outline-secondary ce-action-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        <span>Volver al Login</span>
                    </a>
                </div>

                <form action="<?= BASE_URL ?>auth/processRegister" method="POST">
                    <!-- Token CSRF de proteccion (SEC-01) -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Bloque 1: Identificación y Perfil -->
                    <div class="text-uppercase fw-bold text-muted mb-3" style="font-size: 0.72rem; letter-spacing: 0.06em;">
                        1. Identificación y Datos Personales
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="cedula" class="form-label">Cédula de Identidad *</label>
                            <input type="text"
                                   class="form-control tabular-nums"
                                   id="cedula"
                                   name="cedula"
                                   value="<?= htmlspecialchars($old['cedula'] ?? '') ?>"
                                   placeholder="Ej: 12345678"
                                   pattern="[0-9]{6,20}"
                                   inputmode="numeric"
                                   title="Solo números, entre 6 y 20 dígitos"
                                   required
                                   maxlength="20">
                            <small class="form-text text-muted">Solo números (entre 6 y 20 dígitos).</small>
                        </div>

                        <div class="col-md-6">
                            <label for="especialidad" class="form-label">Especialidad Académica</label>
                            <input type="text"
                                   class="form-control"
                                   id="especialidad"
                                   name="especialidad"
                                   value="<?= htmlspecialchars($old['especialidad'] ?? '') ?>"
                                   placeholder="Ej: Programación, Matemáticas"
                                   maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label for="nombres" class="form-label">Nombres *</label>
                            <input type="text"
                                   class="form-control"
                                   id="nombres"
                                   name="nombres"
                                   value="<?= htmlspecialchars($old['nombres'] ?? '') ?>"
                                   placeholder="Tus nombres"
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
                                   value="<?= htmlspecialchars($old['apellidos'] ?? '') ?>"
                                   placeholder="Tus apellidos"
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
                                   value="<?= htmlspecialchars($old['fecha_nacimiento'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label for="genero" class="form-label">Género *</label>
                            <select class="form-select" id="genero" name="genero" required>
                                <option value="">Selecciona...</option>
                                <option value="M" <?= ($old['genero'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                <option value="F" <?= ($old['genero'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                                <option value="Otro" <?= ($old['genero'] ?? '') === 'Otro' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>
                    </div>

                    <!-- Bloque 2: Contacto -->
                    <div class="text-uppercase fw-bold text-muted mb-3" style="font-size: 0.72rem; letter-spacing: 0.06em;">
                        2. Información de Contacto
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text"
                                   class="form-control tabular-nums"
                                   id="telefono"
                                   name="telefono"
                                   value="<?= htmlspecialchars($old['telefono'] ?? '') ?>"
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
                                   value="<?= htmlspecialchars($old['correo'] ?? '') ?>"
                                   placeholder="correo@ejemplo.com"
                                   required
                                   maxlength="150">
                        </div>

                        <div class="col-12">
                            <label for="direccion" class="form-label">Dirección de habitación</label>
                            <input type="text"
                                   class="form-control"
                                   id="direccion"
                                   name="direccion"
                                   value="<?= htmlspecialchars($old['direccion'] ?? '') ?>"
                                   placeholder="Av. / Calle / Municipio"
                                   maxlength="200">
                        </div>
                    </div>

                    <!-- Bloque 3: Seguridad -->
                    <div class="text-uppercase fw-bold text-muted mb-3" style="font-size: 0.72rem; letter-spacing: 0.06em;">
                        3. Seguridad de la Cuenta
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   placeholder="Mínimo 8 caracteres"
                                   required
                                   minlength="8">

                            <ul class="ce-pwd-checklist" id="pwdChecklist">
                                <li id="chkLen">○ Mínimo 8 caracteres</li>
                                <li id="chkUpper">○ Al menos una letra mayúscula (A-Z)</li>
                                <li id="chkLower">○ Al menos una letra minúscula (a-z)</li>
                                <li id="chkNum">○ Al menos un número (0-9)</li>
                                <li id="chkSpec">○ Al menos un símbolo especial (- * + # $ &amp; . ! ? @)</li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirm" class="form-label">Confirmar contraseña *</label>
                            <input type="password"
                                   class="form-control"
                                   id="password_confirm"
                                   name="password_confirm"
                                   placeholder="Repite la contraseña"
                                   required
                                   minlength="8">
                            <small class="form-text text-muted" id="pwdMatchHelp">
                                Ambas contraseñas deben coincidir exactamente.
                            </small>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-upt-primary py-2 fs-6">
                            <span>Completar Registro Docente</span>
                        </button>
                    </div>
                </form>

                <div class="text-center pt-3 border-top">
                    <span class="text-muted small">¿Ya tienes una cuenta registrada?</span>
                    <a href="<?= BASE_URL ?>auth/login" class="fw-semibold text-decoration-none ms-1" style="color: var(--upt-blue-600);">
                        Inicia sesión aquí
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const pwd = document.getElementById('password');
            const confirmPwd = document.getElementById('password_confirm');
            const matchHelp = document.getElementById('pwdMatchHelp');

            function updateItem(id, valid, label) {
                const el = document.getElementById(id);
                if (!el) return;
                el.className = valid ? 'valid' : '';
                el.textContent = (valid ? '✓ ' : '○ ') + label;
            }

            if (pwd) {
                pwd.addEventListener('input', function () {
                    const v = pwd.value;
                    updateItem('chkLen', v.length >= 8, 'Mínimo 8 caracteres');
                    updateItem('chkUpper', /[A-Z]/.test(v), 'Al menos una letra mayúscula (A-Z)');
                    updateItem('chkLower', /[a-z]/.test(v), 'Al menos una letra minúscula (a-z)');
                    updateItem('chkNum', /\d/.test(v), 'Al menos un número (0-9)');
                    updateItem('chkSpec', /[\-\*\+#\$\&\.\,\!\?\@\%\(\)_\=\[\]\{\}\|:;]/.test(v), 'Al menos un símbolo especial (- * + # $ & . ! ? @)');
                });
            }

            if (confirmPwd && pwd && matchHelp) {
                confirmPwd.addEventListener('input', function () {
                    if (confirmPwd.value === '') {
                        matchHelp.textContent = 'Ambas contraseñas deben coincidir exactamente.';
                        matchHelp.className = 'form-text text-muted';
                    } else if (confirmPwd.value === pwd.value) {
                        matchHelp.textContent = '✓ Las contraseñas coinciden.';
                        matchHelp.className = 'form-text text-success fw-semibold';
                    } else {
                        matchHelp.textContent = 'Las contraseñas aún no coinciden.';
                        matchHelp.className = 'form-text text-danger';
                    }
                });
            }
        })();
    </script>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>