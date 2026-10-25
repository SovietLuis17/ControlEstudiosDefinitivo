<?php
// app/Controllers/NotaController.php
// Controlador para gestionar las calificaciones de los estudiantes
// Compatible al 100% con el esquema de base de datos en produccion (`estudiantes.seccion_id`).
// Incluye validacion estricta de pertenencia de estudiante y actividades (SEC-04)
// y bloqueo automatico de notas cuando el periodo academico esta cerrado (`periodo_estado = 'cerrado'`).

namespace App\Controllers;

use App\Config\Database;
use App\Models\Nota;
use App\Models\Seccion;
use App\Models\PlanEvaluacion;
use App\Models\Estudiante;

class NotaController
{
    private Nota $model;
    private Seccion $modelSeccion;
    private PlanEvaluacion $modelPlan;
    private Estudiante $modelEstudiante;

    public function __construct()
    {
        $database = new Database();
        $this->model = new Nota($database->getConnection());
        $this->modelSeccion = new Seccion($database->getConnection());
        $this->modelPlan = new PlanEvaluacion($database->getConnection());
        $this->modelEstudiante = new Estudiante($database->getConnection());
    }

    // Muestra la vista principal con el resumen de notas de una seccion
    public function index(): void
    {
        $seccionId = intval($_GET['id'] ?? 0);
        $usuarioId = intval($_SESSION['usuario_id'] ?? 0);
        $rol = $_SESSION['rol'] ?? '';

        if ($seccionId <= 0) {
            $_SESSION['swal'] = [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'No se recibio una seccion valida.'
            ];
            header('Location: ' . BASE_URL . 'seccion/index');
            exit;
        }

        // Si es profesor, validar que la seccion le pertenezca
        if ($rol === 'profesor') {
            if (!$this->modelSeccion->perteneceAProfesor($seccionId, $usuarioId)) {
                $_SESSION['swal'] = [
                    'icon' => 'error',
                    'title' => 'Sin permisos',
                    'text' => 'No puedes ver las notas de una seccion que no es tuya.'
                ];
                header('Location: ' . BASE_URL . 'seccion/index');
                exit;
            }
        }

        // Obtener datos de la seccion (incluye `periodo_estado`)
        $seccion = $this->modelSeccion->obtenerPorId($seccionId);
        if (!$seccion) {
            $_SESSION['swal'] = [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'La seccion seleccionada no existe.'
            ];
            header('Location: ' . BASE_URL . 'seccion/index');
            exit;
        }

        $materiaId = intval($seccion['materia_id']);

        // Verificar que el plan de evaluacion este completo (suma 100)
        $planCompleto = $this->modelPlan->planCompleto($materiaId);
        if (!$planCompleto) {
            $_SESSION['swal'] = [
                'icon' => 'warning',
                'title' => 'Plan incompleto',
                'text' => 'El plan de evaluacion de esta materia no suma 100%. Complete el plan antes de cargar notas.'
            ];
            header('Location: ' . BASE_URL . 'seccion/index');
            exit;
        }

        // Obtener resumen de notas de la seccion
        $resumen = $this->model->obtenerResumenSeccion($seccionId, $materiaId);
        $actividades = $this->modelPlan->obtenerPorMateria($materiaId);

        require_once __DIR__ . '/../Views/nota/index.php';
    }

