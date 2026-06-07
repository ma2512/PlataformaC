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
    <title>Mis Cursos | BridgeUp</title>
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
        .course-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <h2 class="logo">BridgeUp</h2>
            <ul>
                <li>
                    <a href="Dashboard.php">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                        Inicio
                    </a>
                </li>
                <li class="active">
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
                    <h1 class="tab-title">📚 Mis Cursos Inscritos</h1>
                    <p style="font-size: 14px; opacity: 0.8;">Continúa tus lecciones donde las dejaste.</p>
                </div>
                <div class="profile">
                    <strong>👤 <?php echo $user_name; ?></strong>
                </div>
            </header>

            <section class="my-courses">
                <div class="course-container">
                    <div class="course-card">
                        <img src="../IMG/ingles.jpg" alt="Inglés">
                        <h3>🇬🇧 Inglés Profesional</h3>
                        <p>Módulo 4: Presentaciones</p>
                        <div class="progress-bar">
                            <div class="progress" style="width:60%"></div>
                        </div>
                        <p style="font-size: 12px; margin-bottom: 10px;">60% completado</p>
                        <button style="background: #6c757d; cursor: not-allowed;" disabled>Continuar lección</button>
                    </div>

                    <div class="course-card">
                        <img src="../IMG/frances.jpg" alt="Francés">
                        <h3>🇫🇷 Francés Básico</h3>
                        <p>Módulo 2: Gastronomía</p>
                        <div class="progress-bar">
                            <div class="progress" style="width:40%"></div>
                        </div>
                        <p style="font-size: 12px; margin-bottom: 10px;">40% completado</p>
                        <button style="background: #6c757d; cursor: not-allowed;" disabled>Continuar lección</button>
                    </div>

                    <div class="course-card">
                        <img src="../IMG/aleman.jpg" alt="Alemán">
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

            <h2 style="margin: 40px 0 20px; color: #140842;">🔍 Explorar Nuevos Idiomas</h2>
            <section class="my-courses">
                <div class="course-container">
                    <div class="course-card" style="opacity: 0.85;">
                        <img src="../IMG/Japones.png" alt="Japonés">
                        <h3>🇯🇵 Japonés Principiantes</h3>
                        <p>Aprende Hiragana y Katakana</p>
                        <button style="background: #7b2cbf;" onclick="inscribirCurso('Japonés Principiantes', '399.00')">Inscribirse (Pago Seguro)</button>
                    </div>
                    <div class="course-card" style="opacity: 0.85;">
                        <img src="../IMG/italiano.jpg" alt="Italiano">
                        <h3>🇮🇹 Italiano Intermedio</h3>
                        <p>Módulo 1: Vocabulario Gastronómico</p>
                        <button style="background: #7b2cbf;" onclick="inscribirCurso('Italiano Intermedio', '399.00')">Inscribirse (Pago Seguro)</button>
                    </div>
                    <div class="course-card" style="opacity: 0.85;">
                        <img src="../IMG/portugues.jpg" alt="Portugués">
                        <h3>🇧🇷 Portugués de Brasil</h3>
                        <p>Expresiones cotidianas avanzadas</p>
                        <button style="background: #7b2cbf;" onclick="inscribirCurso('Portugués de Brasil', '399.00')">Inscribirse (Pago Seguro)</button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        function inscribirCurso(nombreCurso, precio) {
            const concepto = `Inscripción: ${nombreCurso}`;
            showEnrollmentModal(nombreCurso, concepto, precio, () => {
                localStorage.setItem("checkoutTotal", precio);
                localStorage.setItem("checkoutDescription", concepto);
                window.location.href = "Pago.html";
            });
        }

        function injectModalStyles() {
            if (document.getElementById("bridgeup-modal-styles")) return;
            const styles = `
                .bridgeup-modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(20, 8, 66, 0.6);
                    backdrop-filter: blur(8px);
                    -webkit-backdrop-filter: blur(8px);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 10000;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                    pointer-events: none;
                }
                .bridgeup-modal-overlay.active {
                    opacity: 1;
                    pointer-events: auto;
                }
                .bridgeup-modal-card {
                    background: #ffffff;
                    border-radius: 16px;
                    width: 90%;
                    max-width: 440px;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
                    padding: 30px;
                    text-align: center;
                    transform: scale(0.85);
                    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                    border: 1px solid rgba(123, 44, 191, 0.2);
                }
                .bridgeup-modal-overlay.active .bridgeup-modal-card {
                    transform: scale(1);
                }
                .bridgeup-modal-icon {
                    font-size: 54px;
                    margin-bottom: 15px;
                    display: inline-block;
                    animation: pulseIcon 2.5s infinite;
                }
                @keyframes pulseIcon {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.08); }
                    100% { transform: scale(1); }
                }
                .bridgeup-modal-title {
                    font-size: 22px;
                    font-weight: 700;
                    color: #140842;
                    margin-bottom: 12px;
                    font-family: 'Poppins', sans-serif;
                }
                .bridgeup-modal-desc {
                    font-size: 14px;
                    color: #555555;
                    line-height: 1.6;
                    margin-bottom: 25px;
                    font-family: 'Poppins', sans-serif;
                }
                .bridgeup-modal-price-box {
                    background: #f3e8ff;
                    border-radius: 10px;
                    padding: 12px;
                    margin-bottom: 25px;
                    border: 1px dashed #7b2cbf;
                }
                .bridgeup-modal-price-label {
                    font-size: 11px;
                    color: #7b2cbf;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    margin-bottom: 2px;
                }
                .bridgeup-modal-price-value {
                    font-size: 26px;
                    font-weight: 700;
                    color: #5a189a;
                }
                .bridgeup-modal-actions {
                    display: flex;
                    gap: 15px;
                    justify-content: center;
                }
                .bridgeup-btn {
                    padding: 12px 24px;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    border: none;
                    flex: 1;
                    font-family: 'Poppins', sans-serif;
                }
                .bridgeup-btn-primary {
                    background: #7b2cbf;
                    color: white;
                }
                .bridgeup-btn-primary:hover {
                    background: #5a189a;
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(123, 44, 191, 0.4);
                }
                .bridgeup-btn-secondary {
                    background: #f1f3f5;
                    color: #495057;
                    border: 1px solid #dee2e6;
                }
                .bridgeup-btn-secondary:hover {
                    background: #e9ecef;
                    color: #212529;
                }
            `;
            const styleSheet = document.createElement("style");
            styleSheet.id = "bridgeup-modal-styles";
            styleSheet.innerText = styles;
            document.head.appendChild(styleSheet);
        }

        function showEnrollmentModal(courseName, concepto, price, callback) {
            injectModalStyles();
            
            // Eliminar modal anterior si existe
            const existing = document.getElementById("bridgeup-enrollment-modal");
            if (existing) existing.remove();

            const modalHtml = `
                <div id="bridgeup-enrollment-modal" class="bridgeup-modal-overlay">
                    <div class="bridgeup-modal-card">
                        <span class="bridgeup-modal-icon">🎓</span>
                        <h3 class="bridgeup-modal-title">Confirmar Inscripción</h3>
                        <p class="bridgeup-modal-desc">
                            Estás a un paso de iniciar el curso <strong>"${courseName}"</strong>. Para inscribirte, debes realizar el pago correspondiente.
                        </p>
                        <div class="bridgeup-modal-price-box">
                            <div class="bridgeup-modal-price-label">Monto a pagar</div>
                            <div class="bridgeup-modal-price-value">$${price} MXN</div>
                        </div>
                        <div class="bridgeup-modal-actions">
                            <button class="bridgeup-btn bridgeup-btn-secondary" id="bridgeup-modal-btn-cancel">Cancelar</button>
                            <button class="bridgeup-btn bridgeup-btn-primary" id="bridgeup-modal-btn-confirm">Proceder al Pago</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML("beforeend", modalHtml);
            
            const modal = document.getElementById("bridgeup-enrollment-modal");
            // Activar animación
            setTimeout(() => modal.classList.add("active"), 10);

            const close = () => {
                modal.classList.remove("active");
                setTimeout(() => modal.remove(), 300);
            };

            document.getElementById("bridgeup-modal-btn-cancel").addEventListener("click", close);
            
            document.getElementById("bridgeup-modal-btn-confirm").addEventListener("click", () => {
                close();
                callback();
            });

            // Cerrar al hacer clic fuera
            modal.addEventListener("click", (e) => {
                if (e.target === modal) close();
            });
        }
    </script>
</body>
</html>
