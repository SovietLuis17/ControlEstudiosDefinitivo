<?php
// app/Views/estudiante/index.php
// Vista del Padron General de Estudiantes
// Compatible al 100% con el esquema de base de datos en produccion (`estudiantes.seccion_id`).
// Incluye boton directo para descargar el Boletin Individual de Calificaciones en PDF (`reporte/kardexPdf/{id}`).
$pageTitle = 'Estudiantes | Control de Estudios UPT Valencia';
$activeModule = 'estudiante';
$pageHeading = 'Padrón de Estudiantes';
require __DIR__ . '/../partials/header.php';
?>
<body>
<div class="ce-app-wrapper">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="ce-content">
        <div class="ce-page-header">
            <div>
                <h1 class="ce-page-title">Padrón de Estudiantes</h1>
                <p class="ce-page-subtitle">Consulta, filtra por cédula, descarga el Boletín Individual en PDF y actualiza los datos de los estudiantes.</p>
            </div>
            <button type="button" class="btn btn-upt-primary" id="btnNuevoEstudiante">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Nuevo Estudiante</span>
            </button>
        </div>

        <?php if ($rol === 'profesor'): ?>
            <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center gap-2 mb-4" style="border-radius: 12px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span>Como profesor puedes registrar y actualizar datos de estudiantes y descargar sus boletines académicos. La eliminación de registros está reservada al administrador.</span>
            </div>
        <?php endif; ?>

        <div class="ce-card">
            <!-- Barra de Herramientas y Buscador por Cédula -->
            <div class="ce-card-header">
                <form class="d-flex flex-wrap align-items-center gap-2" method="GET" action="<?= BASE_URL ?>estudiante/index" style="min-width: 320px; max-width: 520px; flex: 1;">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="text"
                               class="form-control tabular-nums"
                               name="busqueda"
                               value="<?= htmlspecialchars($busqueda) ?>"
                               placeholder="Buscar estudiante por cédula..."
                               pattern="[0-9]*"
                               inputmode="numeric"
                               title="Solo números"
                               maxlength="20"
                               autocomplete="off">
                        <button type="submit" class="btn btn-upt-primary px-3">Buscar</button>
                        <?php if ($busqueda !== ''): ?>
                            <a href="<?= BASE_URL ?>estudiante/index" class="btn btn-outline-secondary">Limpiar</a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="d-flex align-items-center gap-2">
                    <?php if ($busqueda !== ''): ?>
                        <span class="ce-badge ce-badge-warning">
                            Filtro Cédula: "<?= htmlspecialchars($busqueda) ?>"
                        </span>
                    <?php endif; ?>
                    <span class="ce-badge ce-badge-neutral tabular-nums">
                        <?= intval($totalEstudiantes) ?> estudiante(s) · Pág. <?= intval($pagina) ?> de <?= intval($totalPaginas) ?>
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table ce-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cédula</th>
                            <th>Estudiante y Contacto</th>
                            <th>Edad / Género</th>
                            <th>Carrera</th>
                            <th>Sección Actual</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($estudiantes)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <?= $busqueda !== ''
                                        ? 'No se encontraron estudiantes para la cédula "' . htmlspecialchars($busqueda) . '".'
                                        : 'No hay estudiantes registrados en el sistema.' ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($estudiantes as $estudiante): ?>
                                <?php
                                $iniEst = strtoupper(substr($estudiante['nombres'] ?? 'E', 0, 1) . substr($estudiante['apellidos'] ?? '', 0, 1));
                                $generoTexto = 'Otro';
                                if ($estudiante['genero'] === 'M') {
                                    $generoTexto = 'Masculino';
                                } elseif ($estudiante['genero'] === 'F') {
                                    $generoTexto = 'Femenino';
                                }
                                ?>
                                <tr>
                                    <td class="tabular-nums text-muted">#<?= htmlspecialchars($estudiante['id']) ?></td>
                                    <td>
                                        <span class="ce-code-pill tabular-nums"><?= htmlspecialchars($estudiante['cedula']) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="ce-avatar ce-avatar-sm">
                                                <?= htmlspecialchars($iniEst) ?>
                                            </span>
                                            <div>
                                                <div class="fw-bold text-dark">
                                                    <?= htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?>
                                                </div>
                                                <div class="text-muted small">
                                                    <?= htmlspecialchars(!empty($estudiante['correo']) ? $estudiante['correo'] : 'Sin correo registrado') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold tabular-nums"><?= htmlspecialchars($estudiante['edad'] ?? '—') ?> años</span>
                                        <span class="text-muted small d-block"><?= htmlspecialchars($generoTexto) ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-medium"><?= htmlspecialchars(!empty($estudiante['carrera']) ? $estudiante['carrera'] : 'Sin asignar') ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($estudiante['seccion_id'])): ?>
                                            <span class="ce-badge ce-badge-info">
                                                <span class="ce-badge-dot"></span>
                                                <?= htmlspecialchars($estudiante['materia_codigo'] ?? '') ?> · Sec <?= htmlspecialchars($estudiante['codigo_seccion'] ?? '') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="ce-badge ce-badge-neutral">Sin sección</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex flex-wrap justify-content-center gap-1">
                                            <a href="<?= BASE_URL ?>reporte/kardexPdf/<?= htmlspecialchars($estudiante['id']) ?>"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-danger ce-action-btn"
                                               title="Descargar Boletín Individual del Estudiante en PDF">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                <span>Boletín PDF</span>
                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary ce-action-btn js-editar-estudiante"
                                                    data-id="<?= htmlspecialchars($estudiante['id']) ?>"
                                                    data-cedula="<?= htmlspecialchars($estudiante['cedula']) ?>"
                                                    data-nombres="<?= htmlspecialchars($estudiante['nombres']) ?>"
                                                    data-apellidos="<?= htmlspecialchars($estudiante['apellidos']) ?>"
                                                    data-fecha="<?= htmlspecialchars($estudiante['fecha_nacimiento']) ?>"
                                                    data-genero="<?= htmlspecialchars($estudiante['genero']) ?>"
                                                    data-direccion="<?= htmlspecialchars($estudiante['direccion'] ?? '') ?>"
                                                    data-telefono="<?= htmlspecialchars($estudiante['telefono'] ?? '') ?>"
                                                    data-correo="<?= htmlspecialchars($estudiante['correo'] ?? '') ?>"
                                                    data-carrera="<?= htmlspecialchars($estudiante['carrera'] ?? '') ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                <span>Editar</span>
                                            </button>

                                            <?php if ($rol === 'admin'): ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger ce-action-btn js-eliminar-estudiante"
                                                        data-id="<?= htmlspecialchars($estudiante['id']) ?>"
                                                        data-nombre="<?= htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) ?>">
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

            <?php if ($totalPaginas > 1): ?>
                <div class="p-3 border-top bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">Página <?= intval($pagina) ?> de <?= intval($totalPaginas) ?></span>
                    <nav aria-label="Paginación de estudiantes">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>estudiante/index?pagina=<?= $pagina - 1 ?>&busqueda=<?= urlencode($busqueda) ?>">Anterior</a>
                            </li>
                            <?php if ($totalPaginas <= 7): ?>
                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                    <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= BASE_URL ?>estudiante/index?pagina=<?= $i ?>&busqueda=<?= urlencode($busqueda) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            <?php else: ?>
                                <li class="page-item active">
                                    <span class="page-link">Pág. <?= $pagina ?> / <?= $totalPaginas ?></span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>estudiante/index?pagina=<?= $pagina + 1 ?>&busqueda=<?= urlencode($busqueda) ?>">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal para crear o editar estudiante -->
