<?php
session_start();

// Redirigir si no ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login");
    exit();
}

$mensajeStatus = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'], $_POST['mensaje'], $_POST['email'])) {
    $titulo = trim($_POST['titulo']);
    $mensaje = trim($_POST['mensaje']);
    $emailIngresado = trim($_POST['email']);
    $emailUsuario = $_SESSION['usuario']['email'];

    if ($emailIngresado !== $emailUsuario) {
        $mensajeStatus = "<span style='color:red;'>El correo no concuerda con el correo actual que usa.</span>";
    } else {
        // PHPMailer
        require 'phpmailer/src/PHPMailer.php';
        require 'phpmailer/src/SMTP.php';
        require 'phpmailer/src/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer();

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'genblhost@gmail.com';
            $mail->Password = 'mcvx qqyf qpua oxgr';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('genblhost@gmail.com', 'Gallery Support');

            // Obtener admins de usuarios.json
            $usuarios = json_decode(file_get_contents('usuarios.json'), true);
            foreach ($usuarios as $usuario) {
                if (isset($usuario['rol']) && $usuario['rol'] === 'admin' && !empty($usuario['email'])) {
                    $mail->addAddress($usuario['email']);
                }
            }

            $mail->isHTML(true);
            $mail->Subject = "Soporte: $titulo";
            $mail->Body = "<strong>De:</strong> $emailIngresado<br><strong>Mensaje:</strong><br>" . nl2br(htmlspecialchars($mensaje));

            // Adjuntar archivo si existe
            if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                $mail->addAttachment($_FILES['archivo']['tmp_name'], $_FILES['archivo']['name']);
            }

            $mail->send();
            $mensajeStatus = "<span style='color:green;'>Éxito, ya su mensaje fue enviado y espere a tener una respuesta de ello.</span>";
        } catch (Exception $e) {
            $mensajeStatus = "<span style='color:red;'>Error al enviar el mensaje: {$mail->ErrorInfo}</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Soporte - Gallery</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="images/favicon.ico">
    <style>
        body {
            background-image: url('images/fondo.png');
            background-size: cover;
            font-family: Arial, sans-serif;
            color: white;
            margin: 0;
        }
        .soporte-container {
            width: 400px;
            margin: 60px auto;
            background-color: rgba(0, 0, 0, 0.85);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }
        h2, p {
            text-align: center;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            background-color: #fff;
            color: #000;
        }
        textarea {
            resize: vertical;
            min-height: 120px;
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
            margin-top: 15px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .mensaje-estado {
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
        }
        .volver {
            margin-top: 20px;
            display: block;
            text-align: center;
            color: #ccc;
            text-decoration: none;
        }
        .volver:hover {
            text-decoration: underline;
            color: white;
        }
        label {
            margin-top: 10px;
            display: block;
            color: #ccc;
        }
    </style>
</head>
<body>
    <div class="soporte-container">
        <h2>Soporte</h2>
        <p>¿Necesitas alguna ayuda o tienes problemas con algo? Contáctanos para ayudarte.</p>

        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="titulo" placeholder="Título del mensaje" required>
            <textarea name="mensaje" placeholder="Escribe tu mensaje aquí" required></textarea>
            <input type="email" name="email" placeholder="Tu correo registrado" required>

            <label for="archivo">Archivo adjunto (opcional):</label>
            <input type="file" name="archivo" accept=".jpg,.png,.pdf,.mp4,.zip,.rar">

            <button type="submit">Enviar</button>
        </form>

        <div class="mensaje-estado"><?= $mensajeStatus ?></div>

        <a class="volver" href="home">← Volver al inicio</a>
    </div>
</body>
</html>
