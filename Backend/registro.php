<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar datos
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validar campos vacíos
    if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
        header("Location: ../Frontend/HTML/Registro.html?error=campos_vacios");
        exit();
    }

    // Validar coincidencia de contraseñas
    if ($password !== $confirm_password) {
        header("Location: ../Frontend/HTML/Registro.html?error=passwords_no_coinciden");
        exit();
    }

    // Validar correo
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../Frontend/HTML/Registro.html?error=email_invalido");
        exit();
    }

    // Verificar si el correo ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: ../Frontend/HTML/Registro.html?error=email_duplicado");
        exit();
    }
    $stmt->close();

    // Encriptar contraseña
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // Insertar en la base de datos
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $email, $password_hashed);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: ../Frontend/HTML/Login.html?registro=exito");
        exit();
    } else {
        $stmt->close();
        header("Location: ../Frontend/HTML/Registro.html?error=error_registro");
        exit();
    }
} else {
    // Si no es POST, redirigir al formulario
    header("Location: ../Frontend/HTML/Registro.html");
    exit();
}
?>
