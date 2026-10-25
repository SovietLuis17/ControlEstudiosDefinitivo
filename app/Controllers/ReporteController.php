<?php
// app/Controllers/ReporteController.php
// Controlador del modulo de reportes
// Genera descargas en PDF (con FPDF) y en Excel nativo (.xls sin librerias)
// Incluye prevencion contra inyeccion de formulas en celdas Excel (SEC-07)
// y el Reporte / Boletin Individual del Estudiante en PDF (`kardexPdf` / `boletinPdf`)
// operando al 100% sobre el esquema actual en produccion (`estudiantes.seccion_id` y `notas`).

namespace App\Controllers;

use App\Config\Database;
use App\Config\Institucion;
use App\Models\Seccion;
use App\Models\PlanEvaluacion;
use App\Models\Nota;
use App\Models\Estudiante;

require_once __DIR__ . '/../Libraries/fpdf/fpdf.php';

class ReporteController
{
    private Seccion $modelSeccion;
    private PlanEvaluacion $modelPlan;
    private Nota $modelNota;
    private Estudiante $modelEstudiante;

    public function __construct()
    {
        $database = new Database();
        $this->modelSeccion = new Seccion($database->getConnection());
        $this->modelPlan = new PlanEvaluacion($database->getConnection());
        $this->modelNota = new Nota($database->getConnection());
        $this->modelEstudiante = new Estudiante($database->getConnection());
    }

    // Muestra la vista principal del modulo de reportes
    public function index(): void
    {
        $rol = $_SESSION['rol'] ?? '';
        $usuarioId = intval($_SESSION['usuario_id'] ?? 0);

        if ($rol === 'admin') {
            $secciones = $this->modelSeccion->obtenerTodos();
        } else {
            $secciones = $this->modelSeccion->obtenerPorProfesor($usuarioId);
        }

        $seccionesPorPeriodo = [];
        foreach ($secciones as $seccion) {
            $seccionesPorPeriodo[$seccion['periodo_nombre']][] = $seccion;
        }

        // Lista de estudiantes para descarga directa de Boletin Individual desde el Centro de Reportes
        $estudiantes = $this->modelEstudiante->obtenerTodos();

        require_once __DIR__ . '/../Views/reporte/index.php';
    }

    // Descarga el reporte de notas de una seccion en PDF
    public function notasPdf(): void
    {
        $seccion = $this->resolverSeccion(intval($_GET['id'] ?? 0));
        $materiaId = intval($seccion['materia_id']);

        $actividades = $this->modelPlan->obtenerPorMateria($materiaId);
        $reporte = $this->modelNota->obtenerReporteNotas($seccion['id'], $materiaId);

        $nActividades = count($actividades);

        $anchoNum = 7;
        $anchoCedula = 22;
        $anchoNombres = 36;
        $anchoApellidos = 36;
        $anchoFinal = 14;
        $anchoEstado = 20;
        $anchoFijos = $anchoNum + $anchoCedula + $anchoNombres + $anchoApellidos + $anchoFinal + $anchoEstado;

        $anchoActividad = 24;
        $anchoPagina = $anchoFijos + ($nActividades * $anchoActividad) + 16;

        if ($anchoPagina < 279) {
            $anchoPagina = 279;
            if ($nActividades > 0) {
                $anchoActividad = (279 - 16 - $anchoFijos) / $nActividades;
            }
        }

        $pdf = new \FPDF('L', 'mm', [$anchoPagina, 216]);
        $pdf->SetMargins(8, 8, 8);
        $pdf->AddPage();
        $pdf->SetTitle('Reporte de notas');

        if (is_file(Institucion::LOGO)) {
            $pdf->Image(Institucion::LOGO, 12, 8, 24);
        }

        $pdf->SetFont('Arial', 'B', 15);
        $pdf->Cell(0, 10, $this->t(Institucion::NOMBRE), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 7, $this->t('REPORTE DE NOTAS'), 0, 1, 'C');
        $pdf->Ln(4);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 6, $this->t('Materia: ' . $seccion['materia_codigo'] . ' - ' . $seccion['materia_nombre']), 0, 0);
        $pdf->Cell(60, 6, $this->t('Seccion: ' . $seccion['codigo_seccion'] . ' (Cupo: ' . ($seccion['cupo_maximo'] ?? Institucion::CUPO_MAXIMO_SECCION) . ')'), 0, 0);
        $pdf->Cell(60, 6, $this->t('Periodo: ' . $seccion['periodo_nombre'] . ' (' . strtoupper($seccion['periodo_estado'] ?? 'activo') . ')'), 0, 1);
        $pdf->Cell(90, 6, $this->t('Profesor: ' . $seccion['profesor_nombre']), 0, 0);
        $pdf->Cell(60, 6, $this->t('Aula: ' . ($seccion['aula'] ?? 'Sin asignar')), 0, 0);
        $pdf->Cell(60, 6, $this->t('Generado: ' . date('d/m/Y H:i')), 0, 1);
        $pdf->Ln(3);

