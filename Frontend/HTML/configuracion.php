<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: ../../Backend/admin_crud.php");
    exit();
}
$user_name = htmlspecialchars($_SESSION['user_name']);
$user_email = htmlspecialchars($_SESSION['user_email']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración | BridgeUp</title>
    <link rel="stylesheet" href="../CSS/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .sidebar li.active {
            color: white;
            font-weight: 600;
        }
        .sidebar a {
            color: #c77dff;
            text-decoration: none;
            transition: 0.3s;
        }
        .sidebar a:hover, .sidebar li.active a {
            color: white;
        }
        .tab-title {
            margin-bottom: 25px;
            color: #140842;
        }
        .config-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #dcd6f7;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #7b2cbf;
            box-shadow: 0 0 5px rgba(123,44,191,0.2);
        }
        .btn-save {
            background: #7b2cbf;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-save:hover {
            background: #5a189a;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <h2 class="logo">BridgeUp</h2>
            <ul>
                <li>🏠 <a href="Dashboard.php">Inicio</a></li>
                <li>📚 <a href="mis_cursos.php">Mis Cursos</a></li>
                <li>📈 <a href="progreso.php">Progreso</a></li>
                <li>🎓 <a href="certificados.php">Certificados</a></li>
                <li class="active">⚙️ <a href="configuracion.php">Configuración</a></li>
                <li style="margin-top: 50px; opacity: 0.7;"> <a href="../../Backend/logout.php">Cerrar Sesión</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div>
                    <h1 class="tab-title">⚙️ Configuración del Perfil</h1>
                    <p style="font-size: 14px; opacity: 0.8;">Modifica tus datos personales o actualiza tu contraseña.</p>
                </div>
                <div class="profile">
                    <strong>👤 <?php echo $user_name; ?></strong>
                </div>
            </header>

            <div class="config-card">
                <?php
                if (isset($_GET['status']) && $_GET['status'] == 'exito') {
                    echo '<div class="alert alert-success">¡Perfil actualizado correctamente!</div>';
                }
                if (isset($_GET['error'])) {
                    $err = $_GET['error'];
                    $msg = "Ocurrió un error al actualizar.";
                    if ($err == 'campos_vacios') $msg = "Por favor, completa todos los campos obligatorios.";
                    elseif ($err == 'email_invalido') $msg = "El correo electrónico ingresado no es válido.";
                    elseif ($err == 'email_duplicado') $msg = "El correo electrónico ya está en uso por otra cuenta.";
                    echo '<div class="alert alert-error">' . $msg . '</div>';
                }
                ?>
                <form action="../../Backend/actualizar_usuario.php" method="POST">
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre" value="<?php echo $user_name; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" value="<?php echo $user_email; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Nueva Contraseña (dejar vacío para conservar la actual)</label>
                        <input type="password" name="password" placeholder="Mínimo 8 caracteres">
                    </div>

                    <button type="submit" class="btn-save">Guardar Cambios</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
