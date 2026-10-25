<?php
// app/Views/reporte/index.php
// Vista del Centro de Exportacion y Reportes Oficiales
// Compatible al 100% con el esquema de base de datos en produccion.
// Incluye el generador directo de Boletin Individual por Estudiante en PDF (`reporte/kardexPdf`).
$pageTitle = 'Centro de Reportes | Control de Estudios UPT Valencia';
$activeModule = 'reporte';
$pageHeading = 'Centro de Reportes Oficiales';
require __DIR__ . '/../partials/header.php';
?>
<body>
<div class="ce-app-wrapper">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="ce-content">
        <div class="ce-page-header">
            <div>
                <h1 class="ce-page-title">Centro de Exportación y Reportes Oficiales</h1>
                <p class="ce-page-subtitle">Genera actas de calificaciones por sección, libros en Excel (.xls) y boletines individuales del estudiante en PDF.</p>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Panel 1: Reportes por Sección Académica -->
            <div class="col-xl-7">
                <div class="ce-card h-100">
                    <div class="ce-card-header">
                        <h5 class="ce-card-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Reportes por Sección Académica
                        </h5>
                        <span class="ce-badge ce-badge-info">PDF y Excel (.xls)</span>
                    </div>
                    <div class="ce-card-body">
                        <?php if (empty($seccionesPorPeriodo)): ?>
                            <p class="text-muted mb-0">No tienes secciones disponibles para generar reportes en este momento.</p>
                        <?php else: ?>
                            <form method="GET" class="row g-3 align-items-end">
                                <div class="col-12">
                                    <label for="id" class="form-label">Selecciona una Sección Académica *</label>
                                    <select class="form-select" id="id" name="id" required>
                                        <option value="">Selecciona una sección de la lista...</option>
                                        <?php foreach ($seccionesPorPeriodo as $periodo => $lista): ?>
                                            <optgroup label="Período <?= htmlspecialchars($periodo) ?>">
                                                <?php foreach ($lista as $seccion): ?>
                                                    <option value="<?= htmlspecialchars($seccion['id']) ?>">
                                                        <?= htmlspecialchars(
                                                            $seccion['materia_codigo'] . ' - ' .
                                                            $seccion['materia_nombre'] . ' - Sección ' .
                                                            $seccion['codigo_seccion'] . ' (' .
                                                            intval($seccion['total_estudiantes'] ?? 0) . '/' .
                                                            intval($seccion['cupo_maximo'] ?? 30) . ' cupos)'
                                                        ) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12 d-flex gap-2 flex-wrap pt-1">
                                    <button type="submit"
                                            formaction="<?= BASE_URL ?>reporte/notasPdf"
                                            class="btn btn-danger ce-action-btn py-2 px-3 flex-fill justify-content-center">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <span>Acta de Notas PDF</span>
                                    </button>
                                    <button type="submit"
                                            formaction="<?= BASE_URL ?>reporte/notasExcel"
                                            class="btn btn-success ce-action-btn py-2 px-3 flex-fill justify-content-center">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        <span>Sabana Notas (.xls)</span>
                                    </button>
                                    <button type="submit"
                                            formaction="<?= BASE_URL ?>reporte/inscritosExcel"
                                            class="btn btn-upt-primary ce-action-btn py-2 px-3 flex-fill justify-content-center">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                        <span>Nómina Inscritos (.xls)</span>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Panel 2: Boletín Individual por Estudiante -->
            <div class="col-xl-5">
                <div class="ce-card h-100">
                    <div class="ce-card-header">
                        <h5 class="ce-card-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            Boletín Individual del Estudiante (PDF)
                        </h5>
                        <span class="ce-badge ce-badge-warning">Reporte Individual</span>
                    </div>
                    <div class="ce-card-body">
                        <?php if (empty($estudiantes)): ?>
                            <p class="text-muted mb-0">No hay estudiantes registrados para emitir boletines individuales.</p>
                        <?php else: ?>
                            <form method="GET" action="<?= BASE_URL ?>reporte/kardexPdf" target="_blank" class="row g-3 align-items-end">
                                <div class="col-12">
                                    <label for="estudiante_id_kardex" class="form-label">Selecciona un Estudiante *</label>
                                    <select class="form-select" id="estudiante_id_kardex" name="estudiante_id" required>
                                        <option value="">Selecciona un estudiante del padrón...</option>
                                        <?php foreach ($estudiantes as $estItem): ?>
                                            <?php
                                            $secEtiqueta = !empty($estItem['seccion_id'])
                                                ? ($estItem['materia_codigo'] . ' · Sec ' . $estItem['codigo_seccion'])
                                                : 'Sin sección';
                                            ?>
                                            <option value="<?= htmlspecialchars($estItem['id']) ?>">
                                                <?= htmlspecialchars($estItem['cedula'] . ' - ' . $estItem['nombres'] . ' ' . $estItem['apellidos'] . ' (' . $secEtiqueta . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 pt-1 d-grid">
                                    <button type="submit" class="btn btn-upt-amber ce-action-btn py-2 justify-content-center">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <span>Descargar Boletín Individual PDF</span>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas Informativas de Formatos -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="ce-kpi-card">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="ce-kpi-icon rose">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </span>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Acta de Notas en PDF</h6>
                                <span class="ce-badge ce-badge-danger mt-1">Documento Oficial</span>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">
                            Incluye el membrete y logo oficial de la <strong>UPT Valencia</strong>, desglose de todas las evaluaciones del plan, nota final ponderada, estado de cada estudiante y resumen estadístico al pie.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="ce-kpi-card">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="ce-kpi-icon amber">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            </span>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Boletín Individual en PDF</h6>
                                <span class="ce-badge ce-badge-warning mt-1">Reporte del Estudiante</span>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">
                            Emite la ficha individual del estudiante con su sección asignada, unidades de crédito, calificación definitiva ponderada sobre 20 puntos y el desglose de cada evaluación del plan.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="ce-kpi-card">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="ce-kpi-icon emerald">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                            </span>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Sabana y Nómina en Excel</h6>
                                <span class="ce-badge ce-badge-success mt-1">Hojas .XLS Seguras</span>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">
                            Exporta la matriz completa de calificaciones y el listado de asistencia en formato compatible con Microsoft Excel y LibreOffice Calc con protección contra inyección de fórmulas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>