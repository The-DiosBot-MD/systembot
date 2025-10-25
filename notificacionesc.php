<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: /home");
    exit();
}

$notificacionesFile = 'notificaciones.json';
$notificaciones = file_exists($notificacionesFile) ? json_decode(file_get_contents($notificacionesFile), true) : [];

$modoEdicion = false;
$editarID = null;
$tituloEditar = '';
$mensajeEditar = '';

// Eliminar
if (isset($_GET['eliminar'])) {
    $idEliminar = $_GET['eliminar'];
    $notificaciones = array_filter($notificaciones, fn($n) => $n['id'] !== $idEliminar);
    file_put_contents($notificacionesFile, json_encode(array_values($notificaciones), JSON_PRETTY_PRINT));
    header("Location: notificacionesc.php?eliminado=1");
    exit();
}

// Cargar para editar
if (isset($_GET['editar'])) {
    $editarID = $_GET['editar'];
    foreach ($notificaciones as $n) {
        if ($n['id'] === $editarID) {
            $modoEdicion = true;
            $tituloEditar = $n['titulo'];
            $mensajeEditar = $n['mensaje'];
            break;
        }
    }
}

// Guardar nueva o editada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $mensaje = trim($_POST['mensaje']);
    $editando = isset($_POST['editando']) ? $_POST['editando'] : null;

    if ($titulo && $mensaje) {
        if ($editando) {
            foreach ($notificaciones as &$n) {
                if ($n['id'] === $editando) {
                    $n['titulo'] = $titulo;
                    $n['mensaje'] = $mensaje;
                    $n['fecha'] = date('Y-m-d H:i:s');
                    break;
                }
            }
            file_put_contents($notificacionesFile, json_encode($notificaciones, JSON_PRETTY_PRINT));
            header("Location: notificacionesc.php?editado=1");
            exit();
        } else {
            $nueva = [
                'id' => uniqid(),
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'autor' => $_SESSION['usuario']['nombre'],
                'fecha' => date('Y-m-d H:i:s')
            ];
            $notificaciones[] = $nueva;
            file_put_contents($notificacionesFile, json_encode($notificaciones, JSON_PRETTY_PRINT));
            header("Location: notificacionesc.php?exito=1");
            exit();
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Notificaciones</title>
  <link rel="icon" href="images/favicon.ico">
  <style>
    body {
      margin: 0;
      padding: 0;
      background: url('images/fondo.png') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      color: white;
    }
    .contenedor {
      max-width: 800px;
      margin: 40px auto;
      background-color: rgba(0, 0, 0, 0.8);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 0 20px rgba(0,0,0,0.5);
    }
    h1 {
      color: #ffc107;
      text-align: center;
    }
    form {
      background-color: #222;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 30px;
    }
    label {
      display: block;
      margin-top: 10px;
      font-weight: bold;
    }
    input[type="text"], textarea {
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 5px;
      margin-top: 5px;
      background-color: #333;
      color: white;
    }
    button {
      margin-top: 15px;
      background-color: #28a745;
      border: none;
      color: white;
      padding: 10px 20px;
      font-weight: bold;
      border-radius: 5px;
      cursor: pointer;
    }
    button:hover {
      background-color: #218838;
    }
    .mensaje {
      margin-top: 10px;
      color: #0f0;
    }
    .error {
      margin-top: 10px;
      color: #f55;
    }
    .volver {
      margin-top: 20px;
      display: inline-block;
      color: #ffc107;
      text-decoration: none;
    }
    .volver:hover {
      text-decoration: underline;
    }
    .noti-lista {
      background-color: #222;
      border-radius: 10px;
      padding: 15px;
    }
    .noti-item {
      border-bottom: 1px solid #444;
      padding: 10px 0;
    }
    .noti-item:last-child {
      border-bottom: none;
    }
    .noti-titulo {
      font-weight: bold;
      color: #ffc107;
    }
    .noti-meta {
      font-size: 12px;
      color: #aaa;
    }
    .eliminar, .editar {
      text-decoration: none;
      font-weight: bold;
      margin-left: 10px;
    }
    .eliminar {
      color: #f55;
    }
    .editar {
      color: #0cf;
    }
    .eliminar:hover, .editar:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="contenedor">
    <h1><?= $modoEdicion ? 'Editar Notificación' : 'Crear Notificación' ?></h1>

    <?php if (isset($_GET['exito'])): ?>
      <div class="mensaje">✅ Notificación creada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['editado'])): ?>
      <div class="mensaje">✏️ Notificación editada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['eliminado'])): ?>
      <div class="mensaje">🗑️ Notificación eliminada.</div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
      <div class="error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <label for="titulo">Título:</label>
      <input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($tituloEditar) ?>" required>

      <label for="mensaje">Mensaje:</label>
      <textarea name="mensaje" id="mensaje" rows="4" required><?= htmlspecialchars($mensajeEditar) ?></textarea>

      <?php if ($modoEdicion): ?>
        <input type="hidden" name="editando" value="<?= htmlspecialchars($editarID) ?>">
      <?php endif; ?>

      <button type="submit"><?= $modoEdicion ? 'Guardar Cambios' : 'Crear Notificación' ?></button>
    </form>

    <div class="noti-lista">
      <h2>📋 Notificaciones existentes</h2>
      <?php if (empty($notificaciones)): ?>
        <p>No hay notificaciones aún.</p>
      <?php else: ?>
        <?php foreach (array_reverse($notificaciones) as $n): ?>
          <div class="noti-item">
            <span class="noti-titulo"><?= htmlspecialchars($n['titulo']) ?></span>
            <a class="editar" href="?editar=<?= htmlspecialchars($n['id']) ?>">Editar</a>
            <a class="eliminar" href="?eliminar=<?= htmlspecialchars($n['id']) ?>" onclick="return confirm('¿Eliminar esta notificación?')">Eliminar</a><br>
            <div><?= htmlspecialchars($n['mensaje']) ?></div>
            <div class="noti-meta">✍️ <?= htmlspecialchars($n['autor']) ?> — 🕒 <?= htmlspecialchars($n['fecha']) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <a class="volver" href="/admin">← Volver al panel</a>
  </div>

</body>
</html>
