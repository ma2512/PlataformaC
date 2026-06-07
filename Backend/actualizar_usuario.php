<?php
require_once 'conexion.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Frontend/HTML/Login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($nombre) || empty($email)) {
        header("Location: ../Frontend/HTML/configuracion.php?error=campos_vacios");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../Frontend/HTML/configuracion.php?error=email_invalido");
        exit();
    }

    // Verificar si el correo ya lo tiene otro usuario
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: ../Frontend/HTML/configuracion.php?error=email_duplicado");
        exit();
    }
    $stmt->close();

    // Si se especificó contraseña, actualizarla también
    if (!empty($password)) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nombre, $email, $password_hashed, $user_id);
    } else {
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nombre, $email, $user_id);
    }

    if ($stmt->execute()) {
        // Actualizar variables de sesión
        $_SESSION['user_name'] = $nombre;
        $_SESSION['user_email'] = $email;
        $stmt->close();
        header("Location: ../Frontend/HTML/configuracion.php?status=exito");
        exit();
    } else {
        $stmt->close();
        header("Location: ../Frontend/HTML/configuracion.php?error=error_update");
        exit();
    }
} else {
    header("Location: ../Frontend/HTML/configuracion.php");
    exit();
}
?>
