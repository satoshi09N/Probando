<?php
// Router para php -S: bloquea acceso directo a la carpeta de datos
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Bloquear cualquier intento de acceder a la carpeta oculta
if (stripos($uri, '.sys_tmp_c4ch3') !== false) {
    http_response_code(404);
    // Respuesta falsa que parece un 404 normal
    echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>';
    return true;
}

// Todo lo demás se sirve normalmente
return false;
