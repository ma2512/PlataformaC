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
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | BridgeUp</title>
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
    </style>
</head>

<body>

    <div class="dashboard">

        <aside class="sidebar">
            <h2 class="logo">BridgeUp</h2>
            <ul>
                <li class="active">🏠 <a href="Dashboard.php">Inicio</a></li>
                <li>📚 <a href="mis_cursos.php">Mis Cursos</a></li>
                <li>📈 <a href="progreso.php">Progreso</a></li>
                <li>🎓 <a href="certificados.php">Certificados</a></li>
                <li>⚙️ <a href="configuracion.php">Configuración</a></li>
                <li style="margin-top: 50px; opacity: 0.7;"> <a href="../../Backend/logout.php">Cerrar Sesión</a></li>
            </ul>
        </aside>

        <main class="main-content">

            <header class="topbar">
                <div>
                    <h1>¡Hola de nuevo, <?php echo $user_name; ?>! 👋</h1>
                    <p style="font-size: 14px; opacity: 0.8;">Es un buen día para aprender algo nuevo.</p>
                </div>
                <div class="profile">
                    <strong>👤 <?php echo $user_name; ?></strong>
                </div>
            </header>

            <section class="stats">
                <div class="stat-card">
                    <h3> Cursos activos</h3>
                    <p>3</p>
                </div>

                <div class="stat-card">
                    <h3> Lecciones</h3>
                    <p>24</p>
                </div>

                <div class="stat-card">
                    <h3> Tiempo total</h3>
                    <p>12h 45m</p>
                </div>

                <div class="stat-card" style="border-left: 4px solid #7b2cbf;">
                    <h3> Racha</h3>
                    <p>5 Días</p>
                </div>
            </section>

            <section class="my-courses">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Continuar aprendiendo</h2>
                    <a href="../HTML/Cursos.html" style="font-size: 14px; color: #7b2cbf; text-decoration: none; font-weight: 600;">Ver catálogo completo →</a>
                </div>

                <div class="course-container">

                    <div class="course-card">
                        <h3>🇬🇧 Inglés Profesional</h3>
                        <p>Módulo 4: Presentaciones</p>
                        <div class="progress-bar">
                            <div class="progress" style="width:60%"></div>
                        </div>
                        <p style="font-size: 12px; margin-bottom: 10px;">60% completado</p>
                        <button style="background: #6c757d; cursor: not-allowed;" disabled>Continuar lección</button>
                    </div>

                    <div class="course-card">
                        <h3>🇫🇷 Francés Básico</h3>
                        <p>Módulo 2: Gastronomía</p>
                        <div class="progress-bar">
                            <div class="progress" style="width:40%"></div>
                        </div>
                        <p style="font-size: 12px; margin-bottom: 10px;">40% completado</p>
                        <button style="background: #6c757d; cursor: not-allowed;" disabled>Continuar lección</button>
                    </div>

                    <div class="course-card">
                        <h3>🇩🇪 Alemán Conversacional</h3>
                        <p>Módulo 1: Saludos</p>
                        <div class="progress-bar">
                            <div class="progress" style="width:25%"></div>
                        </div>
                        <p style="font-size: 12px; margin-bottom: 10px;">25% completado</p>
                        <button style="background: #6c757d; cursor: not-allowed;" disabled>Continuar lección</button>
                    </div>

                </div>
            </section>

            <section style="margin-top: 40px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 10px;">📢 Próximas Clases en Vivo</h3>
                <p style="font-size: 14px; color: #555;">
                    • <strong>Taller de Pronunciación:</strong> Mañana a las 10:00 AM <br>
                    • <strong>Club de Conversación:</strong> Jueves a las 5:00 PM
                </p>
            </section>

        </main>
    </div>

</body>
</html>
