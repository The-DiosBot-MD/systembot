<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: /login");
    exit();
}

$usuarioActual = $_SESSION['usuario']['nombre'];
$rolActual = $_SESSION['usuario']['rol'];

$chatFile = 'chat.json';
$chat = file_exists($chatFile) ? json_decode(file_get_contents($chatFile), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    $nuevoMensaje = [
        'usuario' => $usuarioActual,
        'rol' => $rolActual,
        'mensaje' => $_POST['mensaje'],
        'hora' => date('Y-m-d H:i:s')
    ];
    $chat[] = $nuevoMensaje;
    file_put_contents($chatFile, json_encode($chat, JSON_PRETTY_PRINT));
    header("Location: /chat");
    exit();
}

if (isset($_GET['eliminar'])) {
    unset($chat[$_GET['eliminar']]);
    $chat = array_values($chat);
    file_put_contents($chatFile, json_encode($chat, JSON_PRETTY_PRINT));
    header("Location: /chat");
    exit();
}

if (isset($_POST['editar_id']) && isset($_POST['nuevo_mensaje'])) {
    $id = $_POST['editar_id'];
    if ($chat[$id]['usuario'] === $usuarioActual || $rolActual === 'admin') {
        $chat[$id]['mensaje'] = $_POST['nuevo_mensaje'];
        file_put_contents($chatFile, json_encode($chat, JSON_PRETTY_PRINT));
    }
    header("Location: /chat");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat Global</title>
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
        .mensaje strong {
            cursor: pointer;
        }
        .opciones {
            position: absolute;
            right: 10px;
            top: 10px;
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
        .verificado {
            width: 18px;
            vertical-align: middle;
            margin-left: 5px;
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
        .menu-privado {
            margin-top: 5px;
            background: #444;
            padding: 5px;
            border-radius: 5px;
        }
        .menu-privado a {
            color: #1da1f2;
            text-decoration: none;
        }

        #btn-chats-privados {
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            cursor: pointer;
            z-index: 1000;
        }

        #lista-chats {
            position: fixed;
            bottom: 120px;
            right: 20px;
            background: rgba(0,0,0,0.9);
            border-radius: 10px;
            padding: 10px;
            display: none;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
        }

        #lista-chats a {
            color: #4da6ff;
            display: block;
            margin-bottom: 5px;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px;
            }
            #btn-chats-privados {
                bottom: 70px;
                right: 10px;
            }
            #lista-chats {
                right: 10px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>💬 Chat Global</h2>

    <?php foreach ($chat as $index => $msg): ?>
        <div class="mensaje">
            <strong onclick="togglePrivado(this)">
                <?= htmlspecialchars($msg['usuario']) ?>
                <?php if ($msg['rol'] === 'admin'): ?>
                    <img src="images/verificado.png" alt="admin" class="verificado">
                <?php endif; ?>
            </strong>
            <p><?= nl2br(htmlspecialchars($msg['mensaje'])) ?></p>
            <small><?= $msg['hora'] ?></small>

            <?php if ($msg['usuario'] === $usuarioActual || $rolActual === 'admin'): ?>
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

            <div class="menu menu-privado" style="display:none">
                <?php if ($msg['usuario'] !== $usuarioActual): ?>
                    <a href="chatprivado?con=<?= urlencode($msg['usuario']) ?>">Chatear por privado</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <form method="POST">
        <textarea name="mensaje" placeholder="Escribe tu mensaje..." required></textarea>
        <button type="submit">Enviar</button>
    </form>

    <a href="/home" class="btn-volver">← Volver al inicio</a>
</div>

<!-- Botón flotante y lista de chats privados -->
<button id="btn-chats-privados">📂 Chats Privados</button>
<div id="lista-chats">
    <?php
    $chatPrivadoFile = 'chatprivado.json';
    $privados = file_exists($chatPrivadoFile) ? json_decode(file_get_contents($chatPrivadoFile), true) : [];
    $usuariosConChat = [];

    foreach ($privados as $msg) {
        if ($msg['emisor'] === $usuarioActual) {
            $usuariosConChat[$msg['receptor']] = true;
        } elseif ($msg['receptor'] === $usuarioActual) {
            $usuariosConChat[$msg['emisor']] = true;
        }
    }

    foreach (array_keys($usuariosConChat) as $nombreUsuario) {
        echo "<a href='chatprivado?con=" . urlencode($nombreUsuario) . "'>💌 $nombreUsuario</a>";
    }
    ?>
</div>

<script>
    function toggleMenu(el) {
        const menu = el.nextElementSibling;
        menu.style.display = (menu.style.display === 'flex') ? 'none' : 'flex';
    }

    function togglePrivado(el) {
        const menu = el.parentElement.querySelector('.menu-privado');
        menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    }

    document.getElementById('btn-chats-privados').onclick = () => {
        const lista = document.getElementById('lista-chats');
        lista.style.display = (lista.style.display === 'block') ? 'none' : 'block';
    };
</script>
</body>
</html>
