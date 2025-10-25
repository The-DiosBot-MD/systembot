<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login");
    exit();
}

$mensajeStatus = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $categoria = trim($_POST['categoria']);
    $etiquetas = array_map('trim', explode(',', $_POST['etiquetas']));
    $tipo = $_POST['tipo'];

    if (!is_dir('solicitudes')) {
        mkdir('solicitudes', 0777, true);
    }

    $archivoSubido = '';
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $nombreOriginal = basename($_FILES['archivo']['name']);
        $rutaDestino = 'solicitudes/' . time() . '_' . $nombreOriginal;
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaDestino)) {
            $archivoSubido = $rutaDestino;
        }
    }

    if ($archivoSubido) {
        $rutaJson = 'solicitudes/solicitudes.json';
        $solicitudes = file_exists($rutaJson) ? json_decode(file_get_contents($rutaJson), true) : [];

        $solicitudes[] = [
            'usuario' => $_SESSION['usuario']['nombre'],
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'categoria' => $categoria,
            'tags' => $etiquetas,
            'tipo' => $tipo,
            'archivo' => $archivoSubido,
            'estado' => 'pendiente'
        ];

        file_put_contents($rutaJson, json_encode($solicitudes, JSON_PRETTY_PRINT));
        $mensajeStatus = "<span style='color:green;'>Tu solicitud ha sido enviada correctamente y está pendiente de aprobación.</span>";
    } else {
        $mensajeStatus = "<span style='color:red;'>Error al subir el archivo. Intenta nuevamente.</span>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir contenido - Gallery</title>
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
        .form-container {
            width: 500px;
            margin: 60px auto;
            background-color: rgba(0, 0, 0, 0.85);
            padding: 30px;
            border-radius: 10px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input, textarea, select {
            width: 100%;
            margin-bottom: 12px;
            padding: 10px;
            border: none;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            color: #000;
        }
        textarea {
            min-height: 100px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .mensaje-estado {
            text-align: center;
            margin-top: 15px;
        }
        .volver {
            margin-top: 20px;
            display: block;
            text-align: center;
            color: #ccc;
            text-decoration: none;
        }
        .volver:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Solicitar publicación</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="titulo" placeholder="Título" required>
            <textarea name="descripcion" placeholder="Descripción" required></textarea>
            <input type="text" name="categoria" placeholder="Categoría" required>
            <input type="text" name="etiquetas" placeholder="Etiquetas (separadas por coma)" required>
            <select name="tipo" required>
                <option value="">-- Tipo de contenido --</option>
                <option value="imagen">Imagen</option>
                <option value="video">Video</option>
                <option value="pdf">PDF</option>
            </select>
            <input type="file" name="archivo" required>
            <button type="submit">Enviar solicitud</button>
        </form>
        <div class="mensaje-estado"><?= $mensajeStatus ?></div>
        <a href="home" class="volver">← Volver al inicio</a>
    </div>
</body>
</html>
