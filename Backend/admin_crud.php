<?php
require_once 'conexion.php';

// Iniciar sesión y validar que sea administrador
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../Frontend/HTML/Login.html?role=admin&error=necesita_login");
    exit();
}

$mensaje = "";
$tipo_mensaje = ""; // 'success' o 'error'

// Manejar la eliminación
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mensaje = "Producto eliminado correctamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al eliminar el producto: " . $conexion->error;
        $tipo_mensaje = "error";
    }
    $stmt->close();
}

// Manejar la inserción / edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0.0;
    
    // Por defecto para nuevos productos, usar la del select (si tiene valor) o el default
    $imagen = !empty($_POST['imagen']) ? trim($_POST['imagen']) : '/Frontend/IMG/libro-ingles-cero.jpg';
    
    // Si es una edición y no se subió una nueva imagen, primero obtener la imagen actual de la base de datos
    if ($id > 0) {
        $stmt_check = $conexion->prepare("SELECT imagen FROM productos WHERE id = ?");
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        if ($row_check = $res_check->fetch_assoc()) {
            $imagen = $row_check['imagen'];
        }
        $stmt_check->close();
        
        // Si el usuario seleccionó una imagen del dropdown específica (que no sea vacía), y no subió archivo
        if (!empty($_POST['imagen']) && (!isset($_FILES['imagen_file']) || $_FILES['imagen_file']['error'] !== UPLOAD_ERR_OK)) {
            $imagen = trim($_POST['imagen']);
        }
    }

    // Procesar subida de imagen si existe y no tiene error
    $upload_ok = true;
    if (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imagen_file']['tmp_name'];
        $fileName = $_FILES['imagen_file']['name'];
        $fileSize = $_FILES['imagen_file']['size'];
        $fileType = $_FILES['imagen_file']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp', 'avif');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = '../Frontend/IMG/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $imagen = '/Frontend/IMG/' . $newFileName;
            } else {
                $mensaje = "Error al guardar el archivo subido en el servidor.";
                $tipo_mensaje = "error";
                $upload_ok = false;
            }
        } else {
            $mensaje = "Extensión de imagen no permitida. Formatos válidos: " . implode(', ', $allowedfileExtensions);
            $tipo_mensaje = "error";
            $upload_ok = false;
        }
    }

    if ($upload_ok) {
        if (empty($titulo) || empty($descripcion) || $precio <= 0) {
            $mensaje = "Todos los campos son obligatorios y el precio debe ser mayor a 0.";
            $tipo_mensaje = "error";
        } else {
            if ($id > 0) {
                // Actualizar producto existente
                $stmt = $conexion->prepare("UPDATE productos SET titulo = ?, descripcion = ?, precio = ?, imagen = ? WHERE id = ?");
                $stmt->bind_param("ssdsi", $titulo, $descripcion, $precio, $imagen, $id);
                if ($stmt->execute()) {
                    $mensaje = "Producto actualizado correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al actualizar el producto: " . $conexion->error;
                    $tipo_mensaje = "error";
                }
                $stmt->close();
            } else {
                // Insertar nuevo producto
                $stmt = $conexion->prepare("INSERT INTO productos (titulo, descripcion, precio, imagen) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssds", $titulo, $descripcion, $precio, $imagen);
                if ($stmt->execute()) {
                    $mensaje = "Producto agregado correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "Error al agregar el producto: " . $conexion->error;
                    $tipo_mensaje = "error";
                }
                $stmt->close();
            }
        }
    }
}

// Obtener producto para editar si se solicita
$edit_product = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $edit_product = $result->fetch_assoc();
    }
    $stmt->close();
}

// Obtener todos los productos
$productos_result = $conexion->query("SELECT * FROM productos ORDER BY id DESC");

// Obtener todas las compras, suscripciones e inscripciones
$compras_result = $conexion->query("SELECT * FROM compras ORDER BY fecha DESC");

// Obtener KPIs
$kpi_ingresos = 0.0;
$res_ingresos = $conexion->query("SELECT SUM(monto) AS total FROM compras");
if ($res_ingresos && $row = $res_ingresos->fetch_assoc()) {
    $kpi_ingresos = floatval($row['total']);
}

$kpi_suscripciones = 0;
$res_suscripciones = $conexion->query("SELECT COUNT(*) AS total FROM compras WHERE tipo = 'suscripcion'");
if ($res_suscripciones && $row = $res_suscripciones->fetch_assoc()) {
    $kpi_suscripciones = intval($row['total']);
}

