<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        header("Location: ../Frontend/HTML/Login.html?role=admin&error=campos_vacios");
        exit();
    }

    // Buscar el usuario por email y verificar que es_admin sea 1
    $stmt = $conexion->prepare("SELECT id, nombre, password, es_admin FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verificar si es administrador
        if ($user['es_admin'] != 1) {
            $stmt->close();
            header("Location: ../Frontend/HTML/Login.html?role=admin&error=no_admin");
            exit();
        }

        // Verificar la contraseña
        if (password_verify($password, $user['password'])) {
            // Iniciar sesión
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_email'] = $email;
            $_SESSION['is_admin'] = true; // Variable clave para autorizar CRUD

            $stmt->close();
            header("Location: admin_crud.php");
            exit();
        } else {
            $stmt->close();
            header("Location: ../Frontend/HTML/Login.html?role=admin&error=credenciales_incorrectas");
            exit();
        }
    } else {
        $stmt->close();
        header("Location: ../Frontend/HTML/Login.html?role=admin&error=credenciales_incorrectas");
        exit();
    }
} else {
    header("Location: ../Frontend/HTML/Login.html?role=admin");
    exit();
}
?>
