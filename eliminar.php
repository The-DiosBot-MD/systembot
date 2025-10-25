<?php
session_start();

// Solo admins pueden acceder
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: login');
    exit;
}

$contenidoFile = 'contenido.json';
$contenido = file_exists($contenidoFile) ? json_decode(file_get_contents($contenidoFile), true) : [];

// Eliminar publicación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['indice'])) {
    $indice = intval($_POST['indice']);
    if (isset($contenido[$indice])) {
        // Eliminar archivo físico si existe
        $archivo = $contenido[$indice]['archivo'];
        if (file_exists($archivo)) {
            unlink($archivo);
        }

        // Eliminar del array y guardar
        array_splice($contenido, $indice, 1);
        file_put_contents($contenidoFile, json_encode($contenido, JSON_PRETTY_PRINT));

        header('Location: eliminar');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Eliminar Publicaciones</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" href="images/favicon.ico">
  <style>
    body {
      background-color: #111;
      color: white;
      font-family: Arial, sans-serif;
    }
    .container {
      max-width: 900px;
      margin: 30px auto;
      padding: 20px;
      background-color: #1e1e1e;
      border-radius: 8px;
    }
    h2 {
      text-align: center;
    }
    .publicacion {
      background-color: #2a2a2a;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 6px;
    }
    .publicacion img, .publicacion video, .publicacion iframe {
      max-width: 200px;
      max-height: 150px;
      display: block;
      margin-bottom: 10px;
    }
    form button {
      background-color: #dc3545;
      border: none;
      padding: 10px 15px;
      color: white;
      border-radius: 5px;
      cursor: pointer;
    }
    form button:hover {
      background-color: #c82333;
    }
    .back-btn {
      display: inline-block;
      margin-bottom: 20px;
      background-color: #007bff;
      color: white;
      padding: 10px 15px;
      border-radius: 5px;
      text-decoration: none;
    }
    .back-btn:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="admin" class="back-btn">← Volver al panel admin</a>
    <h2>Eliminar Publicaciones</h2>

    <?php if (empty($contenido)): ?>
      <p>No hay publicaciones disponibles.</p>
    <?php else: ?>
      <?php foreach ($contenido as $index => $item): ?>
        <div class="publicacion">
          <strong><?= htmlspecialchars($item['titulo']) ?></strong><br>
          <?= htmlspecialchars($item['descripcion']) ?><br>
          <small><?= $item['tipo'] ?> - <?= $item['categoria'] ?> - <?= implode(', ', $item['tags']) ?></small><br><br>

          <?php if ($item['tipo'] === 'imagen'): ?>
            <img src="<?= $item['archivo'] ?>" alt="Imagen">
          <?php elseif ($item['tipo'] === 'video'): ?>
            <video src="<?= $item['archivo'] ?>" controls width="200"></video>
          <?php elseif ($item['tipo'] === 'pdf'): ?>
            <iframe src="<?= $item['archivo'] ?>" width="200" height="150"></iframe>
          <?php endif; ?>

          <form method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar esta publicación?');">
            <input type="hidden" name="indice" value="<?= $index ?>">
            <button type="submit">Eliminar</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>
