<?php
require_once 'conexion.php';

header('Content-Type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$result = $conexion->query("SELECT * FROM productos ORDER BY id DESC");
$productos = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
}

echo json_encode($productos);
?>
