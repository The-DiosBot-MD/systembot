<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: /login");
    exit();
}

$contenidoFile = 'contenido.json';
$contenido = file_exists($contenidoFile) ? json_decode(file_get_contents($contenidoFile), true) : [];

$notificacionesFile = 'notificaciones.json';
$notificaciones = file_exists($notificacionesFile) ? json_decode(file_get_contents($notificacionesFile), true) : [];

$usuariosFile = 'usuarios.json';
$usuarios = file_exists($usuariosFile) ? json_decode(file_get_contents($usuariosFile), true) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Inicio - Gallery</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" href="images/favicon.ico">
  <style>
    body {
      background-color: #111;
      font-family: Arial, sans-serif;
      color: white;
      margin: 0;
    }

    header {
      background-color: #222;
      padding: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    .welcome {
      font-size: 18px;
      margin-bottom: 10px;
    }

    .admin-button, .logout-button, .boton {
      padding: 8px 15px;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      transition: background-color 0.3s;
      margin-left: 5px;
      margin-top: 5px;
      display: inline-block;
    }

    .admin-button {
      background-color: #ff5722;
      color: white;
    }

    .logout-button {
      background-color: #e91e63;
      color: white;
    }

    .admin-button:hover {
      background-color: #e64a19;
    }

    .logout-button:hover {
      background-color: #c2185b;
    }

    .boton-subir {
      background-color: #28a745;
      color: white;
    }

    .boton-subir:hover {
      background-color: #218838;
    }

    .boton-notificaciones {
      background-color: #ffc107;
      color: black;
    }

    .boton-notificaciones:hover {
      background-color: #e0a800;
    }

    .boton-soporte {
      background-color: #17a2b8;
      color: white;
    }

    .boton-soporte:hover {
      background-color: #138496;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 20px;
      padding: 20px;
    }

    .card {
      background-color: #1a1a1a;
      padding: 10px;
      border-radius: 10px;
      overflow: hidden;
      text-align: center;
      cursor: pointer;
      transition: transform 0.2s;
    }

    .card:hover {
      transform: scale(1.02);
    }

    .card img, .card video, .card iframe {
      max-width: 100%;
      border-radius: 6px;
    }

    .card h3 {
      margin: 10px 0 5px;
    }

    .card p {
      font-size: 14px;
    }

    .tags {
      margin-top: 8px;
      font-size: 12px;
      color: #ccc;
    }

    #chat-button {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background-color: rgba(0, 123, 255, 0.8);
      color: white;
      padding: 12px 18px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: bold;
      box-shadow: 0 4px 8px rgba(0,0,0,0.3);
      z-index: 1000;
      transition: background-color 0.3s;
    }

    #chat-button:hover {
      background-color: rgba(0, 123, 255, 1);
    }

    #notificaciones-panel {
      display: none;
      position: absolute;
      top: 65px;
      right: 20px;
      background: white;
      color: black;
      border: 1px solid #ccc;
      border-radius: 5px;
      padding: 10px;
      width: 250px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
      z-index: 1000;
    }

    #notificaciones-panel ul {
      padding-left: 20px;
      margin-top: 10px;
      margin-bottom: 0;
    }

    #notificaciones-panel ul li a {
      text-decoration: none;
      color: black;
    }

    #notificaciones-panel ul li a:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        align-items: flex-start;
      }

      .welcome {
        margin-bottom: 10px;
      }

      .grid {
        grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
      }

      .card iframe {
        height: 300px;
      }

      #notificaciones-panel {
        right: 10px;
        width: 90%;
      }
    }
  </style>
</head>
<body>

<header>
  <div class="welcome">Bienvenido, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></div>
  <div style="display: flex; flex-wrap: wrap; gap: 10px;">
    <a href="/subirc" class="boton boton-subir">Subir</a>
    <a href="#" onclick="toggleNotificaciones()" class="boton boton-notificaciones">Notificaciones</a>
    <a href="/soporte" class="boton boton-soporte">Soporte</a>
    <?php if ($_SESSION['usuario']['rol'] === 'admin'): ?>
      <a href="/admin" class="admin-button">Panel Admin</a>
    <?php endif; ?>
    <a href="/perfil" class="admin-button">Perfil</a>
    <a href="/logout" class="logout-button">Cerrar sesión</a>
  </div>
</header>

<!-- Panel de notificaciones dinámico -->
<div id="notificaciones-panel">
  <strong>📢 Notificaciones</strong>
  <ul>
    <?php if (empty($notificaciones)): ?>
      <li>No hay notificaciones.</li>
    <?php else: ?>
      <?php foreach (array_reverse($notificaciones) as $n): ?>
        <?php
          $esAdmin = false;
          foreach ($usuarios as $u) {
              if ($u['usuario'] === $n['autor'] && $u['rol'] === 'admin') {
                  $esAdmin = true;
                  break;
              }
          }
        ?>
        <li>
          <a href="notificacion.php?id=<?= $n['id'] ?>">
            <?= htmlspecialchars($n['titulo']) ?>
            <?php if ($esAdmin): ?>
              <img src="verificado.png" alt="✔" style="width: 14px; vertical-align: middle; margin-left: 4px;">
            <?php endif; ?>
          </a>
        </li>
      <?php endforeach; ?>
    <?php endif; ?>
  </ul>
</div>

<main>
  <div class="grid">
    <?php foreach (array_reverse($contenido, true) as $index => $item): ?>
      <div class="card" onclick="location.href='publicacion?id=<?= $index ?>'">
        <h3><?= htmlspecialchars($item['titulo']) ?></h3>
        <p><?= htmlspecialchars($item['descripcion']) ?></p>

        <?php if ($item['tipo'] === 'imagen'): ?>
          <img src="<?= htmlspecialchars($item['archivo']) ?>" alt="<?= htmlspecialchars($item['titulo']) ?>">
        <?php elseif ($item['tipo'] === 'video'): ?>
          <video controls src="<?= htmlspecialchars($item['archivo']) ?>"></video>
        <?php elseif ($item['tipo'] === 'pdf'): ?>
          <iframe src="<?= htmlspecialchars($item['archivo']) ?>" width="100%" height="200px"></iframe>
        <?php endif; ?>

        <div class="tags">
          Categoría: <?= htmlspecialchars($item['categoria']) ?><br>
          Etiquetas: <?= implode(', ', array_map('htmlspecialchars', $item['tags'])) ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<!-- Botón flotante Chat Global -->
<a href="/chat" id="chat-button">Chat Global</a>

<script>
  function toggleNotificaciones() {
    const panel = document.getElementById("notificaciones-panel");
    panel.style.display = (panel.style.display === "none" || panel.style.display === "") ? "block" : "none";
  }

  document.addEventListener('click', function (e) {
    const panel = document.getElementById('notificaciones-panel');
    const boton = document.querySelector('.boton-notificaciones');
    if (!panel.contains(e.target) && !boton.contains(e.target)) {
      panel.style.display = 'none';
    }
  });
</script>

</body>
</html>
