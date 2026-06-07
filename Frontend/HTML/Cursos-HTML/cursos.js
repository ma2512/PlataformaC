document.addEventListener("DOMContentLoaded", () => {
    // 1. Actualizar barra de navegación según el estado de la sesión
    const loginArea = document.getElementById("login-nav-area");

    if (loginArea) {
        fetch("../../../Backend/get_session.php")
            .then(res => res.json())
            .then(data => {
                if (data.logged_in) {
                    const dashboardPath = data.is_admin ? "../../../Backend/admin_crud.php" : "../Dashboard.php";
                    loginArea.innerHTML = `
                        <a href="${dashboardPath}" style="text-decoration: none; margin-right: 10px;">
                            <button class="btn-login" style="width: auto; padding: 5px 15px;">👤 Mi Panel</button>
                        </a>
                        <a href="../../../Backend/logout.php" style="text-decoration: none;">
                            <button class="btn-registro" style="background: #e63946; border-color: #e63946; padding: 5px 15px; color: white;">Salir</button>
                        </a>
                    `;
                }
            })
            .catch(err => console.error("Error al obtener sesión:", err));
    }

    // 2. Controlar clics en los botones de suscripción/inscripción a cursos
    const subscribeButtons = document.querySelectorAll(".subscribe");
    subscribeButtons.forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            
            // Verificar sesión del usuario
            fetch("../../../Backend/get_session.php")
                .then(res => res.json())
                .then(data => {
                    if (!data.logged_in) {
                        alert("Debes iniciar sesión para inscribirte en este curso.");
                        window.location.href = "../Login.html";
                        return;
                    }

                    // Encontrar el título de la tarjeta y del curso
                    const courseCard = btn.closest(".course-card");
                    const h3Text = courseCard ? courseCard.querySelector("h3").innerText.replace(/\s+/g, ' ').trim() : 'Curso';
                    const pageTitle = document.title.split("|")[0].trim();
                    const concepto = `Inscripción: ${pageTitle} - ${h3Text}`;
                    
                    const precioCurso = "399.00"; // Precio estándar por curso
                    
                    showEnrollmentModal(h3Text, concepto, precioCurso, () => {
                        // Guardar datos en localStorage para Pago.html
                        localStorage.setItem("checkoutTotal", precioCurso);
                        localStorage.setItem("checkoutDescription", concepto);
                        
                        // Redirigir a la página de pago
                        window.location.href = "../Pago.html";
                    });
                })
                .catch(err => {
                    console.error("Error:", err);
                    alert("Error al verificar la sesión actual.");
                });
        });
    });
});

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
