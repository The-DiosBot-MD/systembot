<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['email'], $_POST['password'], $_POST['cf-turnstile-response'])) {
    $captcha_token = $_POST['cf-turnstile-response'];

    // Verificar CAPTCHA
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
        echo "<script>alert('Captcha inválido');window.location='registro';</script>";
        exit();
    }

    // Ruta al archivo JSON
    $archivo = 'usuarios.json';

    // Leer usuarios existentes o crear array vacío
    $usuarios = file_exists($archivo) ? json_decode(file_get_contents($archivo), true) : [];

    // Verificar si ya existe el email
    foreach ($usuarios as $usuario) {
        if ($usuario['email'] === $_POST['email']) {
            echo "<script>alert('Ese correo ya está registrado');window.location='registro';</script>";
            exit();
        }
    }

    // Crear nuevo usuario
    $nuevoUsuario = [
        'nombre' => $_POST['nombre'],
        'email' => $_POST['email'],
        'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
        'rol' => 'usuario'
    ];

    // Agregar usuario y guardar en el archivo
    $usuarios[] = $nuevoUsuario;
    file_put_contents($archivo, json_encode($usuarios, JSON_PRETTY_PRINT));

    echo "<script>alert('Registro exitoso.');window.location='login';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
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
        input[type="text"],
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
        <h2>Registro</h2>
        <form action="" method="POST">
            <input type="text" name="nombre" placeholder="Nombre de usuario" required>
            <input type="email" name="email" placeholder="Correo electrónico (Gmail)" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <div class="cf-turnstile" data-sitekey="0x4AAAAAABhCpiDykeSvB0xu"></div>
            <button type="submit">Registrar</button>
        </form>
        <div class="links">
            <a href="login">Volver al inicio</a>
        </div>
    </div>
</body>
</html>
