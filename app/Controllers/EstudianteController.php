<?php
// app/Controllers/EstudianteController.php
// Controlador para gestionar los estudiantes del sistema
// Compatible al 100% con el esquema de base de datos en produccion (`estudiantes.seccion_id`).
// Incluye las mejoras rapidas sin alterar la base de datos:
// - Bloqueo automatico de inscripciones, retiros y eliminaciones cuando el periodo esta cerrado (`estado = 'cerrado'`).
// - Control de cupo maximo por seccion (`Institucion::CUPO_MAXIMO_SECCION`).
// - Proteccion anti-IDOR en edicion de estudiantes por parte de profesores (SEC-04).

namespace App\Controllers;

use App\Config\Database;
use App\Config\Institucion;
use App\Models\Estudiante;
use App\Models\Seccion;
use App\Models\Nota;

class EstudianteController
{
    private Estudiante $model;
    private Seccion $modelSeccion;
    private Nota $modelNota;

    public function __construct()
    {
        $database = new Database();
        $this->model = new Estudiante($database->getConnection());
        $this->modelSeccion = new Seccion($database->getConnection());
        $this->modelNota = new Nota($database->getConnection());
    }

    // Muestra la vista principal con busqueda por cedula y paginacion
    public function index(): void
    {
        $rol = $_SESSION['rol'] ?? '';
        $busqueda = trim($_GET['busqueda'] ?? '');

        if ($busqueda !== '' && !preg_match('/^[0-9]+$/', $busqueda)) {
            $_SESSION['swal'] = [
                'icon' => 'warning',
                'title' => 'Busqueda invalida',
                'text' => 'La busqueda por cedula solo acepta numeros.'
            ];
            header('Location: ' . BASE_URL . 'estudiante/index');
            exit;
        }

        $porPagina = 10;
        $totalEstudiantes = $this->model->contarTotal($busqueda);
        $totalPaginas = max(1, (int) ceil($totalEstudiantes / $porPagina));

        $pagina = intval($_GET['pagina'] ?? 1);
        if ($pagina < 1) {
            $pagina = 1;
        }
        if ($pagina > $totalPaginas) {
            $pagina = $totalPaginas;
        }

        $offset = ($pagina - 1) * $porPagina;
        $estudiantes = $this->model->obtenerPaginados($porPagina, $offset, $busqueda);

        require_once __DIR__ . '/../Views/estudiante/index.php';
    }

    // Guarda un estudiante (crear o actualizar)
    // Incluye validaciones fuertes de formato y proteccion anti-IDOR en edicion (SEC-04)
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

        $id = intval($_POST['id'] ?? 0);
        $rol = $_SESSION['rol'] ?? '';
        $usuarioId = intval($_SESSION['usuario_id'] ?? 0);

