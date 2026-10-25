<?php
// public/index.php
// Front Controller: unico punto de entrada del sistema
// Gestiona sesiones, cabeceras de seguridad, proteccion CSRF, rutas, permisos y ejecuta el controlador correspondiente

// ============================================================
// 1. CONFIGURACION Y SEGURIDAD DE LA SESION
// ============================================================
$esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

session_set_cookie_params([
    'lifetime' => 0,   // Cookie de sesion pura: muere al cerrar el navegador
    'path'     => '/',
    'domain'   => '',
    'secure'   => $esHttps,
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generar token CSRF criptograficamente seguro por sesion (SEC-01)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================================
// 1.5 CABECERAS DE SEGURIDAD HTTP Y PREVENCION DE CACHE (SEC-07)
// ============================================================
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; frame-ancestors 'none';");

// ============================================================
// 2. DETECCION AUTOMATICA DE BASE_URL
// ============================================================
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$scriptDir = rtrim(dirname($scriptName), '/');
$baseUrl = ($scriptDir === '/' || $scriptDir === '') ? '/' : $scriptDir . '/';
define('BASE_URL', $baseUrl);

// ============================================================
// 3. OBTENCION Y LIMPIEZA DE LA URI
// ============================================================
$uriSolicitada = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriDecodificada = urldecode($uriSolicitada);

if (BASE_URL !== '/') {
    $rutaRelativa = str_replace(BASE_URL, '', $uriDecodificada);
} else {
    $rutaRelativa = $uriDecodificada;
}

$rutaRelativa = '/' . ltrim($rutaRelativa, '/');

// ============================================================
// 4. PREVENCION DE PATH TRAVERSAL
// ============================================================
if (strpos($rutaRelativa, '../') !== false || strpos($rutaRelativa, '..\\') !== false) {
    http_response_code(400);
    die("<h1>400 - Solicitud invalida</h1>");
}

// ============================================================
// 5. LIMPIEZA Y SEPARACION DE SEGMENTOS
// ============================================================
$uriLimpia = trim($rutaRelativa, '/');
$segmentos = $uriLimpia !== '' ? explode('/', $uriLimpia) : [];
$controllerR = $segmentos[0] ?? 'auth';
$actionR = $segmentos[1] ?? 'login';
$parametrosRuta = array_slice($segmentos, 2);

// ============================================================
// 6. SANEAMIENTO DE CONTROLADOR Y ACCION
// ============================================================
$controller = ctype_alnum($controllerR) ? $controllerR : null;
$action = ctype_alnum($actionR) ? $actionR : null;

if ($controller === null || $action === null) {
    http_response_code(400);
    die("<h1>400 - Solicitud invalida</h1>");
}

// ============================================================
// 7. MATRIZ DE RUTAS Y ROLES PERMITIDOS
// ============================================================
$rutasPermitidas = [
    'auth' => [
        'login'           => ['publico'],
        'authenticate'    => ['publico'],
        'register'        => ['publico'],
        'processRegister' => ['publico'],
        'logout'          => ['admin', 'profesor']
    ],
    'dashboard' => [
        'index' => ['admin', 'profesor']
    ],
    'perfil' => [
        'index'           => ['admin', 'profesor'],
        'guardarDatos'    => ['admin', 'profesor'],
        'cambiarPassword' => ['admin', 'profesor']
    ],
    'periodo' => [
        'index'    => ['admin'],
        'guardar'  => ['admin'],
        'eliminar' => ['admin']
    ],
    'materia' => [
        'index'    => ['admin', 'profesor'],
        'guardar'  => ['admin'],
        'eliminar' => ['admin']
    ],
    'planEvaluacion' => [
        'index'    => ['admin', 'profesor'],
        'guardar'  => ['admin', 'profesor'],
        'eliminar' => ['admin', 'profesor']
    ],
    'profesor' => [
        'index'    => ['admin'],
        'guardar'  => ['admin'],
        'eliminar' => ['admin']
    ],
    'estudiante' => [
        'index'          => ['admin', 'profesor'],
        'guardar'        => ['admin', 'profesor'],
        'eliminar'       => ['admin'],
        'asignarSeccion' => ['admin', 'profesor'],
        'quitarSeccion'  => ['admin', 'profesor']
    ],
    'seccion' => [
        'index'       => ['admin', 'profesor'],
        'guardar'     => ['admin'],
        'eliminar'    => ['admin'],
        'estudiantes' => ['admin', 'profesor']
    ],
    'nota' => [
        'index'    => ['admin', 'profesor'],
        'ver'      => ['admin', 'profesor'],
        'guardar'  => ['admin', 'profesor'],
        'eliminar' => ['admin']
    ],
    'reporte' => [
        'index'          => ['admin', 'profesor'],
        'notasPdf'       => ['admin', 'profesor'],
        'notasExcel'     => ['admin', 'profesor'],
        'inscritosExcel' => ['admin', 'profesor'],
        'kardexPdf'      => ['admin', 'profesor']
    ]
];

if (!isset($rutasPermitidas[$controller][$action])) {
    http_response_code(404);
    die("<h1>404 - Ruta no encontrada</h1>");
}

// ============================================================
// 8. MIDDLEWARE DE AUTENTICACION, AUTORIZACION Y CSRF (SEC-01)
// ============================================================
$rolesPermitidos = $rutasPermitidas[$controller][$action];
$esRutaPublica = in_array('publico', $rolesPermitidos, true);

// Si no es publica y no hay sesion, redirige al login
if (!$esRutaPublica && !isset($_SESSION['usuario_id'])) {
    $_SESSION['redirect_after_login'] = $rutaRelativa;
    header('Location: ' . BASE_URL . 'auth/login');
    exit;
}

// Si no es publica, verifica que el rol tenga permiso
if (!$esRutaPublica) {
    $rolUsuario = $_SESSION['rol'] ?? null;
    if (!in_array($rolUsuario, $rolesPermitidos, true)) {
        http_response_code(403);
        die("<h1>403 - Acceso denegado</h1>");
    }
}

// Si es publica y ya hay sesion, redirige al dashboard (excepto logout)
if ($esRutaPublica && isset($_SESSION['usuario_id']) && $action !== 'logout') {
    $destino = 'dashboard/index';
    header('Location: ' . BASE_URL . $destino);
    exit;
}

// Validacion centralizada de token CSRF en todas las peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenRecibido = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $tokenSesion = $_SESSION['csrf_token'] ?? '';

    if (empty($tokenRecibido) || empty($tokenSesion) || !hash_equals($tokenSesion, (string)$tokenRecibido)) {
        // Si es envio de formulario tradicional de login o registro, redirigimos con aviso
        if ($controller === 'auth' && in_array($action, ['authenticate', 'processRegister', 'logout'], true)) {
            $_SESSION['swal'] = [
                'icon'  => 'error',
                'title' => 'Sesion o token invalido',
                'text'  => 'La solicitud expiro por seguridad. Por favor intenta nuevamente.'
            ];
            $rutaRetorno = ($action === 'processRegister') ? 'auth/register' : 'auth/login';
            header('Location: ' . BASE_URL . $rutaRetorno);
            exit;
        }

        // Respuesta JSON estandar para peticiones Fetch/AJAX
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'  => 'error',
            'title'   => 'Error de seguridad (CSRF)',
            'message' => 'El token de seguridad es invalido o ha expirado. Recarga la pagina e intenta nuevamente.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ============================================================
// 9. INYECCION DE PARAMETROS EN $_GET
// ============================================================
if (!empty($parametrosRuta)) {
    foreach ($parametrosRuta as $indice => $valor) {
        $nombreVariable = ($indice === 0) ? 'id' : 'param' . ($indice + 1);
        $_GET[$nombreVariable] = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    }
}

// ============================================================
// 10. AUTOLOADER CON NAMESPACES
// ============================================================
spl_autoload_register(function ($clase) {
    $prefijo = 'App\\';
    $dir_base = __DIR__ . '/../app/';
    $longitud = strlen($prefijo);

    if (strncmp($prefijo, $clase, $longitud) !== 0) {
        return;
    }

    $clase_relativa = substr($clase, $longitud);
    $archivo = $dir_base . str_replace('\\', '/', $clase_relativa) . '.php';

    if (file_exists($archivo)) {
        require $archivo;
    }
});

// ============================================================
// 11. CARGA Y EJECUCION DEL CONTROLADOR CON MANEJO SEGURO DE ERRORES (SEC-06)
// ============================================================
$nombreClaseControlador = 'App\\Controllers\\' . ucfirst($controller) . 'Controller';

if (!class_exists($nombreClaseControlador)) {
    error_log("[ControlEstudio] Controlador faltante: {$nombreClaseControlador}");
    http_response_code(500);
    die("<h1>500 - Error interno del servidor</h1>");
}

try {
    $instanciaControlador = new $nombreClaseControlador();

    if (!method_exists($instanciaControlador, $action)) {
        error_log("[ControlEstudio] Metodo {$action} no existe en {$nombreClaseControlador}");
        http_response_code(500);
        die("<h1>500 - Error interno del servidor</h1>");
    }

    $instanciaControlador->$action();
} catch (\Throwable $e) {
    error_log("[ControlEstudio Exception] " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($action, ['authenticate', 'processRegister', 'logout'], true)) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'  => 'error',
            'title'   => 'Error del servidor',
            'message' => 'Ocurrio un error interno al procesar la solicitud. Intenta nuevamente.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(500);
    die("<h1>500 - Error interno del servidor</h1><p>No se pudo completar la operacion en este momento.</p>");
}