<div class="modal fade" id="modalEstudiante" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalEstudiante">Nuevo Estudiante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEstudiante">
                <div class="modal-body">
                    <input type="hidden" name="id" id="estudianteId" value="0">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="cedula" class="form-label">Cédula *</label>
                            <input type="text"
                                   class="form-control tabular-nums"
                                   id="cedula"
                                   name="cedula"
                                   placeholder="Ej: 20123456"
                                   pattern="[0-9]{6,20}"
                                   inputmode="numeric"
                                   title="Solo números, entre 6 y 20 dígitos"
                                   required
                                   maxlength="20">
                            <small class="form-text text-muted">Solo números, entre 6 y 20 dígitos.</small>
                        </div>

                        <div class="col-md-6">
                            <label for="carrera" class="form-label">Carrera / PNF</label>
                            <input type="text"
                                   class="form-control"
                                   id="carrera"
                                   name="carrera"
                                   placeholder="Ej: Informática"
                                   pattern="[A-Za-z0-9ÁÉÍÓÚáéíóúÑñÜü\s\.\-]+"
                                   title="Solo letras, números, espacios, puntos y guiones"
                                   maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label for="nombres" class="form-label">Nombres *</label>
                            <input type="text"
                                   class="form-control"
                                   id="nombres"
                                   name="nombres"
                                   placeholder="Nombres del estudiante"
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
                                   placeholder="Apellidos del estudiante"
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
                            <label for="correo" class="form-label">Correo electrónico</label>
                            <input type="email"
                                   class="form-control"
                                   id="correo"
                                   name="correo"
                                   placeholder="correo@ejemplo.com"
                                   maxlength="150">
                        </div>

                        <div class="col-12">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text"
                                   class="form-control"
                                   id="direccion"
                                   name="direccion"
                                   placeholder="Dirección del estudiante"
                                   maxlength="200">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-upt-primary">Guardar Estudiante</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
<script src="<?= BASE_URL ?>assets/js/estudiante.js"></script>
</body>
</html>