$kpi_libros = 0;
$res_libros = $conexion->query("SELECT COUNT(*) AS total FROM productos");
if ($res_libros && $row = $res_libros->fetch_assoc()) {
    $kpi_libros = intval($row['total']);
}

$kpi_clientes = 0;
$res_clientes = $conexion->query("SELECT COUNT(DISTINCT email_cliente) AS total FROM compras");
if ($res_clientes && $row = $res_clientes->fetch_assoc()) {
    $kpi_clientes = intval($row['total']);
}

// Determinar tab activa al cargar la página
$default_tab = 'dashboard';
if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'delete'))) {
    $default_tab = 'libros';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador | BridgeUp</title>
    <link rel="stylesheet" href="../Frontend/CSS/admin_dashboard.css">
</head>
<body>

    <!-- Sidebar Navegación -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <a href="../Frontend/HTML/Home.html" class="logo-text">
                BridgeUp<span class="logo-dot"></span>
            </a>
        </div>
        <ul class="sidebar-menu">
            <li id="menu-dashboard" class="menu-item active">
                <a onclick="switchTab('dashboard')">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                </a>
            </li>
            <li id="menu-libros" class="menu-item">
                <a onclick="switchTab('libros')">
                    <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    Gestión de Libros
                </a>
            </li>
            <li id="menu-cursos" class="menu-item">
                <a onclick="switchTab('cursos')">
                    <svg viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"></path></svg>
                    Gestión de Cursos
                </a>
            </li>
            <li id="menu-ventas" class="menu-item">
                <a onclick="switchTab('ventas')">
                    <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2" ry="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                    Registro de Ventas
                </a>
            </li>
            <li id="menu-ajustes" class="menu-item">
                <a onclick="switchTab('ajustes')">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    Ajustes
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout">
                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- Main Workspace -->
    <main class="main-content">
        
        <!-- Header -->
        <header class="top-header">
            <div class="header-search">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" placeholder="Buscar en la plataforma..." onkeyup="globalSearch(this.value)">
            </div>
            <div class="header-actions">
                <div class="notification-bell" onclick="alert('No tienes notificaciones nuevas.')">
                    <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    <span class="badge"></span>
                </div>
                <div class="admin-profile">
                    <div class="admin-avatar">A</div>
                    <div class="admin-info">
                        <span class="admin-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Administrador'); ?></span>
                        <span class="admin-role">Admin General</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="page-body">
            
            <!-- Alertas -->
            <?php if (!empty($mensaje)): ?>
                <div class="custom-alert <?php echo $tipo_mensaje; ?>" id="system-alert">
                    <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; stroke: currentColor; stroke-width: 2; fill: none;">
                        <?php if ($tipo_mensaje === 'success'): ?>
                            <polyline points="20 6 9 17 4 12"></polyline>
                        <?php else: ?>
                            <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>
                        <?php endif; ?>
                    </svg>
                    <span><?php echo $mensaje; ?></span>
                </div>
                <script>
                    setTimeout(() => {
                        const alert = document.getElementById('system-alert');
                        if (alert) alert.style.display = 'none';
                    }, 4000);
                </script>
            <?php endif; ?>

            <!-- SECCIÓN: DASHBOARD -->
            <section id="section-dashboard" class="dashboard-section active-section">
                <div class="page-title-area">
                    <div>
                        <h2>Resumen General</h2>
                        <p>Bienvenido de vuelta, monitoriza las métricas clave de BridgeUp.</p>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-details">
                            <h3>Ingresos Totales</h3>
                            <span class="kpi-value">$<?php echo number_format($kpi_ingresos, 2); ?></span>
                        </div>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-details">
                            <h3>Suscripciones Activas</h3>
                            <span class="kpi-value"><?php echo $kpi_suscripciones; ?></span>
                        </div>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-details">
                            <h3>Libros en Catálogo</h3>
                            <span class="kpi-value"><?php echo $kpi_libros; ?></span>
                        </div>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-details">
                            <h3>Clientes Totales</h3>
                            <span class="kpi-value"><?php echo $kpi_clientes; ?></span>
                        </div>
                        <div class="kpi-icon">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M14.31 8l5.74 9.94M9.69 8h11.48M7.38 12l5.74-9.94M9.69 16L3.95 6.06M14.31 16H2.83M16.62 12l-5.74 9.94"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Resumen e Historial Rápido -->
                <div style="display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 24px; margin-bottom: 30px;">
                    
                    <!-- Últimas ventas -->
                    <div class="content-card" style="margin-bottom: 0;">
                        <div class="content-card-header">
                            <span class="content-card-title">Últimas Transacciones</span>
                            <a onclick="switchTab('ventas')" style="color: var(--primary-color); text-decoration: none; font-size: 13px; font-weight: 600; cursor: pointer;">Ver todas →</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table-modern">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Concepto</th>
                                        <th>Monto</th>
                                        <th>Tipo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $recent_sales = $conexion->query("SELECT * FROM compras ORDER BY fecha DESC LIMIT 4");
                                    if ($recent_sales && $recent_sales->num_rows > 0): 
                                        while ($sale = $recent_sales->fetch_assoc()):
                                    ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($sale['nombre_cliente']); ?></strong>
                                                    <p style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($sale['email_cliente']); ?></p>
                                                </td>
                                                <td><?php echo htmlspecialchars($sale['concepto']); ?></td>
                                                <td class="price-text">$<?php echo number_format($sale['monto'], 2); ?></td>
                                                <td>
                                                    <span class="badge-status badge-<?php echo htmlspecialchars($sale['tipo']); ?>">
                                                        <?php echo htmlspecialchars($sale['tipo']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="4" style="text-align: center; color: var(--text-muted);">Sin transacciones recientes.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Distribución por Idioma (Simulado) -->
                    <div class="content-card" style="margin-bottom: 0;">
                        <div class="content-card-header">
                            <span class="content-card-title">Estudiantes por Idioma</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 18px;">
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 6px;">
                                    <span style="font-weight: 600;">Inglés (Profesional/Cero)</span>
                                    <span style="color: var(--text-muted); font-weight: 500;">45%</span>
                                </div>
                                <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                    <div style="width: 45%; height: 100%; background: var(--primary-color);"></div>
                                </div>
                            </div>
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 6px;">
                                    <span style="font-weight: 600;">Francés e Italiano</span>
                                    <span style="color: var(--text-muted); font-weight: 500;">28%</span>
                                </div>
                                <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                    <div style="width: 28%; height: 100%; background: #0284c7;"></div>
                                </div>
                            </div>
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 6px;">
                                    <span style="font-weight: 600;">Alemán y Portugués</span>
                                    <span style="color: var(--text-muted); font-weight: 500;">17%</span>
                                </div>
                                <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                    <div style="width: 17%; height: 100%; background: #ea580c;"></div>
                                </div>
                            </div>
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 6px;">
                                    <span style="font-weight: 600;">Otros (Chino, Japonés, Ruso)</span>
                                    <span style="color: var(--text-muted); font-weight: 500;">10%</span>
                                </div>
                                <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                    <div style="width: 10%; height: 100%; background: #059669;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- SECCIÓN: GESTIÓN DE LIBROS (CRUD) -->
            <section id="section-libros" class="dashboard-section">
                <div class="page-title-area">
                    <div>
                        <h2>Catálogo de Libros</h2>
                        <p>Agrega, edita o elimina libros del inventario comercial.</p>
                    </div>
                    <button class="btn-primary" onclick="openModal()">
                        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Agregar Nuevo Libro
                    </button>
                </div>

                <div class="content-card">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Libro</th>
                                    <th>Precio (MXN)</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($productos_result && $productos_result->num_rows > 0): ?>
                                    <?php while ($prod = $productos_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <div class="product-cell">
                                                    <?php 
                                                    $img_path = htmlspecialchars($prod['imagen']);
                                                    if (strpos($img_path, '/Frontend/') === 0) {
                                                        $img_path = '../' . substr($img_path, 1);
                                                    }
                                                    ?>
                                                    <img src="<?php echo $img_path; ?>" alt="Portada" class="product-cell-img" onerror="this.onerror=null; this.src='../Frontend/IMG/libro-ingles-cero.jpg';">
                                                    <div class="product-cell-info">
                                                        <span class="product-cell-title"><?php echo htmlspecialchars($prod['titulo']); ?></span>
                                                        <span class="product-cell-desc" title="<?php echo htmlspecialchars($prod['descripcion']); ?>"><?php echo htmlspecialchars($prod['descripcion']); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="price-text">$<?php echo number_format($prod['precio'], 2); ?></td>
                                            <td>
                                                <div class="actions-cell">
                                                    <a href="admin_crud.php?action=edit&id=<?php echo $prod['id']; ?>" class="btn-action-icon edit" title="Editar Libro">
                                                        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4Z"></path></svg>
                                                    </a>
                                                    <a href="admin_crud.php?action=delete&id=<?php echo $prod['id']; ?>" class="btn-action-icon delete" title="Eliminar Libro" onclick="return confirm('¿Estás seguro de eliminar este libro del catálogo?')">
                                                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                            No hay libros registrados en la base de datos.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN: GESTIÓN DE CURSOS (SIMULADO) -->
            <section id="section-cursos" class="dashboard-section">
                <div class="page-title-area">
                    <div>
                        <h2>Cursos Activos</h2>
                        <p>Monitoreo y administración de las clases de idiomas BridgeUp.</p>
                    </div>
                    <button class="btn-primary" onclick="alert('Funcionalidad para añadir cursos se habilitará próximamente.')">
                        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Crear Nuevo Curso
                    </button>
                </div>

                <div class="content-card">
                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Curso</th>
                                    <th>Nivel</th>
                                    <th>Alumnos Activos</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong>Inglés Profesional</strong>
                                        <p style="font-size: 12px; color: var(--text-muted);">Enfoque corporativo, CV y entrevistas laborales</p>
                                    </td>
                                    <td>Intermedio (B2)</td>
                                    <td><strong>148 estudiantes</strong></td>
                                    <td><span class="badge-status badge-compra" style="background:#dcfce7; color:#166534;">Activo</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Francés Básico</strong>
                                        <p style="font-size: 12px; color: var(--text-muted);">Vocabulario inicial, saludos y comunicación diaria</p>
                                    </td>
                                    <td>Principiante (A1)</td>
                                    <td><strong>94 estudiantes</strong></td>
                                    <td><span class="badge-status badge-compra" style="background:#dcfce7; color:#166534;">Activo</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Alemán Conversacional</strong>
                                        <p style="font-size: 12px; color: var(--text-muted);">Práctica auditiva y pronunciación fluida</p>
                                    </td>
                                    <td>Intermedio (B1)</td>
                                    <td><strong>62 estudiantes</strong></td>
                                    <td><span class="badge-status badge-compra" style="background:#dcfce7; color:#166534;">Activo</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Japonés para Viajeros</strong>
                                        <p style="font-size: 12px; color: var(--text-muted);">Gramática inicial, Hiragana y frases de utilidad</p>
                                    </td>
                                    <td>Básico (N5)</td>
                                    <td><strong>39 estudiantes</strong></td>
                                    <td><span class="badge-status badge-suscripcion" style="background:#fef9c3; color:#854d0e;">En Espera</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN: REGISTRO DE VENTAS (HISTORIAL) -->
            <section id="section-ventas" class="dashboard-section">
                <div class="page-title-area">
                    <div>
                        <h2>Historial de Ventas</h2>
                        <p>Monitoreo y filtrado de todas las transacciones realizadas.</p>
                    </div>
                </div>

                <div class="content-card">
                    <div class="content-card-header">
                        <span class="content-card-title">Registro de Transacciones</span>
                        <div class="filters-row">
                            <div class="filter-input-wrapper">
                                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                <input type="text" id="searchVentaInput" placeholder="Buscar transacción..." onkeyup="filterTransactions()">
                            </div>
                            <select class="filter-select" id="filterVentaType" onchange="filterTransactions()">
                                <option value="all">Todos los Tipos</option>
                                <option value="compra">Compra Libro</option>
                                <option value="suscripcion">Suscripción Plan</option>
                                <option value="inscripcion">Inscripción Curso</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Transacción</th>
                                    <th>Cliente</th>
                                    <th>Concepto</th>
                                    <th>Monto</th>
                                    <th>Tipo</th>
                                    <th>Método</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody id="ventasTableBody">
                                <?php if ($compras_result && $compras_result->num_rows > 0): ?>
                                    <?php while ($compra = $compras_result->fetch_assoc()): ?>
                                        <tr data-type="<?php echo htmlspecialchars($compra['tipo']); ?>">
                                            <td>#<?php echo $compra['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($compra['nombre_cliente']); ?></strong>
                                                <p style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($compra['email_cliente']); ?></p>
                                            </td>
                                            <td><?php echo htmlspecialchars($compra['concepto']); ?></td>
                                            <td class="price-text"><strong>$<?php echo number_format($compra['monto'], 2); ?></strong></td>
                                            <td>
                                                <span class="badge-status badge-<?php echo htmlspecialchars($compra['tipo']); ?>">
                                                    <?php echo htmlspecialchars($compra['tipo']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span style="font-size: 11px; font-weight: 600; color: #5a189a; background: #f3e8ff; border: 1px solid #e0aaff; padding: 4px 10px; border-radius: 20px; display: inline-block;">
                                                    <?php echo htmlspecialchars($compra['metodo_pago'] ?? 'Tarjeta de Crédito'); ?>
                                                </span>
                                            </td>
                                            <td style="font-size: 12.5px; color: var(--text-muted);"><?php echo htmlspecialchars($compra['fecha']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                            No hay registros de transacciones.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN: AJUSTES -->
            <section id="section-ajustes" class="dashboard-section">
                <div class="page-title-area">
                    <div>
                        <h2>Configuración General</h2>
                        <p>Configura las credenciales de tu cuenta y opciones de BridgeUp.</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1.5fr 1.5fr; gap: 30px;">
                    <!-- Ajustes de Perfil -->
                    <div class="content-card">
                        <div class="content-card-header">
                            <span class="content-card-title">Seguridad de la Cuenta</span>
                        </div>
                        <form onsubmit="alert('Esta demo no altera las credenciales del Administrador.'); return false;">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Correo Electrónico</label>
                                    <input type="email" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? 'admin@bridgeup.com'); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Nueva Contraseña</label>
                                    <input type="password" placeholder="Mínimo 8 caracteres">
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn-primary">Guardar Cambios</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Ajustes del Sistema -->
                    <div class="content-card">
                        <div class="content-card-header">
                            <span class="content-card-title">Estado del Portal</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong style="font-size: 14.5px;">Modo de Mantenimiento</strong>
                                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Desactiva temporalmente el acceso público.</p>
                                </div>
                                <input type="checkbox" style="width: 40px; height: 20px; cursor: pointer;">
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 20px;">
                                <div>
                                    <strong style="font-size: 14.5px;">Registro de Usuarios</strong>
                                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Permite nuevos registros de alumnos.</p>
                                </div>
                                <input type="checkbox" checked style="width: 40px; height: 20px; cursor: pointer;">
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 20px;">
                                <div>
                                    <strong style="font-size: 14.5px;">Moneda del Sitio</strong>
                                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Moneda predeterminada para el checkout.</p>
                                </div>
                                <select class="filter-select">
                                    <option value="MXN">Pesos Mexicanos (MXN)</option>
                                    <option value="USD">Dólares (USD)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>

        <!-- Footer -->
        <footer class="footer-dashboard">
            <p>© 2026 BridgeUp Admin Panel</p>
            <p>Hecho con ❤️ para BridgeUp Idiomas</p>
        </footer>

    </main>

    <!-- MODAL FORMULARIO CRUD -->
    <div class="modal-overlay" id="formModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="modalTitle"><?php echo $edit_product ? "Editar Libro" : "Agregar Nuevo Libro"; ?></h3>
                <button class="modal-close" onclick="closeModal()">
                    <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="modal-body">
                <form action="admin_crud.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="prod-id" value="<?php echo $edit_product ? $edit_product['id'] : 0; ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Título del Libro</label>
                            <input type="text" name="titulo" id="prod-titulo" value="<?php echo $edit_product ? htmlspecialchars($edit_product['titulo']) : ''; ?>" placeholder="Ej. Inglés para Negocios" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="descripcion" id="prod-descripcion" rows="3" placeholder="Describe el libro..." required><?php echo $edit_product ? htmlspecialchars($edit_product['descripcion']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Precio (MXN)</label>
                            <input type="number" name="precio" id="prod-precio" step="0.01" value="<?php echo $edit_product ? htmlspecialchars($edit_product['precio']) : ''; ?>" placeholder="Ej. 350.00" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Subir Imagen de Portada</label>
                            <input type="file" name="imagen_file" accept="image/*">
                            <p class="help-text">Si subes un archivo, sobrescribirá el selector de abajo.</p>
                        </div>
                        
                        <div class="form-group">
                            <label>O Seleccionar Imagen Existente</label>
                            <select name="imagen" id="prod-imagen">
                                <?php if ($edit_product): ?>
                                    <option value="" selected>-- Mantener Imagen Actual (<?php echo basename($edit_product['imagen']); ?>) --</option>
                                <?php else: ?>
                                    <option value="" selected>-- Seleccionar Imagen --</option>
                                <?php endif; ?>
                                <?php
                                $imagenes = [
                                    '/Frontend/IMG/libro-ingles-cero.jpg' => 'Inglés desde Cero',
                                    '/Frontend/IMG/libro-frances-basico.webp' => 'Francés Básico',
                                    '/Frontend/IMG/libro-aleman-prac.webp' => 'Alemán Práctico',
                                    '/Frontend/IMG/libro-japones-princi.webp' => 'Japonés Principiantes',
                                    '/Frontend/IMG/diccionario-italiano.avif' => 'Diccionario Italiano',
                                    '/Frontend/IMG/libro-portugues-fluido.jpg' => 'Portugués Fluido',
                                    '/Frontend/IMG/libro-gramatica.jpg' => 'Gramática Avanzada',
                                    '/Frontend/IMG/libro-escriturachi.jpg' => 'Escritura China'
                                ];
                                foreach ($imagenes as $path => $label) {
                                    $selected = ($edit_product && $edit_product['imagen'] === $path) ? 'selected' : '';
                                    echo "<option value='$path' $selected>$label</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button>
                            <button type="submit" class="btn-primary">Guardar Libro</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS Control de Dashboard -->
    <script>
        // Tab switcher function
        function switchTab(tabId) {
            // Desactivar todos los items del menú
            document.querySelectorAll('.sidebar-menu li').forEach(li => {
                li.classList.remove('active');
            });
            // Ocultar todas las secciones
            document.querySelectorAll('.dashboard-section').forEach(sec => {
                sec.classList.remove('active-section');
            });

            // Activar la seleccionada
            const menuItem = document.getElementById('menu-' + tabId);
            if (menuItem) menuItem.classList.add('active');

            const section = document.getElementById('section-' + tabId);
            if (section) section.classList.add('active-section');

            // Guardar tab activa en session storage para mantener navegación en refresco (si no hay acción edit/delete activa)
            if (!window.location.search.includes('action=edit')) {
                sessionStorage.setItem('activeTab', tabId);
            }
        }

        // Modales
        function openModal() {
            document.getElementById('formModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('formModal').classList.remove('active');
            // Si estábamos editando y cancelamos, limpiamos la URL para evitar que vuelva a abrirse en refresco
            if (window.location.search.includes('action=edit')) {
                window.history.replaceState({}, document.title, window.location.pathname);
                document.getElementById('prod-id').value = 0;
                document.getElementById('prod-titulo').value = '';
                document.getElementById('prod-descripcion').value = '';
                document.getElementById('prod-precio').value = '';
                document.getElementById('modalTitle').textContent = 'Agregar Nuevo Libro';
            }
        }

        // Buscador rápido y filtros para transacciones
        function filterTransactions() {
            const searchVal = document.getElementById('searchVentaInput').value.toLowerCase().trim();
            const typeVal = document.getElementById('filterVentaType').value;
            const rows = document.querySelectorAll('#ventasTableBody tr');
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const type = row.getAttribute('data-type');
                
                const textMatch = text.includes(searchVal);
                const typeMatch = (typeVal === 'all' || type === typeVal);
                
                if (textMatch && typeMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Buscador global de cabecera
        function globalSearch(query) {
            query = query.toLowerCase().trim();
            if (query.length === 0) {
                // Resetear vistas si la búsqueda está vacía
                document.querySelectorAll('.table-modern tbody tr').forEach(tr => tr.style.display = '');
                return;
            }

            // Cambiar a la sección que tenga más sentido buscar
            const activeSec = document.querySelector('.dashboard-section.active-section').id;
            if (activeSec === 'section-dashboard') {
                switchTab('libros'); // Redirigir a libros por ejemplo
            }

            // Filtrar las tablas visibles
            document.querySelectorAll('.dashboard-section.active-section .table-modern tbody tr').forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Onload init
        window.addEventListener('DOMContentLoaded', () => {
            // Validar si PHP tiene un default tab (por ejemplo, después de un CRUD)
            let defaultTab = '<?php echo $default_tab; ?>';
            
            // Si el storage guardó otra tab y no hay un override activo, cargamos esa
            const savedTab = sessionStorage.getItem('activeTab');
            if (savedTab && !window.location.search.includes('action=edit')) {
                defaultTab = savedTab;
            }

            switchTab(defaultTab);

            // Si se cargó para editar, abrimos automáticamente el modal
            <?php if ($edit_product !== null): ?>
                openModal();
            <?php endif; ?>
        });
    </script>

</body>
</html>
