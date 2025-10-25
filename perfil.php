<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login");
    exit();
}

$usuarioActual = $_SESSION['usuario']['nombre'];
$archivoUsuarios = 'usuarios.json';
$usuarios = json_decode(file_get_contents($archivoUsuarios), true);

// Buscar usuario actual
foreach ($usuarios as $index => $usuario) {
    if ($usuario['nombre'] === $usuarioActual) {
        $usuarioDatos = $usuario;
        $usuarioIndex = $index;
        break;
    }
}

// Guardar cambios
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoNombre = trim($_POST['nombre']);
    $nuevoCorreo = trim($_POST['email']);
    $nuevaContrasena = trim($_POST['password']);

    if ($nuevoNombre && $nuevoCorreo && $nuevaContrasena) {
        $usuarios[$usuarioIndex]['nombre'] = $nuevoNombre;
        $usuarios[$usuarioIndex]['email'] = $nuevoCorreo;
        $usuarios[$usuarioIndex]['password'] = password_hash($nuevaContrasena, PASSWORD_DEFAULT);

        $_SESSION['usuario']['nombre'] = $nuevoNombre;
        $_SESSION['usuario']['email'] = $nuevoCorreo;

        file_put_contents($archivoUsuarios, json_encode($usuarios, JSON_PRETTY_PRINT));
        $mensaje = "Datos actualizados correctamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil - Gallery</title>
    <link rel="icon" href="images/favicon.ico">
    <style>
        body {
            background-image: url('images/fondo.png');
            background-size: cover;
            font-family: Arial, sans-serif;
            color: white;
        }
        .perfil-container {
            width: 400px;
            margin: 60px auto;
            background-color: rgba(0, 0, 0, 0.85);
            padding: 30px;
            border-radius: 10px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: none;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .volver {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #0bf;
            text-decoration: none;
        }
        .volver:hover {
            text-decoration: underline;
        }
        .mensaje {
            text-align: center;
            margin-top: 10px;
            color: yellow;
        }
    </style>
</head>
<body>
    <div class="perfil-container">
        <h2>Perfil</h2>
        <form method="POST">
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuarioDatos['nombre']) ?>" placeholder="Nombre de usuario" required>
            <input type="email" name="email" value="<?= htmlspecialchars($usuarioDatos['email']) ?>" placeholder="Correo" required>
            <input type="password" name="password" placeholder="Nueva contraseña" required>
            <button type="submit">Guardar</button>
        </form>
        <?php if ($mensaje): ?>
            <div class="mensaje"><?= $mensaje ?></div>
        <?php endif; ?>
        <a href="home" class="volver">← Volver al inicio</a>
    </div>
</body>
</html>
