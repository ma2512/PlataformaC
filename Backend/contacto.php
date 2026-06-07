<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

    if (empty($nombre) || empty($email) || empty($mensaje)) {
        header("Location: ../Frontend/HTML/Contacto.html?error=campos_vacios");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../Frontend/HTML/Contacto.html?error=email_invalido");
        exit();
    }

    $stmt = $conexion->prepare("INSERT INTO mensajes (nombre, email, mensaje) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $email, $mensaje);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: ../Frontend/HTML/Contacto.html?envio=exito");
        exit();
    } else {
        $stmt->close();
        header("Location: ../Frontend/HTML/Contacto.html?error=error_envio");
        exit();
    }
} else {
    header("Location: ../Frontend/HTML/Contacto.html");
    exit();
}
?>