        // Si es edicion, verificar existencia del estudiante y permisos del profesor (SEC-04)
        if ($id > 0) {
            $estudianteActual = $this->model->obtenerPorId($id);
            if (!$estudianteActual) {
                echo json_encode([
                    'status' => 'error',
                    'title' => 'No encontrado',
                    'message' => 'El estudiante que intentas editar no existe.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Si el usuario es profesor y el estudiante ya pertenece a una seccion de otro profesor, impedir edicion (SEC-04)
            if ($rol === 'profesor' && !empty($estudianteActual['seccion_id'])) {
                $seccionEstudiante = intval($estudianteActual['seccion_id']);
                if (!$this->modelSeccion->perteneceAProfesor($seccionEstudiante, $usuarioId)) {
                    echo json_encode([
                        'status' => 'error',
                        'title' => 'Sin permisos',
                        'message' => 'No puedes modificar los datos de un estudiante inscrito en la seccion de otro profesor.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        }

        $cedula = trim($_POST['cedula'] ?? '');
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $fechaNacimiento = $_POST['fecha_nacimiento'] ?? '';
        $genero = $_POST['genero'] ?? '';
        $direccion = trim($_POST['direccion'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $carrera = trim($_POST['carrera'] ?? '');

        if (empty($cedula)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Campo obligatorio',
                'message' => 'La cedula es obligatoria.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($nombres)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Campo obligatorio',
                'message' => 'Los nombres son obligatorios.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($apellidos)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Campo obligatorio',
                'message' => 'Los apellidos son obligatorios.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($fechaNacimiento)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Campo obligatorio',
                'message' => 'La fecha de nacimiento es obligatoria.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($genero) || !in_array($genero, ['M', 'F', 'Otro'], true)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Campo obligatorio',
                'message' => 'Debe seleccionar un genero valido.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!$this->validarSoloNumeros($cedula)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Cedula invalida',
                'message' => 'La cedula solo puede contener numeros.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!$this->validarSoloLetras($nombres)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Nombres invalidos',
                'message' => 'Los nombres solo pueden contener letras y espacios.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!$this->validarSoloLetras($apellidos)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Apellidos invalidos',
                'message' => 'Los apellidos solo pueden contener letras y espacios.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!empty($telefono) && !$this->validarTelefono($telefono)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Telefono invalido',
                'message' => 'El telefono solo puede contener numeros, guiones y el signo +.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!empty($carrera) && !$this->validarTextoSimple($carrera)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Carrera invalida',
                'message' => 'La carrera solo puede contener letras, numeros, espacios, puntos y guiones.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Correo invalido',
                'message' => 'El correo no tiene un formato valido.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($id === 0) {
            if ($this->model->existeCedula($cedula)) {
                echo json_encode([
                    'status' => 'error',
                    'title' => 'Cedula duplicada',
                    'message' => 'Ya existe un estudiante con esa cedula.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        } else {
            if ($this->model->existeCedulaExceptoId($cedula, $id)) {
                echo json_encode([
                    'status' => 'error',
                    'title' => 'Cedula duplicada',
                    'message' => 'Ya existe otro estudiante con esa cedula.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        if (!empty($correo)) {
            if ($id === 0) {
                if ($this->model->existeCorreo($correo)) {
                    echo json_encode([
                        'status' => 'error',
                        'title' => 'Correo duplicado',
                        'message' => 'Ya existe un estudiante con ese correo.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            } else {
                if ($this->model->existeCorreoExceptoId($correo, $id)) {
                    echo json_encode([
                        'status' => 'error',
                        'title' => 'Correo duplicado',
                        'message' => 'Ya existe otro estudiante con ese correo.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        }

        $datos = [
            'cedula' => $cedula,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'fecha_nacimiento' => $fechaNacimiento,
            'genero' => $genero,
            'direccion' => $direccion,
            'telefono' => $telefono,
            'correo' => $correo,
            'carrera' => $carrera
        ];

        if ($id === 0) {
            $resultado = $this->model->crear($datos);
            if ($resultado) {
                echo json_encode([
                    'status' => 'success',
                    'title' => 'Estudiante creado',
                    'message' => 'El estudiante se registro correctamente.'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'title' => 'Error',
                    'message' => 'No se pudo crear el estudiante.'
                ], JSON_UNESCAPED_UNICODE);
            }
        } else {
            $resultado = $this->model->actualizar($id, $datos);
            if ($resultado) {
                echo json_encode([
                    'status' => 'success',
                    'title' => 'Estudiante actualizado',
                    'message' => 'Los cambios se guardaron correctamente.'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'title' => 'Error',
                    'message' => 'No se pudo actualizar el estudiante.'
                ], JSON_UNESCAPED_UNICODE);
            }
        }
        exit;
    }

    // Elimina un estudiante por su id (solo admin)
    // Impide eliminar si su seccion actual pertenece a un periodo cerrado
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

        $id = intval($_POST['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode([
                'status' => 'error',
                'title' => 'ID invalido',
                'message' => 'No se recibio un identificador valido.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($this->model->tieneInscripcionEnPeriodoCerrado($id)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Periodo cerrado',
                'message' => 'No se puede eliminar este estudiante porque pertenece a una seccion de un periodo cerrado.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $resultado = $this->model->eliminar($id);

        if ($resultado) {
            echo json_encode([
                'status' => 'success',
                'title' => 'Estudiante eliminado',
                'message' => 'El estudiante se elimino correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'status' => 'error',
                'title' => 'Error',
                'message' => 'No se pudo eliminar el estudiante.'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // Asigna un estudiante a una seccion (`estudiantes.seccion_id`)
    // Valida: pertenencia del profesor, estado activo del periodo y cupo maximo de la seccion
    public function asignarSeccion(): void
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
                    'message' => 'No puedes asignar estudiantes a una seccion que no es tuya.'
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

        // BLOQUEO DE PERIODO CERRADO: No se permite inscribir en periodos cerrados
        if (($seccion['periodo_estado'] ?? 'activo') === 'cerrado') {
            echo json_encode([
                'status' => 'error',
                'title' => 'Periodo cerrado',
                'message' => 'El periodo academico (' . $seccion['periodo_nombre'] . ') esta cerrado. No se permiten nuevas inscripciones en esta seccion.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // VALIDACION DE CUPO MAXIMO DE LA SECCION (sin alterar la base de datos)
        $inscritosActuales = $this->model->contarPorSeccion($seccionId);
        $cupoMaximo = intval($seccion['cupo_maximo'] ?? Institucion::CUPO_MAXIMO_SECCION);
        if ($inscritosActuales >= $cupoMaximo) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Cupo agotado',
                'message' => 'La seccion alcanzo su cupo maximo permitido (' . $cupoMaximo . ' estudiantes).'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $estudiante = $this->model->obtenerPorId($estudianteId);
        if (!$estudiante) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Estudiante no encontrado',
                'message' => 'El estudiante que intentas asignar no existe.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!empty($estudiante['seccion_id'])) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Estudiante ya asignado',
                'message' => 'Este estudiante ya pertenece a una seccion.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $resultado = $this->model->asignarSeccion($estudianteId, $seccionId);

        if ($resultado) {
            echo json_encode([
                'status' => 'success',
                'title' => 'Estudiante asignado',
                'message' => 'El estudiante se asigno a la seccion correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'status' => 'error',
                'title' => 'Error',
                'message' => 'No se pudo asignar el estudiante.'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // Quita un estudiante de su seccion actual (`estudiantes.seccion_id = NULL`)
    // Bloquea el retiro si el periodo academico de la seccion esta cerrado
    public function quitarSeccion(): void
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
        $usuarioId = intval($_SESSION['usuario_id'] ?? 0);
        $rol = $_SESSION['rol'] ?? '';

        if ($estudianteId <= 0) {
            echo json_encode([
                'status' => 'error',
                'title' => 'ID invalido',
                'message' => 'No se recibio un identificador valido.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $estudiante = $this->model->obtenerPorId($estudianteId);
        if (!$estudiante) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Estudiante no encontrado',
                'message' => 'El estudiante que intentas quitar no existe.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($estudiante['seccion_id'])) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Sin seccion',
                'message' => 'Este estudiante no pertenece a ninguna seccion.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $seccionId = intval($estudiante['seccion_id']);

        if ($rol === 'profesor') {
            if (!$this->modelSeccion->perteneceAProfesor($seccionId, $usuarioId)) {
                echo json_encode([
                    'status' => 'error',
                    'title' => 'Sin permisos',
                    'message' => 'No puedes quitar estudiantes de una seccion que no es tuya.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        $seccion = $this->modelSeccion->obtenerPorId($seccionId);
        if ($seccion) {
            // BLOQUEO DE PERIODO CERRADO: No se permite retirar estudiantes ni borrar notas de periodos cerrados
            if (($seccion['periodo_estado'] ?? 'activo') === 'cerrado') {
                echo json_encode([
                    'status' => 'error',
                    'title' => 'Periodo cerrado',
                    'message' => 'El periodo academico (' . $seccion['periodo_nombre'] . ') esta cerrado. No se puede retirar estudiantes ni alterar sus notas.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $this->modelNota->eliminarPorEstudianteYMateria($estudianteId, intval($seccion['materia_id']));
        }

        $resultado = $this->model->quitarSeccion($estudianteId);

        if ($resultado) {
            echo json_encode([
                'status' => 'success',
                'title' => 'Estudiante retirado',
                'message' => 'El estudiante se quito de la seccion y sus notas fueron eliminadas.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'status' => 'error',
                'title' => 'Error',
                'message' => 'No se pudo quitar el estudiante de la seccion.'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function validarSoloLetras(string $valor): bool
    {
        return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/', $valor) === 1;
    }

    private function validarSoloNumeros(string $valor): bool
    {
        return preg_match('/^[0-9]+$/', $valor) === 1;
    }

    private function validarTelefono(string $valor): bool
    {
        return preg_match('/^[0-9\-\+\s]+$/', $valor) === 1;
    }

    private function validarTextoSimple(string $valor): bool
    {
        return preg_match('/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\-]+$/', $valor) === 1;
    }
}