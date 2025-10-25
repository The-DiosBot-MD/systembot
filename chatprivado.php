<?php
session_start();

if (!isset($_SESSION['usuario']) || !isset($_GET['con'])) {
    header("Location: /login");
    exit();
}

$usuarioActual = $_SESSION['usuario']['nombre'];
$rolActual = $_SESSION['usuario']['rol'];
$destinatario = $_GET['con'];

$chatFile = 'chatprivado.json';
$chat = file_exists($chatFile) ? json_decode(file_get_contents($chatFile), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    $chat[] = [
        'emisor' => $usuarioActual,
        'receptor' => $destinatario,
        'mensaje' => $_POST['mensaje'],
        'hora' => date('Y-m-d H:i:s'),
        'rol' => $rolActual
    ];
    file_put_contents($chatFile, json_encode($chat, JSON_PRETTY_PRINT));
    header("Location: chatprivado?con=" . urlencode($destinatario));
    exit();
}

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    if (isset($chat[$id]) && ($chat[$id]['emisor'] === $usuarioActual || $rolActual === 'admin')) {
        unset($chat[$id]);
        $chat = array_values($chat);
        file_put_contents($chatFile, json_encode($chat, JSON_PRETTY_PRINT));
    }
    header("Location: chatprivado?con=" . urlencode($destinatario));
    exit();
}

if (isset($_POST['editar_id']) && isset($_POST['nuevo_mensaje'])) {
    $id = $_POST['editar_id'];
    if (isset($chat[$id]) && ($chat[$id]['emisor'] === $usuarioActual || $rolActual === 'admin')) {
        $chat[$id]['mensaje'] = $_POST['nuevo_mensaje'];
        file_put_contents($chatFile, json_encode($chat, JSON_PRETTY_PRINT));
    }
    header("Location: chatprivado?con=" . urlencode($destinatario));
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat Privado</title>
    <link rel="icon" href="images/favicon.ico">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('images/fondo.png') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }
        .container {
            max-width: 700px;
            margin: 60px auto;
            background-color: rgba(0,0,0,0.85);
            border-radius: 15px;
            padding: 20px;
            position: relative;
        }
        h2 {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .mensaje {
            background-color: #222;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            position: relative;
        }
        .emisor {
            text-align: right;
        }
        .opciones {
            position: relative;
            margin-left: 10px;
        }
        .btn-opciones {
            cursor: pointer;
            font-size: 18px;
        }
        .menu {
            display: none;
            flex-direction: column;
            position: absolute;
            right: 0;
            top: 20px;
            background: #333;
            border-radius: 5px;
            z-index: 9999;
            padding: 5px;
        }
        .menu button {
            background: none;
            border: none;
            color: white;
            padding: 5px 10px;
            cursor: pointer;
            text-align: left;
        }
        .menu button:hover {
            background-color: #555;
        }
        form textarea {
            width: 100%;
            border-radius: 15px;
            padding: 10px;
            font-size: 16px;
            resize: none;
            border: none;
        }
        form button {
            margin-top: 10px;
            width: 100%;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
        }
        .btn-volver {
            margin-top: 15px;
            display: inline-block;
            background-color: #0069d9;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
        }
        .editar-form {
            margin-top: 5px;
        }
        .verificado {
            width: 18px;
            vertical-align: middle;
            margin-left: 5px;
        }
        .mensaje-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🔒 Chat Privado con <?= htmlspecialchars($destinatario) ?></h2>

    <?php foreach ($chat as $index => $msg): ?>
        <?php if (
            ($msg['emisor'] === $usuarioActual && $msg['receptor'] === $destinatario) ||
            ($msg['emisor'] === $destinatario && $msg['receptor'] === $usuarioActual)
        ): ?>
            <div class="mensaje <?= $msg['emisor'] === $usuarioActual ? 'emisor' : '' ?>">
                <div class="mensaje-header">
                    <strong>
                        <?= htmlspecialchars($msg['emisor']) ?>
                        <?php if (isset($msg['rol']) && $msg['rol'] === 'admin'): ?>
                            <img src="images/verificado.png" alt="admin" class="verificado">
                        <?php endif; ?>
                    </strong>
                    <?php if ($msg['emisor'] === $usuarioActual || $rolActual === 'admin'): ?>
                        <div class="opciones">
                            <span class="btn-opciones" onclick="toggleMenu(this)">⋮</span>
                            <div class="menu">
                                <form method="GET" action="">
                                    <input type="hidden" name="eliminar" value="<?= $index ?>">
                                    <button type="submit">Eliminar</button>
                                </form>
                                <form method="POST" class="editar-form">
                                    <input type="hidden" name="editar_id" value="<?= $index ?>">
                                    <textarea name="nuevo_mensaje" required><?= htmlspecialchars($msg['mensaje']) ?></textarea>
                                    <button type="submit">Guardar</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <p><?= nl2br(htmlspecialchars($msg['mensaje'])) ?></p>
                <small><?= $msg['hora'] ?></small>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <form method="POST">
        <textarea name="mensaje" placeholder="Escribe tu mensaje..." required></textarea>
        <button type="submit">Enviar</button>
    </form>

    <a href="/chat" class="btn-volver">← Volver al inicio</a>
</div>

<script>
    function toggleMenu(el) {
        const menu = el.nextElementSibling;
        menu.style.display = (menu.style.display === 'flex') ? 'none' : 'flex';
    }
</script>
</body>
</html>