        $altoCabecera = 12;
        $altoLinea = 6;

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(33, 37, 41);
        $pdf->SetTextColor(255);
        $pdf->Cell($anchoNum, $altoCabecera, '#', 1, 0, 'C', true);
        $pdf->Cell($anchoCedula, $altoCabecera, 'CEDULA', 1, 0, 'C', true);
        $pdf->Cell($anchoNombres, $altoCabecera, 'NOMBRES', 1, 0, 'C', true);
        $pdf->Cell($anchoApellidos, $altoCabecera, 'APELLIDOS', 1, 0, 'C', true);

        foreach ($actividades as $actividad) {
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $lineas = $this->dividirEnDosLineas($pdf, $actividad['nombre_actividad'], $anchoActividad);

            $pdf->SetXY($x, $y);
            $pdf->Cell($anchoActividad, $altoLinea, $lineas[0], 1, 0, 'C', true);

            $pdf->SetXY($x, $y + $altoLinea);
            $pdf->Cell($anchoActividad, $altoLinea, $lineas[1], 1, 0, 'C', true);

            $pdf->SetXY($x + $anchoActividad, $y);
        }

        $pdf->Cell($anchoFinal, $altoCabecera, 'FINAL', 1, 0, 'C', true);
        $pdf->Cell($anchoEstado, $altoCabecera, 'ESTADO', 1, 1, 'C', true);

        $pdf->SetTextColor(0);
        $pdf->SetFont('Arial', '', 8);
        $i = 1;
        foreach ($reporte as $fila) {
            $pdf->Cell($anchoNum, 8, $i, 1, 0, 'C');
            $pdf->Cell($anchoCedula, 8, $this->t($fila['cedula']), 1, 0, 'C');
            $pdf->Cell($anchoNombres, 8, $this->t($fila['nombres']), 1, 0);
            $pdf->Cell($anchoApellidos, 8, $this->t($fila['apellidos']), 1, 0);
            foreach ($actividades as $actividad) {
                $valor = $fila['notas'][$actividad['id']] ?? null;
                $texto = $valor === null ? '-' : number_format(floatval($valor), 2);
                $pdf->Cell($anchoActividad, 8, $texto, 1, 0, 'C');
            }
            $textoFinal = $fila['nota_final'] === null ? '-' : number_format($fila['nota_final'], 2);
            $pdf->Cell($anchoFinal, 8, $textoFinal, 1, 0, 'C');
            $pdf->Cell($anchoEstado, 8, $this->t($this->textoEstado($fila['estado'])), 1, 1, 'C');
            $i++;
        }

