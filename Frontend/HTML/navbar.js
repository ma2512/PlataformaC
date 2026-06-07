document.addEventListener("DOMContentLoaded", () => {
    const loginArea = document.getElementById("login-nav-area");
    
    if (loginArea) {
        // Consultar estado de sesión al backend con cache buster
        fetch("../../Backend/get_session.php?v=" + Date.now())
            .then(res => res.json())
            .then(data => {
                if (data.logged_in) {
                    const dashboardPath = data.is_admin ? "../../Backend/admin_crud.php" : "../HTML/Dashboard.php";
                    loginArea.innerHTML = `
                        <a href="${dashboardPath}" style="text-decoration: none; margin-right: 10px;">
                            <button class="btn-login" style="width: auto; padding: 5px 15px;">👤 Mi Panel</button>
                        </a>
                        <a href="../../Backend/logout.php" style="text-decoration: none;">
                            <button class="btn-registro" style="background: #e63946; border-color: #e63946; padding: 5px 15px; color: white;">Salir</button>
                        </a>
                    `;
                }
            })
            .catch(err => console.error("Error al obtener sesión:", err));
    }
});
