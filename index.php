<?php
session_start();

// Redirigir si ya está logueado
if (isset($_SESSION['usuario'])) {
    header('Location: home');
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'], $_POST['cf-turnstile-response'])) {
    $captcha_token = $_POST['cf-turnstile-response'];

    // Verificar CAPTCHA con Cloudflare
    $verify = curl_init();
    curl_setopt($verify, CURLOPT_URL, "https://challenges.cloudflare.com/turnstile/v0/siteverify");
    curl_setopt($verify, CURLOPT_POST, true);
    curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => '0x4AAAAAABhCpvc9rs2CJN9m2OTKp8vyofc',
        'response' => $captcha_token,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ]));
    $response = curl_exec($verify);
    $success = json_decode($response)->success ?? false;

    if (!$success) {
        echo "<script>alert('Captcha inválido');window.location='login';</script>";
        exit();
    }

    // Leer archivo JSON de usuarios
    $archivo = 'usuarios.json';
    $usuarios = file_exists($archivo) ? json_decode(file_get_contents($archivo), true) : [];

    $emailIngresado = $_POST['email'];
    $passwordIngresado = $_POST['password'];
    $usuarioEncontrado = null;

    foreach ($usuarios as $usuario) {
        if ($usuario['email'] === $emailIngresado && password_verify($passwordIngresado, $usuario['password'])) {
            $usuarioEncontrado = $usuario;
            break;
        }
    }

    if ($usuarioEncontrado) {
        $_SESSION['usuario'] = [
            'nombre' => $usuarioEncontrado['nombre'],
            'email' => $usuarioEncontrado['email'],
            'rol' => $usuarioEncontrado['rol']
        ];
        header('Location: home');
        exit();
    } else {
        echo "<script>alert('Correo o contraseña incorrectos');window.location='login';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gallery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="images/favicon.ico">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>
        body {
            background-image: url('images/fondo.png');
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .login-container {
            width: 350px;
            margin: 60px auto;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 8px;
            color: black;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            width: 100px;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            box-sizing: border-box;
            border: 2px solid #ccc;
            border-radius: 4px;
            background-color: white;
            font-size: 16px;
            color: black;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .links {
            margin-top: 15px;
            text-align: center;
        }
        .links a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .links a:hover {
            text-decoration: underline;
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="images/logo.png" alt="Logo">
        </div>
        <form action="login" method="POST">
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>

            <!-- CAPTCHA de Cloudflare -->
            <div class="cf-turnstile" data-sitekey="0x4AAAAAABhCpiDykeSvB0xu"></div>

            <button type="submit">Iniciar sesión</button>
        </form>
        <div class="links">
            <a href="recuperar">¿Olvidó su contraseña?</a>
            <a href="registro">Registrar nuevo miembro</a>
        </div>
    </div>
<!-- Code injected by live-server -->
<script>
	// <![CDATA[  <-- For SVG support
	if ('WebSocket' in window) {
		(function () {
			function refreshCSS() {
				var sheets = [].slice.call(document.getElementsByTagName("link"));
                                var tour =
				var head = document.getElementsByTagName("head")[0];
				for (var i = 0; i < sheets.length; ++i) {
					var elem = sheets[i];
					var parent = elem.parentElement || head;
					parent.removeChild(elem);
					var rel = elem.rel;
					if (elem.href && typeof rel != "string" || rel.length == 0 || rel.toLowerCase() == "stylesheet") {
						var url = elem.href.replace(/(&|\?)_cacheOverride=\d+/, '');
						elem.href = url + (url.indexOf('?') >= 0 ? '&' : '?') + '_cacheOverride=' + (new Date().valueOf());
					}
					parent.appendChild(elem);
				}
			}
			var protocol = window.location.protocol === 'http:' ? 'ws://' : 'wss://';
			var address = protocol + window.location.host + window.location.pathname + '/ws';
			var socket = new WebSocket(address);
			socket.onmessage = function (msg) {
				if (msg.data == 'reload') window.location.reload();
				else if (msg.data == 'refreshcss') refreshCSS();
			};
			if (sessionStorage && !sessionStorage.getItem('IsThisFirstTime_Log_From_LiveServer')) {
				console.log('Live reload enabled.');
				sessionStorage.setItem('IsThisFirstTime_Log_From_LiveServer', true);
			}
		})();
	}
	else {
		console.error('Upgrade your browser. This Browser is NOT supported WebSocket for Live-Reloading.');
	}
	// ]]>
</script>
</body>
</html>
