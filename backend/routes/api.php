<?php
// Rutas del API (delegar a controladores)
// Acepta URIs que contengan '/api/login' para ser tolerante a prefijos de proyecto
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// CSRF token endpoint (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($uri, '/api/csrf-token') !== false) {
  // provide a token associated to the session
  $maybe = __DIR__ . '/../src/helpers/csrf.php';
  if (file_exists($maybe))
    require_once $maybe;
  if (function_exists('csrf_get_token')) {
    $t = csrf_get_token();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['token' => $t]);
    exit;
  }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($uri, '/api/login/change-password') !== false) {
  if (!class_exists('\App\\Controllers\\LoginController')) {
    $maybe = __DIR__ . '/../src/controllers/LoginController.php';
    if (file_exists($maybe))
      require_once $maybe;
  }
  \App\Controllers\LoginController::changePassword();
  exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($uri, '/api/login/verify') !== false) {
  if (!class_exists('\App\\Controllers\\LoginController')) {
    $maybe = __DIR__ . '/../src/controllers/LoginController.php';
    if (file_exists($maybe))
      require_once $maybe;
  }
  \App\Controllers\LoginController::verify();
  exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($uri, '/api/login') !== false) {
  // Usar el controlador de login
  if (!class_exists('\App\\Controllers\\LoginController')) {
    // Si el autoload no está presente, intentar require manual
    $maybe = __DIR__ . '/../src/controllers/LoginController.php';
    if (file_exists($maybe)) {
      require_once $maybe;
    }
  }

  \App\Controllers\LoginController::login();
  exit;
}

// Ruta de depuración: devuelve lo que devuelve el procedimiento almacenado para un usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($uri, '/api/login/debug') !== false) {
  if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    http_response_code(403);
    echo json_encode(['mensaje' => 'Debug solo permitido desde localhost']);
    exit;
  }
  if (!class_exists('\App\\Controllers\\LoginController')) {
    $maybe = __DIR__ . '/../src/controllers/LoginController.php';
    if (file_exists($maybe))
      require_once $maybe;
  }
  \App\Controllers\LoginController::debug();
  exit;
}

// Puedes añadir aquí otras rutas si lo deseas.

// Delegar endpoints de dashboard a DashboardController
if (strpos($uri, '/api/dashboard/') !== false) {
  if (!class_exists('\App\\Controllers\\DashboardController')) {
    $maybe = __DIR__ . '/../src/controllers/DashboardController.php';
    if (file_exists($maybe))
      require_once $maybe;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($uri, '/api/dashboard/obras') !== false) {
    \App\Controllers\DashboardController::obras();
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($uri, '/api/dashboard/kpis') !== false) {
    \App\Controllers\DashboardController::kpis();
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($uri, '/api/dashboard/general') !== false) {
    \App\Controllers\DashboardController::general();
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($uri, '/api/dashboard/financiero') !== false) {
    \App\Controllers\DashboardController::financiero();
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($uri, '/api/dashboard/completo') !== false) {
    \App\Controllers\DashboardController::completo();
    exit;
  }
}
