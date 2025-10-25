<?php
session_start();

// Solo admins pueden acceder
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: login');
    exit;
}

$contenidoFile = 'contenido.json';
$categoriasFile = 'categorias.json';
$tagsFile = 'tags.json';

$contenido = file_exists($contenidoFile) ? json_decode(file_get_contents($contenidoFile), true) : [];
$categorias = file_exists($categoriasFile) ? json_decode(file_get_contents($categoriasFile), true) : [];
$tags = file_exists($tagsFile) ? json_decode(file_get_contents($tagsFile), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['indice'])) {
    $indice = intval($_POST['indice']);
    if (isset($contenido[$indice])) {
        $contenido[$indice]['titulo'] = trim($_POST['titulo']);
        $contenido[$indice]['descripcion'] = trim($_POST['descripcion']);
        $contenido[$indice]['categoria'] = $_POST['categoria'];
        $contenido[$indice]['tags'] = $_POST['tags'] ?? [];

        file_put_contents($contenidoFile, json_encode($contenido, JSON_PRETTY_PRINT));
        echo "<script>alert('Publicación actualizada correctamente'); window.location='editar';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Publicaciones</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" href="images/favicon.ico">
  <style>
    body {
      background-image: url('images/fondo.png');
      background-size: cover;
      color: white;
      font-family: Arial, sans-serif;
    }
    .container {
      max-width: 900px;
      margin: 30px auto;
      background-color: rgba(0,0,0,0.8);
      padding: 20px;
      border-radius: 10px;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    .publicacion {
      border: 1px solid #444;
      padding: 20px;
      border-radius: 6px;
      margin-bottom: 30px;
      background-color: #1e1e1e;
    }
    input, textarea, select {
      width: 100%;
      padding: 8px;
      margin-top: 8px;
      border-radius: 5px;
      border: none;
    }
    button {
      background-color: #ffc107;
      color: black;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      margin-top: 10px;
      cursor: pointer;
    }
    button:hover {
      background-color: #e0a800;
    }
    .back-btn {
      background-color: #007bff;
      padding: 10px 20px;
      text-decoration: none;
      color: white;
      border-radius: 5px;
      display: inline-block;
      margin-bottom: 20px;
    }
    .back-btn:hover {
      background-color: #0056b3;
    }
    label {
      font-weight: bold;
      display: block;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="admin" class="back-btn">← Volver al Panel Admin</a>
    <h2>Editar Publicaciones</h2>

    <?php foreach ($contenido as $index => $item): ?>
      <div class="publicacion">
        <form method="POST">
          <input type="hidden" name="indice" value="<?= $index ?>">

          <label for="titulo_<?= $index ?>">Título:</label>
          <input type="text" name="titulo" id="titulo_<?= $index ?>" value="<?= htmlspecialchars($item['titulo']) ?>" required>

          <label for="descripcion_<?= $index ?>">Descripción:</label>
          <textarea name="descripcion" id="descripcion_<?= $index ?>" rows="3" required><?= htmlspecialchars($item['descripcion']) ?></textarea>

          <label for="categoria_<?= $index ?>">Categoría:</label>
          <select name="categoria" id="categoria_<?= $index ?>" required>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat ?>" <?= $cat === $item['categoria'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label for="tags_<?= $index ?>">Etiquetas:</label>
          <select name="tags[]" id="tags_<?= $index ?>" multiple>
            <?php foreach ($tags as $tag): ?>
              <option value="<?= $tag ?>" <?= in_array($tag, $item['tags']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($tag) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <button type="submit">Guardar Cambios</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
