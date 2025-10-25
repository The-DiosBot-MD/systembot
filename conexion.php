<?php
$host = '207.180.254.11';
$usuario = 'galleryuser';
$clave = '@hjte2463';
$base_datos = 'mi_sistema_web';

try {
    $conexion = new PDO("mysql:host=$host;dbname=$base_datos;charset=utf8mb4", $usuario, $clave);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
