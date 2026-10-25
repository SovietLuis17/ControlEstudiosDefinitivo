<?php
// app/Controllers/PerfilController.php
// Controlador para el modulo de autogestion "Mi Perfil y Cambio de Contrasena"
// Permite tanto a Administradores como a Profesores actualizar sus datos de contacto
// y cambiar su contrasena verificando primero su clave actual con `password_verify()`.

namespace App\Controllers;

use App\Config\Database;
use App\Models\Profesor;

class PerfilController
{
    private Profesor $modelProfesor;

    public function __construct()
    {
        $database = new Database();
        $this->modelProfesor = new Profesor($database->getConnection());
    }

    // Muestra la vista "Mi Perfil" con los datos actuales del usuario en sesion
    public function index(): void
    {
        $usuarioId = intval($_SESSION['usuario_id'] ?? 0);
        $usuario = $this->modelProfesor->obtenerPorId($usuarioId);

        if (!$usuario) {
            header('Location: ' . BASE_URL . 'dashboard/index');
            exit;
        }

        require_once __DIR__ . '/../Views/perfil/index.php';
    }

    // Actualiza los datos personales de contacto del usuario autenticado via AJAX (POST)
    public function guardarDatos(): void
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

        $usuarioId = intval($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId <= 0) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Sesion invalida',
                'message' => 'No se pudo identificar tu sesion activa.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $fechaNacimiento = trim($_POST['fecha_nacimiento'] ?? '');
        $genero = trim($_POST['genero'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $especialidad = trim($_POST['especialidad'] ?? '');

        if (empty($nombres) || empty($apellidos) || empty($fechaNacimiento) || empty($genero) || empty($correo)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Campos obligatorios',
                'message' => 'Nombres, apellidos, fecha de nacimiento, genero y correo electronico son obligatorios.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!in_array($genero, ['M', 'F', 'Otro'], true)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Genero invalido',
                'message' => 'Seleccione una opcion de genero valida.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!$this->validarSoloLetras($nombres) || !$this->validarSoloLetras($apellidos)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Nombre invalido',
                'message' => 'Los nombres y apellidos solo pueden contener letras y espacios.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!empty($telefono) && !$this->validarTelefono($telefono)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Telefono invalido',
                'message' => 'El telefono solo puede contener numeros, espacios, guiones y el signo +.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!empty($especialidad) && !$this->validarTextoSimple($especialidad)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Especialidad invalida',
                'message' => 'La especialidad solo puede contener letras, numeros, espacios, puntos y guiones.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Correo invalido',
                'message' => 'Ingresa un correo electronico con formato valido.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($this->modelProfesor->existeCorreoExceptoId($correo, $usuarioId)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Correo en uso',
                'message' => 'Ya existe otra cuenta registrada con ese correo electronico.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $datos = [
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'fecha_nacimiento' => $fechaNacimiento,
            'genero' => $genero,
            'direccion' => $direccion,
            'telefono' => $telefono,
            'correo' => $correo,
            'especialidad' => $especialidad
        ];

        if ($this->modelProfesor->actualizarPerfil($usuarioId, $datos)) {
            // Actualizar el nombre y correo en la sesion activa inmediatamente
            $_SESSION['nombre'] = $nombres . ' ' . $apellidos;
            $_SESSION['correo'] = $correo;

            echo json_encode([
                'status' => 'success',
                'title' => 'Perfil actualizado',
                'message' => 'Tus datos personales se actualizaron correctamente.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'status' => 'error',
                'title' => 'Error',
                'message' => 'No se pudieron guardar los cambios del perfil.'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // Cambia la contrasena del usuario autenticado exigiendo verificacion de la contrasena actual
    public function cambiarPassword(): void
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

        $usuarioId = intval($_SESSION['usuario_id'] ?? 0);
        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';
        $passwordConfirmar = $_POST['password_confirmar'] ?? '';

        if (empty($passwordActual) || empty($passwordNueva) || empty($passwordConfirmar)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Campos requeridos',
                'message' => 'Debes completar tu contrasena actual, la nueva contrasena y su confirmacion.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($passwordNueva !== $passwordConfirmar) {
            echo json_encode([
                'status' => 'error',
                'title' => 'No coinciden',
                'message' => 'La nueva contrasena y su confirmacion no coinciden.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!$this->validarPasswordFuerte($passwordNueva)) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Contrasena debil',
                'message' => 'La nueva contrasena debe tener minimo 8 caracteres, una mayuscula, una minuscula, un numero y un simbolo especial.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $usuario = $this->modelProfesor->obtenerConPasswordPorId($usuarioId);
        if (!$usuario || !password_verify($passwordActual, $usuario['password'])) {
            echo json_encode([
                'status' => 'error',
                'title' => 'Contrasena actual incorrecta',
                'message' => 'La contrasena actual que ingresaste no es correcta.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $nuevoHash = password_hash($passwordNueva, PASSWORD_DEFAULT);
        if ($this->modelProfesor->actualizarPassword($usuarioId, $nuevoHash)) {
            // Regenerar ID de sesion tras cambio de credenciales por higiene criptografica
            session_regenerate_id(true);

            echo json_encode([
                'status' => 'success',
                'title' => 'Contrasena actualizada',
                'message' => 'Tu contrasena ha sido actualizada de forma segura.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'status' => 'error',
                'title' => 'Error',
                'message' => 'No se pudo actualizar la contrasena.'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function validarSoloLetras(string $valor): bool
    {
        return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/', $valor) === 1;
    }

    private function validarTelefono(string $valor): bool
    {
        return preg_match('/^[0-9\-\+\s]+$/', $valor) === 1;
    }

    private function validarTextoSimple(string $valor): bool
    {
        return preg_match('/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s\.\-]+$/', $valor) === 1;
    }

    private function validarPasswordFuerte(string $password): bool
    {
        $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\-\*\+#\$\&\.\,\!\?\@\%\(\)_\=\[\]\{\}\|:;]).{8,}$/';
        return preg_match($regex, $password) === 1;
    }
}
