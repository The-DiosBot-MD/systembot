<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: /login");
    exit();
}

// Archivos JSON
$contenidoFile   = 'contenido.json';
$comentariosFile = 'comentarios.json';

// Cargar datos
$contenido   = file_exists($contenidoFile) ? json_decode(file_get_contents($contenidoFile), true) : [];
$comentarios = file_exists($comentariosFile) ? json_decode(file_get_contents($comentariosFile), true) : [];

// Usuario actual
$user = $_SESSION['usuario']['nombre'];
$role = $_SESSION['usuario']['rol'];

// ID de publicación
$id = isset($_GET['id']) ? intval($_GET['id']) : -1;
if (!isset($contenido[$id])) {
    echo "<h2>Publicación no encontrada.</h2>";
    exit();
}

$item = $contenido[$id];

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear comentario
    if (!empty($_POST['comentario'])) {
        $nuevo = [
            'id_publicacion' => $id,
            'usuario'        => $user,
            'fecha'          => date('Y-m-d H:i'),
            'mensaje'        => trim($_POST['comentario'])
        ];
        $comentarios[] = $nuevo;
        file_put_contents($comentariosFile, json_encode($comentarios, JSON_PRETTY_PRINT));
        header("Location: /publicacion?id=$id");
        exit();
    }
    // Eliminar comentario
    if (isset($_POST['delete_idx'])) {
        $idx = intval($_POST['delete_idx']);
        if (isset($comentarios[$idx]) && ($comentarios[$idx]['usuario'] === $user || $role === 'admin')) {
            array_splice($comentarios, $idx, 1);
            file_put_contents($comentariosFile, json_encode($comentarios, JSON_PRETTY_PRINT));
        }
        header("Location: /publicacion?id=$id");
        exit();
    }
    // Editar comentario
    if (isset($_POST['edit_idx']) && isset($_POST['edit_text'])) {
        $idx  = intval($_POST['edit_idx']);
        $text = trim($_POST['edit_text']);
        if (isset($comentarios[$idx]) && ($comentarios[$idx]['usuario'] === $user || $role === 'admin')) {
            $comentarios[$idx]['mensaje'] = $text;
            file_put_contents($comentariosFile, json_encode($comentarios, JSON_PRETTY_PRINT));
        }
        header("Location: /publicacion?id=$id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($item['titulo']) ?> – Gallery</title>
  <link rel="stylesheet" href="/css/style.css">
  <link rel="icon" href="/images/favicon.ico">
  <style>
    body { background-color: #111; color: #fff; font-family: Arial, sans-serif; margin: 0; padding: 20px; }
    .container { max-width: 800px; margin: auto; background: #1e1e1e; padding: 20px; border-radius: 8px; }
    h1 { text-align: center; }
    .media { text-align: center; margin: 20px 0; }
    .media img, .media video, .media iframe { max-width: 100%; border-radius: 6px; }
    .tags { text-align: center; color: #ccc; margin: 10px 0; font-size: 14px; }
    .acciones { display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; margin-bottom: 20px; }
    .acciones a, .acciones button {
      flex: 0 0 140px;
      text-align: center;
      padding: 12px 0;
      background: #28a745;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.2s;
    }
    .acciones a:hover, .acciones button:hover { background: #218838; }
    .comentarios { margin-top: 30px; }
    .comentarios h2 { text-align: center; margin-bottom: 10px; }
    form textarea { width: 100%; padding: 10px; border-radius: 6px; border: none; resize: vertical; }
    form button {
      margin-top: 10px;
      background: #28a745;
      color: #fff;
      padding: 10px 15px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    form button:hover { background: #218838; }
    .comentario { background: #2e2e2e; padding: 10px; border-radius: 6px; margin-bottom: 10px; position: relative; }
    .comentario p { margin: 5px 0; }
    .comentario strong { color: #90caf9; }
    .comentario small { color: #888; }
    .dropdown { position: absolute; top: 10px; right: 10px; }
    .dropbtn { background: none; border: none; color: #ccc; font-size: 18px; cursor: pointer; }
    .dropbtn:hover { color: #fff; }
    .dropdown-content {
      display: none;
      position: absolute;
      right: 0;
      background: #333;
      border-radius: 4px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0,0,0,0.5);
    }
    .dropdown-content form, .dropdown-content button {
      display: block;
      width: 100%;
      padding: 8px 12px;
      background: none;
      border: none;
      color: #fff;
      text-align: left;
      cursor: pointer;
    }
    .dropdown-content form button:hover, .dropdown-content button:hover { background: #444; }
    .edit-area { margin-top: 10px; }
    .edit-area textarea { width: 100%; padding: 8px; border-radius: 4px; }
    .edit-area button { margin-top: 5px; padding: 6px 10px; }
  </style>
</head>
<body>
<div class="container">
  <h1><?= htmlspecialchars($item['titulo']) ?></h1>
  <p style="text-align:center"><?= htmlspecialchars($item['descripcion']) ?></p>
  <div class="media">
    <?php if ($item['tipo'] === 'imagen'): ?>
      <img src="<?= htmlspecialchars($item['archivo']) ?>" alt="">
    <?php elseif ($item['tipo'] === 'video'): ?>
      <video controls src="<?= htmlspecialchars($item['archivo']) ?>"></video>
    <?php elseif ($item['tipo'] === 'pdf'): ?>
      <iframe src="<?= htmlspecialchars($item['archivo']) ?>" width="100%" height="400px"></iframe>
    <?php endif; ?>
  </div>
  <div class="tags">
    <strong>Categoría:</strong> <?= htmlspecialchars($item['categoria']) ?><br>
    <strong>Etiquetas:</strong> <?= implode(', ', array_map('htmlspecialchars', $item['tags'])) ?>
  </div>
  <div class="acciones">
    <a href="<?= htmlspecialchars($item['archivo']) ?>" download>📥 Descargar</a>
    <button onclick="copiarURL()">🔗 Compartir</button>
    <a href="/home">← Volver al inicio</a>
  </div>
  <div class="comentarios">
    <h2>Comentarios</h2>
    <form method="POST">
      <textarea name="comentario" placeholder="Escribe un comentario..." required></textarea>
      <button type="submit">Enviar</button>
    </form>
    <?php foreach ($comentarios as $idx => $c): ?>
      <?php if ($c['id_publicacion'] == $id): ?>
        <div class="comentario">
          <p><strong><?= htmlspecialchars($c['usuario']) ?></strong> <small>(<?= htmlspecialchars($c['fecha']) ?>)</small></p>
          <p><?= nl2br(htmlspecialchars($c['mensaje'])) ?></p>
          <?php if ($c['usuario'] === $user || $role === 'admin'): ?>
            <div class="dropdown">
              <button class="dropbtn" onclick="toggleMenu(<?= $idx ?>)">⋮</button>
              <div class="dropdown-content" id="menu-<?= $idx ?>">
                <button onclick="showEdit(<?= $idx ?>);return false;">Editar</button>
                <form method="POST" onsubmit="return confirm('¿Eliminar este comentario?');">
                  <input type="hidden" name="delete_idx" value="<?= $idx ?>">
                  <button type="submit">Eliminar</button>
                </form>
              </div>
            </div>
            <div class="edit-area" id="edit-<?= $idx ?>" style="display:none">
              <form method="POST">
                <input type="hidden" name="edit_idx" value="<?= $idx ?>">
                <textarea name="edit_text" required><?= htmlspecialchars($c['mensaje']) ?></textarea>
                <button type="submit">Guardar</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>
<script>
function copiarURL() {
  navigator.clipboard.writeText(window.location.href)
    .then(_=>alert('¡Enlace copiado al portapapeles!'))
    .catch(_=>alert('No se pudo copiar el enlace.'));
}
function toggleMenu(i) {
  var m = document.getElementById('menu-'+i);
  m.style.display = (m.style.display==='block'?'none':'block');
}
function showEdit(i) {
  document.getElementById('edit-'+i).style.display='block';
  toggleMenu(i);
}
// Cerrar menus al hacer clic afuera
window.addEventListener('click', function(e) {
  if (!e.target.matches('.dropbtn')) {
    document.querySelectorAll('.dropdown-content').forEach(function(m){ m.style.display='none'; });
  }
});
</script>
</body>
</html>
