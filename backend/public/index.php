<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// Composer autoload if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
  require __DIR__ . '/../vendor/autoload.php';
}

// Load routes file if present
if (file_exists(__DIR__ . '/../routes/api.php')) {
  require __DIR__ . '/../routes/api.php';
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Simple router: /api/hello
if (strpos($uri, '/api/hello') !== false) {
  echo json_encode(['message' => 'Hola desde the backend']);
  exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
