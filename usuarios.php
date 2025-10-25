<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login");
    exit();
}

$archivoUsuarios = 'usuarios.json';
$usuarios = file_exists($archivoUsuarios) ? json_decode(file_get_contents($archivoUsuarios), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $indice = $_POST['indice'] ?? null;

    if (is_numeric($indice) && isset($usuarios[$indice])) {
        switch ($accion) {
            case 'nombre':
                $usuarios[$indice]['nombre'] = $_POST['valor'];
                break;
            case 'correo':
                $usuarios[$indice]['correo'] = $_POST['valor'];
                break;
            case 'contrasena':
                $usuarios[$indice]['contrasena'] = $_POST['valor'];
                break;
            case 'rol':
                $usuarios[$indice]['rol'] = $_POST['valor'];
                break;
        }
        file_put_contents($archivoUsuarios, json_encode($usuarios, JSON_PRETTY_PRINT));
        header("Location: usuarios");
        exit();
    }
}

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $indice = $_GET['eliminar'];
    if (isset($usuarios[$indice]) && $usuarios[$indice]['nombre'] !== $_SESSION['usuario']['nombre']) {
        unset($usuarios[$indice]);
        $usuarios = array_values($usuarios);
        file_put_contents($archivoUsuarios, json_encode($usuarios, JSON_PRETTY_PRINT));
    }
    header("Location: usuarios");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="icon" href="images/favicon.ico">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('images/fondo.png') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }
        .container {
            max-width: 1000px;
            margin: 60px auto;
            background-color: rgba(0,0,0,0.8);
            border-radius: 15px;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            color: #ccc;
        }
        td input, td select {
            width: 90%;
            padding: 5px;
            border-radius: 5px;
            border: none;
        }
        td form {
            display: inline;
        }
        button {
            padding: 5px 10px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .eliminar {
            background-color: red;
        }
        .volver {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
        .volver a {
            background-color: #0069d9;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>👤 Gestión de Usuarios</h2>
    <table border="1">
        <tr>
            <th>Nombre (Actual)</th>
            <th>Nuevo Nombre</th>
            <th>Correo (Actual)</th>
            <th>Nuevo Correo</th>
            <th>Contraseña (Actual)</th>
            <th>Nueva Contraseña</th>
            <th>Rol (Actual)</th>
            <th>Nuevo Rol</th>
            <th>Acción</th>
        </tr>
        <?php foreach ($usuarios as $i => $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['nombre']) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="indice" value="<?= $i ?>">
                        <input type="hidden" name="accion" value="nombre">
                        <input type="text" name="valor" placeholder="Nuevo nombre">
                        <button type="submit">Guardar</button>
                    </form>
                </td>

                <td><?= htmlspecialchars($user['correo']) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="indice" value="<?= $i ?>">
                        <input type="hidden" name="accion" value="correo">
                        <input type="email" name="valor" placeholder="Nuevo correo">
                        <button type="submit">Guardar</button>
                    </form>
                </td>

                <td><?= htmlspecialchars($user['contrasena']) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="indice" value="<?= $i ?>">
                        <input type="hidden" name="accion" value="contrasena">
                        <input type="text" name="valor" placeholder="Nueva contraseña">
                        <button type="submit">Guardar</button>
                    </form>
                </td>

                <td><?= htmlspecialchars($user['rol']) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="indice" value="<?= $i ?>">
                        <input type="hidden" name="accion" value="rol">
                        <select name="valor">
                            <option value="usuario" <?= $user['rol'] === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                            <option value="admin" <?= $user['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <button type="submit">Guardar</button>
                    </form>
                </td>

                <td>
                    <?php if ($user['nombre'] === $_SESSION['usuario']['nombre']): ?>
                        (Tú)
                    <?php else: ?>
                        <a href="?eliminar=<?= $i ?>"><button class="eliminar">Eliminar</button></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <div class="volver">
        <a href="admin">← Volver al inicio</a>
    </div>
</div>
</body>
</html>