    // Devuelve JSON con las notas de un estudiante para una materia
    public function ver(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => 'error',
                'title' => 'Metodo no permitido',
                'message' => 'La solicitud debe ser POST.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $estudianteId = intval($_POST['estudiante_id'] ?? 0);
        $seccionId = intval($_POST['seccion_id'] ?? 0);
        $usuarioId = intval($_SESSION['usuario_id'] ?? 0);
        $rol = $_SESSION['rol'] ?? '';

        if ($estudianteId <= 0 || $seccionId <= 0) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Datos invalidos',
                'message' => 'Se requieren estudiante y seccion validos.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($rol === 'profesor') {
            if (!$this->modelSeccion->perteneceAProfesor($seccionId, $usuarioId)) {
                echo json_encode([
                    'status' => 'error',
                    'title' => 'Sin permisos',
                    'message' => 'No puedes ver las notas de una seccion que no es tuya.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        $seccion = $this->modelSeccion->obtenerPorId($seccionId);
        if (!$seccion) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Seccion no encontrada',
                'message' => 'La seccion seleccionada no existe.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $materiaId = intval($seccion['materia_id']);

        $estudiante = $this->modelEstudiante->obtenerPorId($estudianteId);
        if (!$estudiante) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Estudiante no encontrado',
                'message' => 'El estudiante seleccionado no existe.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (intval($estudiante['seccion_id']) !== $seccionId) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Estudiante no inscrito',
                'message' => 'Este estudiante no pertenece a la seccion seleccionada.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $actividades = $this->model->obtenerPorEstudianteYMateria($estudianteId, $materiaId);

        $notaFinal = 0;
        $totalNotas = 0;
        $totalActividades = count($actividades);

        foreach ($actividades as $actividad) {
            if ($actividad['nota'] !== null) {
                $notaFinal += floatval($actividad['nota']) * floatval($actividad['porcentaje']) / 100;
                $totalNotas++;
            }
        }
        $notaFinal = round($notaFinal, 2);

        if ($totalNotas === 0) {
            $estado = 'sin_notas';
            $notaFinal = null;
        } elseif ($totalNotas < $totalActividades) {
            $estado = 'en_curso';
            $notaFinal = null;
        } elseif ($notaFinal >= 13) {
            $estado = 'aprobado';
        } else {
            $estado = 'reprobado';
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'estudiante' => [
                    'id' => $estudiante['id'],
                    'nombres' => $estudiante['nombres'],
                    'apellidos' => $estudiante['apellidos'],
                    'cedula' => $estudiante['cedula']
                ],
                'actividades' => $actividades,
                'nota_final' => $notaFinal,
                'estado' => $estado,
                'periodo_estado' => $seccion['periodo_estado'] ?? 'activo'
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Guarda las notas de un estudiante para todas las actividades
    // Incluye verificacion anti-IDOR (SEC-04) y bloqueo estricto de periodos cerrados
    public function guardar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => 'error',
                'title' => 'Metodo no permitido',
                'message' => 'La solicitud debe ser POST.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $estudianteId = intval($_POST['estudiante_id'] ?? 0);
        $seccionId = intval($_POST['seccion_id'] ?? 0);
        $usuarioId = intval($_SESSION['usuario_id'] ?? 0);
        $rol = $_SESSION['rol'] ?? '';

        if ($estudianteId <= 0 || $seccionId <= 0) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Datos invalidos',
                'message' => 'Se requieren estudiante y seccion validos.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($rol === 'profesor') {
            if (!$this->modelSeccion->perteneceAProfesor($seccionId, $usuarioId)) {
                echo json_encode([
                    'status' => 'error',
                    'title' => 'Sin permisos',
                    'message' => 'No puedes guardar notas en una seccion que no es tuya.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        $seccion = $this->modelSeccion->obtenerPorId($seccionId);
        if (!$seccion) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Seccion no encontrada',
                'message' => 'La seccion seleccionada no existe.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // BLOQUEO DE PERIODO CERRADO: Si el periodo esta cerrado, nadie puede alterar calificaciones
        if (($seccion['periodo_estado'] ?? 'activo') === 'cerrado') {
            echo json_encode([
                'status' => 'error',
                'title' => 'Periodo cerrado',
                'message' => 'El periodo academico (' . $seccion['periodo_nombre'] . ') se encuentra cerrado. Las calificaciones estan en modo de solo lectura.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $notasRecibidas = $_POST['notas'] ?? [];

        if (empty($notasRecibidas) || !is_array($notasRecibidas)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Sin datos',
                'message' => 'No se recibieron notas para guardar.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Verificar que el estudiante exista y pertenezca efectivamente a esta seccion (SEC-04)
        $estudiante = $this->modelEstudiante->obtenerPorId($estudianteId);
        if (!$estudiante || intval($estudiante['seccion_id']) !== $seccionId) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Estudiante no inscrito',
                'message' => 'Este estudiante no pertenece a la seccion seleccionada.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $materiaId = intval($seccion['materia_id']);

        if (!$this->modelPlan->planCompleto($materiaId)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Plan incompleto',
                'message' => 'El plan de evaluacion de esta materia no suma 100%.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Obtener los IDs de actividades que pertenecen legitimamente a esta materia (SEC-04)
        $actividadesMateria = $this->modelPlan->obtenerPorMateria($materiaId);
        $idsActividadesPermitidas = array_map('intval', array_column($actividadesMateria, 'id'));

        $notasParaGuardar = [];
        foreach ($notasRecibidas as $actividadId => $notaValor) {
            $actividadId = intval($actividadId);
            $notaValor = trim((string)$notaValor);

            if ($notaValor === '') {
                continue;
            }

            // Verificar que la actividad pertenezca a la materia de la seccion (SEC-04)
            if (!in_array($actividadId, $idsActividadesPermitidas, true)) {
                echo json_encode([
                    'status' => 'error',
                    'title' => 'Actividad invalida',
                    'message' => 'Una de las actividades enviadas no pertenece al plan de evaluacion de esta materia.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            if (!is_numeric($notaValor)) {
                echo json_encode([
                    'status' => 'error',
                    'title' => 'Formato invalido',
                    'message' => 'Las calificaciones deben ser valores numericos.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $notaFloat = floatval($notaValor);

            if ($notaFloat < 0 || $notaFloat > 20) {
                echo json_encode([
                    'status' => 'error',
                    'title' => 'Nota invalida',
                    'message' => 'Las notas deben estar entre 0 y 20.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $notasParaGuardar[] = [
                'actividad_id' => $actividadId,
                'nota' => $notaFloat
            ];
        }

        if (empty($notasParaGuardar)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Sin notas validas',
                'message' => 'Debe ingresar al menos una nota.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $resultado = $this->model->guardarNotasEstudiante($estudianteId, $notasParaGuardar);

        if ($resultado) {
            echo json_encode([
                'status' => 'success',
                'title' => 'Notas guardadas',
                'message' => 'Las calificaciones se guardaron correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'status' => 'error',
                'title' => 'Error',
                'message' => 'No se pudieron guardar las notas.'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // Elimina un registro de nota especifico (solo admin, bloqueado si la nota corresponde a un periodo cerrado)
    public function eliminar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'status' => 'error',
                'title' => 'Metodo no permitido',
                'message' => 'La solicitud debe ser POST.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $notaId = intval($_POST['nota_id'] ?? 0);

        if ($notaId <= 0) {
            echo json_encode([
                'status' => 'error',
                'title' => 'ID invalido',
                'message' => 'No se recibio un identificador valido.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $detalleNota = $this->model->obtenerDetalleNotaPorId($notaId);
        if (!$detalleNota) {
            echo json_encode([
                'status' => 'error',
                'title' => 'No encontrada',
                'message' => 'La calificacion que intentas eliminar no existe.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($this->modelSeccion->tieneSeccionesCerradasConNotas(intval($detalleNota['materia_id']))) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Periodo cerrado',
                'message' => 'No se puede eliminar esta calificacion porque pertenece a una asignatura con actas cerradas.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $resultado = $this->model->eliminar($notaId);

        if ($resultado) {
            echo json_encode([
                'status' => 'success',
                'title' => 'Nota eliminada',
                'message' => 'La calificacion se elimino correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'status' => 'error',
                'title' => 'Error',
                'message' => 'No se pudo eliminar la calificacion.'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}