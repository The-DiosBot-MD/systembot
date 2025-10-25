<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: /login');
    exit;
}

// Rutas de los archivos
$categoriasFile = 'categorias.json';
$tagsFile = 'tags.json';
$contenidoFile = 'contenido.json';

// Cargar datos existentes
$categorias = file_exists($categoriasFile) ? json_decode(file_get_contents($categoriasFile), true) : [];
$tags = file_exists($tagsFile) ? json_decode(file_get_contents($tagsFile), true) : [];
$contenidos = file_exists($contenidoFile) ? json_decode(file_get_contents($contenidoFile), true) : [];

// Guardar nuevas categorías
if (isset($_POST['nuevas_categorias'])) {
    $nuevas = array_map('trim', explode(',', $_POST['nuevas_categorias']));
    $categorias = array_unique(array_merge($categorias, $nuevas));
    file_put_contents($categoriasFile, json_encode(array_values($categorias), JSON_PRETTY_PRINT));
    header('Location: /admin');
    exit;
}

// Guardar nuevos tags
if (isset($_POST['nuevos_tags'])) {
    $nuevas = array_map('trim', explode(',', $_POST['nuevos_tags']));
    $tags = array_unique(array_merge($tags, $nuevas));
    file_put_contents($tagsFile, json_encode(array_values($tags), JSON_PRETTY_PRINT));
    header('Location: /admin');
    exit;
}

// Eliminar categoría
if (isset($_GET['eliminar_categoria'])) {
    $categorias = array_values(array_filter($categorias, fn($c) => $c !== $_GET['eliminar_categoria']));
    file_put_contents($categoriasFile, json_encode($categorias, JSON_PRETTY_PRINT));
    header('Location: /admin');
    exit;
}

// Eliminar tag
if (isset($_GET['eliminar_tag'])) {
    $tags = array_values(array_filter($tags, fn($t) => $t !== $_GET['eliminar_tag']));
    file_put_contents($tagsFile, json_encode($tags, JSON_PRETTY_PRINT));
    header('Location: /admin');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Admin - Gallery</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" href="images/favicon.ico">
  <style>
    body {
      background-image: url('images/fondo.png');
      background-size: cover;
      font-family: Arial, sans-serif;
      color: white;
    }
    .container {
      max-width: 800px;
      margin: 30px auto;
      background-color: rgba(0,0,0,0.7);
      padding: 20px;
      border-radius: 8px;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    form {
      margin-bottom: 30px;
    }
    input[type="text"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 4px;
      border: none;
      font-size: 16px;
    }
    button, .admin-button {
      background-color: #28a745;
      color: white;
      padding: 10px 20px;
      display: inline-block;
      text-decoration: none;
      text-align: center;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
    }
    button:hover, .admin-button:hover {
      background-color: #218838;
    }
    .admin-button.danger {
      background-color: #dc3545;
    }
    .admin-button.danger:hover {
      background-color: #c82333;
    }
    .admin-button.back {
      background-color: #007bff;
    }
    .admin-button.back:hover {
      background-color: #0069d9;
    }
    .lista-datos {
      background-color: #222;
      padding: 10px;
      border-radius: 5px;
    }
    .lista-datos span {
      display: inline-block;
      margin: 4px;
      background: #444;
      padding: 6px 10px;
      border-radius: 4px;
    }
    .lista-datos a {
      color: #f00;
      margin-left: 6px;
      text-decoration: none;
    }
    .acciones {
      display: flex;
      gap: 10px;
      margin-top: 20px;
      flex-wrap: wrap;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Panel de Administración</h2>

    <form method="POST">
      <h3>Agregar Categorías</h3>
      <input type="text" name="nuevas_categorias" placeholder="Separadas por coma, ej: noticia,galeria">
      <button type="submit">Guardar Categorías</button>
      <div class="lista-datos">
        <strong>Existentes:</strong><br>
        <?php foreach ($categorias as $cat): ?>
            <span><?= htmlspecialchars($cat) ?> <a href="?eliminar_categoria=<?= urlencode($cat) ?>" title="Eliminar">✖</a></span>
        <?php endforeach; ?>
      </div>
    </form>

    <form method="POST">
      <h3>Agregar Tags</h3>
      <input type="text" name="nuevos_tags" placeholder="Separadas por coma, ej: arte,anime">
      <button type="submit">Guardar Tags</button>
      <div class="lista-datos">
        <strong>Existentes:</strong><br>
        <?php foreach ($tags as $tag): ?>
            <span><?= htmlspecialchars($tag) ?> <a href="?eliminar_tag=<?= urlencode($tag) ?>" title="Eliminar">✖</a></span>
        <?php endforeach; ?>
      </div>
    </form>

    <div class="acciones">
      <a href="/subir" class="admin-button">📤 Ir a Subir Contenido</a>
      <a href="/eliminar" class="admin-button danger">🗑️ Eliminar Publicaciones</a>
      <a href="/home" class="admin-button back">🏠 Volver al Inicio</a>
      <a href="editar" class="admin-button">✏️ Editar Publicaciones</a>
      <a href="/usuarios" class="admin-button danger">👤​Editar Usuarios</a>
      <a href="/solicitudes.php" class="admin-button back">🏠 Revisión de solicitudes</a>
      <a href="/notificacionesc" class="admin-button">🔔​Manejo Notificaciones</a>
    </div>
  </div>
</body>
</html>
