<?php
// Mostrar errores en desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir PHPMailer manualmente
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_correo'], $_POST['cf-turnstile-response'])) {
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
        echo "<script>alert('Captcha inválido. Inténtelo de nuevo.');window.location='recuperar';</script>";
        exit();
    }

    $entrada = trim($_POST['usuario_correo']);
    $usuarios = json_decode(file_get_contents('usuarios.json'), true);
    $usuarioEncontrado = null;

    foreach ($usuarios as $usuario) {
        if ($usuario['nombre'] === $entrada || $usuario['email'] === $entrada) {
            $usuarioEncontrado = $usuario;
            break;
        }
    }

    if ($usuarioEncontrado) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'genblhost@gmail.com';
            $mail->Password = 'mcvx qqyf qpua oxgr';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('genblhost@gmail.com', 'Gallery Support');
            $mail->addAddress($usuarioEncontrado['email'], $usuarioEncontrado['nombre']);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperacion de datos';
            $mail->Body = "
                Hola <b>{$usuarioEncontrado['nombre']}</b>,<br><br>
                Has solicitado la recuperación de tus datos:<br><br>
                <strong>Usuario:</strong> {$usuarioEncontrado['nombre']}<br>
                <strong>Correo:</strong> {$usuarioEncontrado['email']}<br>
                <strong>Contraseña:</strong> {$usuarioEncontrado['password']}<br><br>
                Por favor, guarda esta información en un lugar seguro.<br><br>
                Saludos,<br>
                <em>Equipo Gallery</em>
            ";

            $mail->send();
            echo "<script>alert('Éxito, ya sus datos fueron enviados por su correo. Revíselo.');window.location='login';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Error al enviar correo: " . $mail->ErrorInfo . "');</script>";
        }
    } else {
        echo "<script>alert('El usuario o correo no existe en el sistema');</script>";
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
        h2 {
            text-align: center;
            color: red;
            font-size: 28px;
            margin-bottom: 20px;
        }
        input[type="text"] {
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
        <h2>Recuperación de datos</h2>
        <form method="POST">
            <input type="text" name="usuario_correo" placeholder="Nombre de usuario o Correo" required>

            <!-- CAPTCHA -->
            <div class="cf-turnstile" data-sitekey="0x4AAAAAABhCpiDykeSvB0xu"></div>

            <button type="submit">Recuperar</button>
        </form>
        <div class="links">
            <a href="login">Volver al inicio</a>
        </div>
    </div>
</body>
</html>
