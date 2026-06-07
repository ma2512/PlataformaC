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
                <li class="active">
                    <a href="Dashboard.php">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                        Inicio
                    </a>
                </li>
                <li>
                    <a href="mis_cursos.php">
                        <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        Mis Cursos
                    </a>
                </li>
                <li>
                    <a href="progreso.php">
                        <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        Progreso
                    </a>
                </li>
                <li>
                    <a href="certificados.php">
                        <svg viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"></path></svg>
                        Certificados
                    </a>
                </li>
                <li>
                    <a href="configuracion.php">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        Configuración
                    </a>
                </li>
                <li style="margin-top: 50px; opacity: 0.7;">
                    <a href="../../Backend/logout.php">
                        <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        Cerrar Sesión
                    </a>
                </li>
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
