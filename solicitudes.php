<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: login");
    exit();
}

$solicitudesFile = 'solicitudes/solicitudes.json';
$contenidoFile = 'contenido.json';

// Cargar solicitudes
$solicitudes = file_exists($solicitudesFile) ? json_decode(file_get_contents($solicitudesFile), true) : [];

// Procesar acciones de aprobar o rechazar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];
    $id = $_POST['id'];

    if (isset($solicitudes[$id])) {
        if ($accion === 'aprobar') {
            // Cargar publicaciones actuales
            $contenido = file_exists($contenidoFile) ? json_decode(file_get_contents($contenidoFile), true) : [];

            // Agregar solicitud al contenido
            $contenido[] = $solicitudes[$id];
            file_put_contents($contenidoFile, json_encode($contenido, JSON_PRETTY_PRINT));

            // Eliminar la solicitud aprobada
            unset($solicitudes[$id]);
        } elseif ($accion === 'rechazar') {
            unset($solicitudes[$id]);
        }

        // Guardar el archivo actualizado
        file_put_contents($solicitudesFile, json_encode($solicitudes, JSON_PRETTY_PRINT));
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitudes - Gallery</title>
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
        .container {
            max-width: 900px;
            margin: 40px auto;
            background-color: rgba(0,0,0,0.85);
            padding: 20px;
            border-radius: 10px;
        }
        h2 {
            text-align: center;
        }
        .solicitud {
            border: 1px solid #555;
            border-radius: 8px;
            margin: 15px 0;
            padding: 15px;
            background-color: #222;
        }
        .solicitud h3 {
            margin: 0 0 10px;
        }
        .solicitud p {
            margin: 5px 0;
        }
        form {
            display: inline-block;
            margin-right: 10px;
        }
        button {
            padding: 8px 14px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        .aprobar {
            background-color: #28a745;
            color: white;
        }
        .rechazar {
            background-color: #dc3545;
            color: white;
        }
        .volver {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: #ccc;
            text-decoration: none;
        }
        .volver:hover {
            text-decoration: underline;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Solicitudes de Publicación</h2>

        <?php if (empty($solicitudes)): ?>
            <p style="text-align:center;">No hay solicitudes pendientes.</p>
        <?php else: ?>
            <?php foreach ($solicitudes as $id => $item): ?>
                <div class="solicitud">
                    <h3><?= htmlspecialchars($item['titulo']) ?></h3>
                    <p><strong>Descripción:</strong> <?= htmlspecialchars($item['descripcion']) ?></p>
                    <p><strong>Categoría:</strong> <?= htmlspecialchars($item['categoria']) ?></p>
                    <p><strong>Etiquetas:</strong> <?= implode(', ', array_map('htmlspecialchars', $item['tags'])) ?></p>
                    <?php if ($item['tipo'] === 'imagen'): ?>
                        <img src="<?= htmlspecialchars($item['archivo']) ?>" style="max-width: 100%; border-radius: 6px;">
                    <?php elseif ($item['tipo'] === 'video'): ?>
                        <video controls src="<?= htmlspecialchars($item['archivo']) ?>" style="max-width: 100%;"></video>
                    <?php elseif ($item['tipo'] === 'pdf'): ?>
                        <iframe src="<?= htmlspecialchars($item['archivo']) ?>" width="100%" height="200"></iframe>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <input type="hidden" name="accion" value="aprobar">
                        <button class="aprobar" type="submit">Aprobar</button>
                    </form>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <input type="hidden" name="accion" value="rechazar">
                        <button class="rechazar" type="submit">Rechazar</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="admin" class="volver">← Volver al inicio</a>
    </div>
</body>
</html>
