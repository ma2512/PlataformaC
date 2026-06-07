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
    <title>Mis Certificados | BridgeUp</title>
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
        .cert-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-left: 5px solid #28a745;
        }
        .cert-info h3 {
            color: #140842;
            margin-bottom: 5px;
        }
        .cert-info p {
            font-size: 14px;
            color: #6c757d;
        }
        .btn-download {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-download:hover {
            background: #218838;
        }
        
        /* Modal simple de Certificado */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .certificate-container {
            background: #fdfbf7;
            border: 15px solid #140842;
            padding: 50px;
            width: 700px;
            text-align: center;
            font-family: 'Georgia', serif;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
        }
        .certificate-container h2 {
            font-size: 36px;
            color: #140842;
            margin-bottom: 10px;
        }
        .certificate-container p {
            margin: 15px 0;
            font-size: 16px;
            color: #555;
        }
        .certificate-container h3 {
            font-size: 28px;
            color: #5a189a;
            border-bottom: 2px solid #ddd;
            display: inline-block;
            padding-bottom: 5px;
            margin: 20px 0;
        }
        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #e63946;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
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
                <li class="active">🎓 <a href="certificados.php">Certificados</a></li>
                <li>⚙️ <a href="configuracion.php">Configuración</a></li>
                <li style="margin-top: 50px; opacity: 0.7;"> <a href="../../Backend/logout.php">Cerrar Sesión</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <header class="topbar">
                <div>
                    <h1 class="tab-title">🎓 Mis Certificaciones Oficiales</h1>
                    <p style="font-size: 14px; opacity: 0.8;">Descarga tus diplomas de cursos finalizados.</p>
                </div>
                <div class="profile">
                    <strong>👤 <?php echo $user_name; ?></strong>
                </div>
            </header>

            <!-- Lista de Certificados -->
            <div class="cert-card">
                <div class="cert-info">
                    <h3>Diploma de Inglés Básico A1</h3>
                    <p>Completado el: 12 de Abril, 2026 • Calificación: 95/100</p>
                </div>
                <button class="btn-download" onclick="showCert('Inglés Básico A1')">Ver Certificado</button>
            </div>

            <div class="cert-card" style="border-left-color: #6c757d; opacity: 0.7;">
                <div class="cert-info">
                    <h3>Diploma de Francés Conversacional A2</h3>
                    <p>Estado: En Curso (40% completado)</p>
                </div>
                <button class="btn-download" style="background: #6c757d; cursor: not-allowed;" disabled>No Disponible</button>
            </div>

            <!-- Modal de Certificado Visual -->
            <div id="certModal" class="modal">
                <div class="certificate-container">
                    <button class="close-btn" onclick="closeCert()">X</button>
                    <p style="text-transform: uppercase; letter-spacing: 2px;">Certificado de Finalización</p>
                    <hr style="width: 60px; margin: 10px auto; border-color: #5a189a;">
                    <h2>BridgeUp Language School</h2>
                    <p>Este documento certifica que</p>
                    <h3 id="student-name"><?php echo $user_name; ?></h3>
                    <p>ha acreditado y finalizado satisfactoriamente el curso de</p>
                    <h4 id="course-title" style="font-size: 22px; color: #140842; font-style: italic; margin-bottom: 20px;">Curso de Idioma</h4>
                    <p style="font-size: 12px; color: #888;">Otorgado el 26 de Mayo de 2026. Código de Verificación: BU-8893042</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        function showCert(course) {
            document.getElementById("course-title").textContent = course;
            document.getElementById("certModal").style.display = "flex";
        }
        function closeCert() {
            document.getElementById("certModal").style.display = "none";
        }
    </script>
</body>
</html>
