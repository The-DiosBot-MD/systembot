<?php
session_start();

// Validar que el usuario sea admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: login');
    exit();
}

// Cargar categorías y tags
$categorias = json_decode(file_get_contents('categorias.json'), true);
$tags = json_decode(file_get_contents('tags.json'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $categoria = $_POST['categoria'];
    $tagsSeleccionados = $_POST['tags'] ?? [];

    // Validar archivo
    if ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        echo "<script>alert('Error al subir archivo: código " . $_FILES['archivo']['error'] . "');</script>";
        exit();
    }

    $tipoArchivo = $_FILES['archivo']['type'];
    $nombreOriginal = basename($_FILES['archivo']['name']);
    $nombreArchivo = time() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $nombreOriginal);
    $rutaTemp = $_FILES['archivo']['tmp_name'];

    // Determinar carpeta según tipo de archivo
    if (strpos($tipoArchivo, 'image') === 0) {
        $destino = "uploads/imagenes/$nombreArchivo";
        $tipo = 'imagen';
    } elseif (strpos($tipoArchivo, 'video') === 0) {
        $destino = "uploads/videos/$nombreArchivo";
        $tipo = 'video';
    } elseif ($tipoArchivo === 'application/pdf') {
        $destino = "uploads/pdfs/$nombreArchivo";
        $tipo = 'pdf';
    } else {
        echo "<script>alert('Tipo de archivo no permitido');</script>";
        exit();
    }

    // Mover archivo al destino
    if (move_uploaded_file($rutaTemp, $destino)) {
        // Guardar metadatos
        $nuevoContenido = [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'categoria' => $categoria,
            'tags' => $tagsSeleccionados,
            'tipo' => $tipo,
            'archivo' => $destino,
            'fecha' => date('Y-m-d H:i:s')
        ];

        $contenido = file_exists('contenido.json') ? json_decode(file_get_contents('contenido.json'), true) : [];
        $contenido[] = $nuevoContenido;
        file_put_contents('contenido.json', json_encode($contenido, JSON_PRETTY_PRINT));

        echo "<script>alert('Contenido subido correctamente');window.location='admin';</script>";
        exit();
    } else {
        echo "<script>alert('No se pudo mover el archivo al destino');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Contenido</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            width: 400px;
            margin: 60px auto;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 10px;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background-color: #218838;
        }
        a.btn-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #0bf;
            text-decoration: none;
        }
        a.btn-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Subir Contenido</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="titulo" placeholder="Título" required>
        <textarea name="descripcion" placeholder="Descripción" rows="4" required></textarea>

        <label for="categoria">Categoría:</label>
        <select name="categoria" required>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="tags">Etiquetas:</label>
        <select name="tags[]" multiple>
            <?php foreach ($tags as $tag): ?>
                <option value="<?= htmlspecialchars($tag) ?>"><?= htmlspecialchars($tag) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="archivo">Archivo:</label>
        <input type="file" name="archivo" accept="image/*,video/*,.pdf" required>

        <button type="submit">Subir</button>
    </form>

    <a href="admin" class="btn-link">← Volver al Panel Admin</a>
    <a href="home" class="btn-link">🏠 Volver al Inicio</a>
</div>
</body>
</html>
