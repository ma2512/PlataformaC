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
    <title>Mi Progreso | BridgeUp</title>
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
        .progress-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .progress-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .progress-grid {
                grid-template-columns: 1fr;
            }
        }
        .activity-item {
            padding: 12px 15px;
            border-left: 4px solid #7b2cbf;
            background: #f8f9fa;
            border-radius: 0 8px 8px 0;
            margin-bottom: 12px;
            font-size: 14px;
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
                <li class="active">📈 <a href="progreso.php">Progreso</a></li>
                <li>🎓 <a href="certificados.php">Certificados</a></li>
                <li>⚙️ <a href="configuracion.php">Configuración</a></li>
                <li style="margin-top: 50px; opacity: 0.7;"> <a href="../../Backend/logout.php">Cerrar Sesión</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div>
                    <h1 class="tab-title">📈 Mi Progreso Académico</h1>
                    <p style="font-size: 14px; opacity: 0.8;">Revisa tus estadísticas y actividades recientes.</p>
                </div>
                <div class="profile">
                    <strong>👤 <?php echo $user_name; ?></strong>
                </div>
            </header>

            <div class="progress-grid">
                <!-- Estadísticas Detalladas -->
                <div class="progress-card">
                    <h2 style="color: #5a189a; margin-bottom: 20px; font-size: 18px;">Rendimiento del Curso</h2>
                    
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 5px;">
                            <span>Inglés Profesional</span>
                            <strong>60%</strong>
                        </div>
                        <div class="progress-bar"><div class="progress" style="width: 60%"></div></div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 5px;">
                            <span>Francés Básico</span>
                            <strong>40%</strong>
                        </div>
                        <div class="progress-bar"><div class="progress" style="width: 40%"></div></div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 5px;">
                            <span>Alemán Conversacional</span>
                            <strong>25%</strong>
                        </div>
                        <div class="progress-bar"><div class="progress" style="width: 25%"></div></div>
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <div class="progress-card">
                    <h2 style="color: #5a189a; margin-bottom: 20px; font-size: 18px;">Actividad Reciente</h2>
                    <div class="activity-item">
                        <strong>Hoy:</strong> Completaste la lección 4 de <em>Inglés Profesional</em>.
                    </div>
                    <div class="activity-item">
                        <strong>Ayer:</strong> Aprobaste el quiz de vocabulario de <em>Francés Básico</em> con 90%.
                    </div>
                    <div class="activity-item">
                        <strong>Hace 3 días:</strong> Iniciaste el curso <em>Alemán Conversacional</em>.
                    </div>
                </div>
            </div>

            <!-- Sección de Medallas / Logros -->
            <div class="progress-card" style="margin-top: 20px;">
                <h2 style="color: #5a189a; margin-bottom: 20px; font-size: 18px;">🏆 Logros Desbloqueados</h2>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="background: #f3f0fc; padding: 15px; border-radius: 8px; text-align: center; width: 120px; font-size: 12px; font-weight: bold; border: 1px solid #7b2cbf;">
                        <span style="font-size: 32px; display: block; margin-bottom: 5px;">🔥</span>
                        Racha 5 Días
                    </div>
                    <div style="background: #f3f0fc; padding: 15px; border-radius: 8px; text-align: center; width: 120px; font-size: 12px; font-weight: bold; border: 1px solid #7b2cbf;">
                        <span style="font-size: 32px; display: block; margin-bottom: 5px;">🗣️</span>
                        Primer Vocablo
                    </div>
                    <div style="background: #f3f0fc; padding: 15px; border-radius: 8px; text-align: center; width: 120px; font-size: 12px; font-weight: bold; border: 1px solid #7b2cbf;">
                        <span style="font-size: 32px; display: block; margin-bottom: 5px;">📚</span>
                        Trilingüe
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