        $conteo = $this->contarEstados($reporte);
        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 6, $this->t(
            'Total estudiantes: ' . count($reporte) .
            '  |  Aprobados: ' . $conteo['aprobado'] .
            '  |  Reprobados: ' . $conteo['reprobado'] .
            '  |  En curso: ' . $conteo['en_curso'] .
            '  |  Sin notas: ' . $conteo['sin_notas']
        ), 0, 1);

        $nombreArchivo = 'notas_' . $this->sanearNombre(
            $seccion['materia_codigo'] . '_Sec' . $seccion['codigo_seccion'] . '_' . $seccion['periodo_nombre']
        ) . '.pdf';
        $pdf->Output('D', $nombreArchivo);
        exit;
    }

    // Genera y descarga el Boletin / Reporte Individual de Calificaciones de un estudiante en PDF
    // Compatible al 100% con el esquema actual en produccion (`estudiantes.seccion_id` y `notas`).
    public function kardexPdf(): void
    {
        $estudianteId = intval($_GET['id'] ?? ($_GET['estudiante_id'] ?? 0));

        if ($estudianteId <= 0) {
            $_SESSION['swal'] = [
                'icon' => 'error',
                'title' => 'Estudiante invalido',
                'text' => 'Debe seleccionar un estudiante valido para generar su Boletin Individual en PDF.'
            ];
            header('Location: ' . BASE_URL . 'estudiante/index');
            exit;
        }

        $estudiante = $this->modelEstudiante->obtenerPorId($estudianteId);
        if (!$estudiante) {
            $_SESSION['swal'] = [
                'icon' => 'error',
                'title' => 'No encontrado',
                'text' => 'El estudiante solicitado no existe en el sistema.'
            ];
            header('Location: ' . BASE_URL . 'estudiante/index');
            exit;
        }

        $boletin = $this->modelNota->obtenerBoletinEstudiante($estudianteId);

        $pdf = new \FPDF('P', 'mm', 'Letter');
        $pdf->SetMargins(12, 12, 12);
        $pdf->AddPage();
        $pdf->SetTitle('Boletin Individual de Calificaciones - ' . $estudiante['cedula']);

        if (is_file(Institucion::LOGO)) {
            $pdf->Image(Institucion::LOGO, 14, 10, 22);
        }

        // Encabezado Institucional
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, $this->t(Institucion::NOMBRE), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 6, $this->t('BOLETIN INDIVIDUAL DE CALIFICACIONES DEL ESTUDIANTE'), 0, 1, 'C');
        $pdf->SetFont('Arial', 'I', 8.5);
        $pdf->Cell(0, 5, $this->t(Institucion::LEMA), 0, 1, 'C');
        $pdf->Ln(4);

        // Bloque de Datos Personales y Situacion Academica
        $pdf->SetFillColor(241, 245, 249);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 7, $this->t('  1. DATOS DEL ESTUDIANTE Y RESUMEN ACADEMICO'), 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 9);
        $nombreCompleto = $estudiante['nombres'] . ' ' . $estudiante['apellidos'];
        $carrera = !empty($estudiante['carrera']) ? $estudiante['carrera'] : 'Sin asignar';
        $indiceTexto = $boletin['indice_academico'] !== null
            ? number_format(floatval($boletin['indice_academico']), 2) . ' / 20 pts'
            : 'En curso (Sin nota definitiva)';

        $pdf->Cell(96, 6.5, $this->t('Estudiante: ' . $nombreCompleto), 1, 0);
        $pdf->Cell(46, 6.5, $this->t('Cedula: ' . $estudiante['cedula']), 1, 0);
        $pdf->Cell(50, 6.5, $this->t('Carrera: ' . $carrera), 1, 1);

        $pdf->Cell(64, 6.5, $this->t('Edad / Genero: ' . ($estudiante['edad'] ?? '-') . ' anos (' . $this->textoGenero($estudiante['genero']) . ')'), 1, 0);
        $pdf->Cell(64, 6.5, $this->t('U.C. Aprobadas / Inscritas: ' . $boletin['uc_aprobadas'] . ' / ' . $boletin['uc_inscritas']), 1, 0);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(64, 6.5, $this->t('Definitiva: ' . $indiceTexto), 1, 1);
        $pdf->Ln(4);

        // Tabla de Seccion y Asignatura Inscrita
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(15, 23, 42);
        $pdf->SetTextColor(255);
        $pdf->Cell(22, 7, 'PERIODO', 1, 0, 'C', true);
        $pdf->Cell(24, 7, 'CODIGO', 1, 0, 'C', true);
        $pdf->Cell(64, 7, 'UNIDAD CURRICULAR', 1, 0, 'L', true);
        $pdf->Cell(14, 7, 'SECC.', 1, 0, 'C', true);
        $pdf->Cell(14, 7, 'U.C.', 1, 0, 'C', true);
        $pdf->Cell(24, 7, 'DEFINITIVA', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'ESTADO', 1, 1, 'C', true);

        $pdf->SetTextColor(0);
        $pdf->SetFont('Arial', '', 8.5);

        if (empty($boletin['materias'])) {
            $pdf->Cell(192, 8, $this->t('El estudiante no posee seccion asignada actualmente.'), 1, 1, 'C');
        } else {
            foreach ($boletin['materias'] as $mat) {
                $notaTexto = $mat['nota_final'] === null ? '---' : number_format(floatval($mat['nota_final']), 2);
                $estadoTexto = $this->textoEstado($mat['estado']);

                $pdf->Cell(22, 7, $this->t($mat['periodo_nombre']), 1, 0, 'C');
                $pdf->Cell(24, 7, $this->t($mat['materia_codigo']), 1, 0, 'C');
                $pdf->Cell(64, 7, $this->t($this->recortarAlAncho($pdf, $mat['materia_nombre'], 61)), 1, 0, 'L');
                $pdf->Cell(14, 7, $this->t($mat['codigo_seccion']), 1, 0, 'C');
                $pdf->Cell(14, 7, (string)$mat['materia_creditos'], 1, 0, 'C');
                $pdf->SetFont('Arial', 'B', 8.5);
                $pdf->Cell(24, 7, $notaTexto, 1, 0, 'C');
                $pdf->SetFont('Arial', '', 8.5);
                $pdf->Cell(30, 7, $this->t($estadoTexto), 1, 1, 'C');
            }
        }

        // Desglose detallado de evaluaciones de la materia
        if (!empty($boletin['materias'])) {
            $pdf->Ln(5);
            $pdf->SetFillColor(241, 245, 249);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(0, 7, $this->t('  2. DESGLOSE DE EVALUACIONES DE LA ASIGNATURA'), 1, 1, 'L', true);
            $pdf->Ln(2);

            foreach ($boletin['materias'] as $mat) {
                $pdf->SetFont('Arial', 'B', 8.5);
                $pdf->SetFillColor(226, 232, 240);
                $cabeceraMat = $mat['materia_codigo'] . ' - ' . $mat['materia_nombre']
                    . '  |  Seccion ' . $mat['codigo_seccion']
                    . '  |  Periodo ' . $mat['periodo_nombre']
                    . '  |  Docente: ' . $mat['profesor_nombre'];
                $pdf->Cell(192, 6.5, $this->t(' ' . $cabeceraMat), 1, 1, 'L', true);

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(112, 6, 'Actividad de Evaluacion', 1, 0, 'L');
                $pdf->Cell(40, 6, 'Ponderacion (%)', 1, 0, 'C');
                $pdf->Cell(40, 6, 'Calificacion (0-20)', 1, 1, 'C');

                $pdf->SetFont('Arial', '', 8);
                if (empty($mat['actividades'])) {
                    $pdf->Cell(192, 6, $this->t('Sin plan de evaluacion registrado.'), 1, 1, 'C');
                } else {
                    foreach ($mat['actividades'] as $act) {
                        $calif = $act['nota'] === null ? 'Pendiente' : number_format(floatval($act['nota']), 2);
                        $pdf->Cell(112, 5.5, $this->t($act['nombre_actividad']), 1, 0, 'L');
                        $pdf->Cell(40, 5.5, number_format(floatval($act['porcentaje']), 2) . '%', 1, 0, 'C');
                        $pdf->Cell(40, 5.5, $this->t($calif), 1, 1, 'C');
                    }
                }
                $pdf->Ln(2);
            }
        }

        // Pie de firma y fecha de emision
        $pdf->Ln(6);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(0, 5, $this->t('Documento emitido por el Sistema de Control de Estudios — ' . Institucion::NOMBRE), 0, 1, 'C');
        $pdf->Cell(0, 5, $this->t('Fecha de generacion: ' . date('d/m/Y H:i')), 0, 1, 'C');

        $nombreArchivo = 'boletin_' . $this->sanearNombre($estudiante['cedula'] . '_' . $estudiante['apellidos']) . '.pdf';
        $pdf->Output('D', $nombreArchivo);
        exit;
    }

    // Descarga el reporte de notas de una seccion en Excel nativo (con proteccion anti-formulas SEC-07)
    public function notasExcel(): void
    {
        $seccion = $this->resolverSeccion(intval($_GET['id'] ?? 0));
        $materiaId = intval($seccion['materia_id']);

        $actividades = $this->modelPlan->obtenerPorMateria($materiaId);
        $reporte = $this->modelNota->obtenerReporteNotas($seccion['id'], $materiaId);

        $totalColumnas = 4 + count($actividades) + 2;

        $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="UTF-8"></head><body>';
        $html .= '<table border="1">';

        $html .= '<tr><th colspan="' . $totalColumnas . '" style="font-size:16px;">' . $this->sanearCeldaExcel(Institucion::NOMBRE) . '</th></tr>';
        $html .= '<tr><th colspan="' . $totalColumnas . '" style="font-size:13px;">REPORTE DE NOTAS</th></tr>';
        $html .= '<tr><td colspan="' . $totalColumnas . '">' . $this->sanearCeldaExcel(Institucion::LEMA) . '</td></tr>';
        $html .= '<tr><td colspan="2">Materia: ' . $this->sanearCeldaExcel($seccion['materia_codigo'] . ' - ' . $seccion['materia_nombre']) . '</td>'
               . '<td colspan="2">Seccion: ' . $this->sanearCeldaExcel($seccion['codigo_seccion']) . '</td>'
               . '<td colspan="2">Periodo: ' . $this->sanearCeldaExcel($seccion['periodo_nombre']) . '</td></tr>';
        $html .= '<tr><td colspan="2">Profesor: ' . $this->sanearCeldaExcel($seccion['profesor_nombre']) . '</td>'
               . '<td colspan="2">Aula: ' . $this->sanearCeldaExcel($seccion['aula'] ?? 'Sin asignar') . '</td>'
               . '<td colspan="2">Generado: ' . date('d/m/Y H:i') . '</td></tr>';
        $html .= '<tr></tr>';

        $html .= '<tr><th>#</th><th>Cedula</th><th>Nombres</th><th>Apellidos</th>';
        foreach ($actividades as $actividad) {
            $html .= '<th>' . $this->sanearCeldaExcel($actividad['nombre_actividad']) . '</th>';
        }
        $html .= '<th>Nota Final</th><th>Estado</th></tr>';

        $i = 1;
        foreach ($reporte as $fila) {
            $html .= '<tr><td>' . $i . '</td>'
                   . '<td>' . $this->sanearCeldaExcel($fila['cedula']) . '</td>'
                   . '<td>' . $this->sanearCeldaExcel($fila['nombres']) . '</td>'
                   . '<td>' . $this->sanearCeldaExcel($fila['apellidos']) . '</td>';
            foreach ($actividades as $actividad) {
                $valor = $fila['notas'][$actividad['id']] ?? null;
                $html .= '<td>' . ($valor === null ? '-' : number_format(floatval($valor), 2)) . '</td>';
            }
            $textoFinal = $fila['nota_final'] === null ? '-' : number_format($fila['nota_final'], 2);
            $html .= '<td>' . $textoFinal . '</td>'
                   . '<td>' . $this->textoEstado($fila['estado']) . '</td></tr>';
            $i++;
        }

        $html .= '</table></body></html>';

        $nombreArchivo = 'notas_' . $this->sanearNombre(
            $seccion['materia_codigo'] . '_Sec' . $seccion['codigo_seccion'] . '_' . $seccion['periodo_nombre']
        );
        $this->enviarExcel($nombreArchivo, $html);
    }

    // Descarga el listado de estudiantes inscritos en una seccion en Excel (con proteccion anti-formulas SEC-07)
    public function inscritosExcel(): void
    {
        $seccion = $this->resolverSeccion(intval($_GET['id'] ?? 0));

        $inscritos = $this->modelEstudiante->obtenerInscritosPorSeccion($seccion['id']);

        $html = '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="UTF-8"></head><body>';
        $html .= '<table border="1">';

        $html .= '<tr><th colspan="9" style="font-size:16px;">' . $this->sanearCeldaExcel(Institucion::NOMBRE) . '</th></tr>';
        $html .= '<tr><th colspan="9" style="font-size:13px;">ESTUDIANTES INSCRITOS EN LA SECCION</th></tr>';
        $html .= '<tr><td colspan="3">Materia: ' . $this->sanearCeldaExcel($seccion['materia_codigo'] . ' - ' . $seccion['materia_nombre']) . '</td>'
               . '<td colspan="3">Seccion: ' . $this->sanearCeldaExcel($seccion['codigo_seccion']) . ' (Cupo: ' . intval($seccion['cupo_maximo'] ?? Institucion::CUPO_MAXIMO_SECCION) . ')</td>'
               . '<td colspan="3">Periodo: ' . $this->sanearCeldaExcel($seccion['periodo_nombre']) . '</td></tr>';
        $html .= '<tr><td colspan="3">Profesor: ' . $this->sanearCeldaExcel($seccion['profesor_nombre']) . '</td>'
               . '<td colspan="3">Aula: ' . $this->sanearCeldaExcel($seccion['aula'] ?? 'Sin asignar') . '</td>'
               . '<td colspan="3">Generado: ' . date('d/m/Y H:i') . '</td></tr>';
        $html .= '<tr></tr>';

        $html .= '<tr><th>#</th><th>Cedula</th><th>Nombres</th><th>Apellidos</th><th>Edad</th>'
               . '<th>Genero</th><th>Carrera</th><th>Correo</th><th>Telefono</th></tr>';

        $i = 1;
        foreach ($inscritos as $estudiante) {
            $html .= '<tr><td>' . $i . '</td>'
                   . '<td>' . $this->sanearCeldaExcel($estudiante['cedula']) . '</td>'
                   . '<td>' . $this->sanearCeldaExcel($estudiante['nombres']) . '</td>'
                   . '<td>' . $this->sanearCeldaExcel($estudiante['apellidos']) . '</td>'
                   . '<td>' . $this->sanearCeldaExcel((string)($estudiante['edad'] ?? '')) . '</td>'
                   . '<td>' . $this->textoGenero($estudiante['genero']) . '</td>'
                   . '<td>' . $this->sanearCeldaExcel($estudiante['carrera'] ?? 'Sin asignar') . '</td>'
                   . '<td>' . $this->sanearCeldaExcel($estudiante['correo'] ?? '') . '</td>'
                   . '<td>' . $this->sanearCeldaExcel($estudiante['telefono'] ?? '') . '</td></tr>';
            $i++;
        }

        $html .= '</table></body></html>';

        $nombreArchivo = 'inscritos_' . $this->sanearNombre(
            $seccion['materia_codigo'] . '_Sec' . $seccion['codigo_seccion'] . '_' . $seccion['periodo_nombre']
        );
        $this->enviarExcel($nombreArchivo, $html);
    }

    // ============================================================
    // METODOS PRIVADOS DE APOYO
    // ============================================================

    // Neutraliza la inyeccion de formulas en Excel/Calc si el texto inicia con =, +, -, @ (SEC-07)
    private function sanearCeldaExcel(?string $valor): string
    {
        $texto = (string)($valor ?? '');
        if ($texto !== '' && in_array($texto[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            $texto = "'" . $texto;
        }
        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }

    private function resolverSeccion(int $seccionId): array
    {
        $rol = $_SESSION['rol'] ?? '';
        $usuarioId = intval($_SESSION['usuario_id'] ?? 0);

        if ($seccionId <= 0) {
            $_SESSION['swal'] = [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Debe seleccionar una seccion valida.'
            ];
            header('Location: ' . BASE_URL . 'reporte/index');
            exit;
        }

        $seccion = $this->modelSeccion->obtenerPorId($seccionId);
        if (!$seccion) {
            $_SESSION['swal'] = [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'La seccion seleccionada no existe.'
            ];
            header('Location: ' . BASE_URL . 'reporte/index');
            exit;
        }

        if ($rol === 'profesor' && !$this->modelSeccion->perteneceAProfesor($seccionId, $usuarioId)) {
            $_SESSION['swal'] = [
                'icon' => 'error',
                'title' => 'Sin permisos',
                'text' => 'Solo puedes generar reportes de las secciones que dictas.'
            ];
            header('Location: ' . BASE_URL . 'reporte/index');
            exit;
        }

        return $seccion;
    }

    private function t(string $texto): string
    {
        $convertido = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
        return $convertido === false ? $texto : $convertido;
    }

    private function dividirEnDosLineas(\FPDF $pdf, string $texto, float $ancho): array
    {
        $texto = $this->t(trim($texto));
        $max = $ancho - 1.5;

        if ($pdf->GetStringWidth($texto) <= $max) {
            return [$texto, ''];
        }

        $palabras = explode(' ', $texto);
        $linea1 = '';
        $linea2 = '';

        foreach ($palabras as $palabra) {
            if ($linea2 === '') {
                $prueba = ($linea1 === '') ? $palabra : $linea1 . ' ' . $palabra;
                if ($pdf->GetStringWidth($prueba) <= $max) {
                    $linea1 = $prueba;
                    continue;
                }
                if ($linea1 === '') {
                    $linea1 = $this->recortarAlAncho($pdf, $palabra, $max);
                    continue;
                }
                $linea2 = $palabra;
            } else {
                $linea2 .= ' ' . $palabra;
            }
        }

        if ($linea2 !== '' && $pdf->GetStringWidth($linea2) > $max) {
            $linea2 = $this->recortarAlAncho($pdf, $linea2, $max);
        }

        return [$linea1, $linea2];
    }

    private function recortarAlAncho(\FPDF $pdf, string $texto, float $max): string
    {
        if ($pdf->GetStringWidth($texto) <= $max) {
            return $texto;
        }
        while (strlen($texto) > 1 && $pdf->GetStringWidth($texto . '..') > $max) {
            $texto = substr($texto, 0, -1);
        }
        return $texto . '..';
    }

    private function textoEstado(string $estado): string
    {
        return match ($estado) {
            'aprobado' => 'Aprobado',
            'reprobado' => 'Reprobado',
            'en_curso' => 'En curso',
            default => 'Sin notas'
        };
    }

    private function textoGenero(string $genero): string
    {
        return match ($genero) {
            'M' => 'Masculino',
            'F' => 'Femenino',
            default => 'Otro'
        };
    }

    private function contarEstados(array $reporte): array
    {
        $conteo = ['aprobado' => 0, 'reprobado' => 0, 'en_curso' => 0, 'sin_notas' => 0];
        foreach ($reporte as $fila) {
            $conteo[$fila['estado']]++;
        }
        return $conteo;
    }

    private function sanearNombre(string $texto): string
    {
        return preg_replace('/[^A-Za-z0-9]+/', '_', $texto);
    }

    private function enviarExcel(string $nombreArchivo, string $html): void
    {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF" . $html;
        exit;
    }
}