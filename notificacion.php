<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: /login");
    exit();
}

$usuario = $_SESSION['usuario']['nombre'];
$rol = $_SESSION['usuario']['rol'];
$id = $_GET['id'] ?? null;

$archivoNoti = 'notificaciones.json';
$notificaciones = file_exists($archivoNoti) ? json_decode(file_get_contents($archivoNoti), true) : [];

$usuariosFile = 'usuarios.json';
$usuarios = file_exists($usuariosFile) ? json_decode(file_get_contents($usuariosFile), true) : [];

$notificacion = null;
foreach ($notificaciones as $n) {
    if ($n['id'] == $id) {
        $notificacion = $n;
        break;
    }
}

if (!$notificacion) {
    echo "Notificación no encontrada.";
    exit();
}

// Guardar respuesta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = $_POST['mensaje'] ?? '';
    $fecha = date("Y-m-d H:i");
    $archivo = '';

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
        $ruta = 'archivos/' . basename($_FILES['archivo']['name']);
        move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta);
        $archivo = $ruta;
    }

    foreach ($notificaciones as &$n) {
        if ($n['id'] == $id) {
            $n['respuestas'][] = [
                'usuario' => $usuario,
                'mensaje' => $mensaje,
                'archivo' => $archivo,
                'fecha' => $fecha
            ];
            break;
        }
    }
    file_put_contents($archivoNoti, json_encode($notificaciones, JSON_PRETTY_PRINT));
    header("Location: notificacion.php?id=$id");
    exit();
}

// Función para saber si un usuario es admin
function esAdmin($nombre, $usuarios) {
    foreach ($usuarios as $u) {
        if ($u['usuario'] === $nombre && $u['rol'] === 'admin') {
            return true;
        }
    }
    return false;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($notificacion['titulo']) ?></title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" href="images/favicon.ico">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      background: url('images/fondo.png') no-repeat center center fixed;
      background-size: cover;
      color: white;
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 20px;
    }
    .container {
      background-color: rgba(0,0,0,0.8);
      padding: 20px;
      border-radius: 10px;
      max-width: 700px;
      margin: auto;
    }
    .mensaje {
      margin-top: 15px;
      padding: 10px;
      background: #222;
      border-radius: 8px;
    }
    .mensaje .autor {
      font-weight: bold;
    }
    .mensaje .autor img {
      width: 14px;
      vertical-align: middle;
      margin-left: 5px;
    }
    form textarea {
      width: 100%;
      height: 80px;
      border-radius: 5px;
      padding: 8px;
      resize: vertical;
    }
    form input[type="file"] {
      margin-top: 10px;
    }
    form button {
      margin-top: 10px;
      background-color: #28a745;
      color: white;
      padding: 8px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    a.boton-volver {
      display: inline-block;
      margin-top: 20px;
      color: white;
      text-decoration: none;
      padding: 8px 15px;
      background: #007bff;
      border-radius: 5px;
    }

    @media (max-width: 600px) {
      .container {
        margin: 10px;
      }
    }
  </style>
</head>
<body>
<div class="container">
  <h2><?= htmlspecialchars($notificacion['titulo']) ?></h2>
  <p><?= htmlspecialchars($notificacion['contenido']) ?></p>

  <hr>
  <h3>Respuestas</h3>
  <?php foreach ($notificacion['respuestas'] as $respuesta): ?>
    <div class="mensaje">
      <div class="autor">
        <?= htmlspecialchars($respuesta['usuario']) ?>
        <?php if (esAdmin($respuesta['usuario'], $usuarios)): ?>
          <img src="verificado.png" alt="✔">
        <?php endif; ?>
      </div>
      <div><?= nl2br(htmlspecialchars($respuesta['mensaje'])) ?></div>
      <?php if (!empty($respuesta['archivo'])): ?>
        <div><a href="<?= htmlspecialchars($respuesta['archivo']) ?>" download>📎 Archivo adjunto</a></div>
      <?php endif; ?>
      <small><?= htmlspecialchars($respuesta['fecha']) ?></small>
    </div>
  <?php endforeach; ?>

  <hr>
  <h3>Responder</h3>
  <form method="post" enctype="multipart/form-data">
    <textarea name="mensaje" placeholder="Escribe tu mensaje" required></textarea>
    <input type="file" name="archivo">
    <br>
    <button type="submit">Enviar</button>
  </form>

  <a class="boton-volver" href="/home">← Volver al inicio</a>
</div>
</body>
</html>
