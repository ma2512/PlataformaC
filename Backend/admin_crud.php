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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Libros | BridgeUp</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background-color: #f8fafc;
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            color: #0f172a;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        header h1 {
            font-size: 22px;
            font-weight: 600;
            color: #0f172a;
        }
        header a {
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px 18px;
            border-radius: 20px;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        header a:hover {
            background: #e2e8f0;
            color: #0f172a;
            transform: translateY(-1px);
        }
        .container {
            max-width: 1250px;
            width: 100%;
            margin: 40px auto;
            padding: 0 20px;
            flex-grow: 1;
            display: grid;
            grid-template-columns: 1.15fr 1.85fr;
            gap: 30px;
        }
        @media (max-width: 950px) {
            .container {
                grid-template-columns: 1fr;
            }
        }
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            height: fit-content;
        }
        .card h2 {
            font-size: 18px;
            margin-bottom: 25px;
            color: #4f46e5;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px 15px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }
        .form-group option {
            background: #ffffff;
            color: #0f172a;
        }
        .btn {
            display: inline-block;
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            width: 100%;
            transition: all 0.2s ease;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .btn:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }
        .btn:active {
            transform: scale(0.98);
        }
        .btn-secondary {
            display: block;
            background: #f8fafc;
            color: #475569;
            border: 1px solid #cbd5e1;
            margin-top: 10px;
            box-shadow: none;
        }
        .btn-secondary:hover {
            background: #f1f5f9;
            color: #0f172a;
            box-shadow: none;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 600;
        }
        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        th, td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
        }
        tr:hover {
            background: #f8fafc;
        }
        .product-img {
            width: 50px;
            height: 70px;
            object-fit: cover;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }
        .btn-edit {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .btn-edit:hover {
            background: #1d4ed8;
            color: white;
            border-color: #1d4ed8;
        }
        .btn-delete {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .btn-delete:hover {
            background: #b91c1c;
            color: white;
            border-color: #b91c1c;
        }
        .card-full {
            grid-column: 1 / -1;
            margin-top: 20px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-compra {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .badge-suscripcion {
            background-color: #f3e8ff;
            color: #7e22ce;
            border: 1px solid #e9d5ff;
        }
        .badge-inscripcion {
            background-color: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        footer {
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            margin-top: auto;
        }
    </style>
</head>
<body>

    <header>
        <h1>🛠️ BridgeUp CRUD Admin</h1>
        <div style="display: flex; gap: 15px; align-items: center;">
            <span style="font-size: 14px; font-weight: 500; color: #64748b;">Conectado como Administrador</span>
            <a href="../Frontend/HTML/Home.html">Ir al Sitio</a>
            <a href="logout.php" style="background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c;">Salir</a>
        </div>
    </header>

    <div class="container">
        
        <!-- Formulario de Agregar / Editar -->
        <div class="card">
            <h2><?php echo $edit_product ? "Editar Libro" : "Agregar Nuevo Libro"; ?></h2>
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form action="admin_crud.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $edit_product ? $edit_product['id'] : 0; ?>">
                
                <div class="form-group">
                    <label>Título del Libro</label>
                    <input type="text" name="titulo" value="<?php echo $edit_product ? htmlspecialchars($edit_product['titulo']) : ''; ?>" placeholder="Ej. Inglés para Negocios" required>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="4" placeholder="Breve descripción del libro..." required><?php echo $edit_product ? htmlspecialchars($edit_product['descripcion']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Precio (MXN)</label>
                    <input type="number" name="precio" step="0.01" value="<?php echo $edit_product ? htmlspecialchars($edit_product['precio']) : ''; ?>" placeholder="Ej. 350.00" required>
                </div>

                <div class="form-group">
                    <label>Subir Imagen Personalizada (Recomendado)</label>
                    <input type="file" name="imagen_file" accept="image/*">
                    <p style="font-size: 11px; color: #777; margin-top: 5px;">Si seleccionas un archivo, se utilizará en lugar del selector de abajo.</p>
                </div>

                <div class="form-group">
                    <label>O Seleccionar Imagen Existente</label>
                    <select name="imagen">
                        <?php if ($edit_product): ?>
                            <option value="" selected>-- Mantener Imagen Actual (<?php echo basename($edit_product['imagen']); ?>) --</option>
                        <?php else: ?>
                            <option value="" selected>-- Seleccionar Imagen --</option>
                        <?php endif; ?>
                        <?php
                        // Listado de imágenes disponibles
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

                <button type="submit" class="btn"><?php echo $edit_product ? "Actualizar Producto" : "Guardar Producto"; ?></button>
                
                <?php if ($edit_product): ?>
                    <a href="admin_crud.php" class="btn btn-secondary">Cancelar Edición</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabla de Listado -->
        <div class="card">
            <h2>Inventario de Libros</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($productos_result && $productos_result->num_rows > 0): ?>
                            <?php while ($prod = $productos_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $img_path = htmlspecialchars($prod['imagen']);
                                        if (strpos($img_path, '/Frontend/') === 0) {
                                            $img_path = '../' . substr($img_path, 1);
                                        }
                                        ?>
                                        <img src="<?php echo $img_path; ?>" alt="Portada" class="product-img" onerror="this.onerror=null; this.src='../Frontend/IMG/libro-ingles-cero.jpg';">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($prod['titulo']); ?></strong>
                                        <p style="font-size: 12px; color: #777;"><?php echo htmlspecialchars($prod['descripcion']); ?></p>
                                    </td>
                                    <td>$<?php echo number_format($prod['precio'], 2); ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="admin_crud.php?action=edit&id=<?php echo $prod['id']; ?>" class="action-btn btn-edit">Editar</a>
                                            <a href="admin_crud.php?action=delete&id=<?php echo $prod['id']; ?>" class="action-btn btn-delete" onclick="return confirm('¿Estás seguro de eliminar este libro?')">Eliminar</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #999;">No hay libros registrados. Usa el formulario para agregar uno.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Registro de Compras y Suscripciones (Ancho Completo) -->
        <div class="card card-full">
            <h2>Registro de Compras y Suscripciones</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Tipo</th>
                            <th>Método</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($compras_result && $compras_result->num_rows > 0): ?>
                            <?php while ($compra = $compras_result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $compra['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($compra['nombre_cliente']); ?></strong>
                                        <p style="font-size: 12px; color: #777;"><?php echo htmlspecialchars($compra['email_cliente']); ?></p>
                                    </td>
                                    <td><?php echo htmlspecialchars($compra['concepto']); ?></td>
                                    <td><strong>$<?php echo number_format($compra['monto'], 2); ?></strong></td>
                                    <td>
                                        <?php 
                                        $tipo = htmlspecialchars($compra['tipo']);
                                        $badge_class = 'badge-compra';
                                        if ($tipo === 'suscripcion') {
                                            $badge_class = 'badge-suscripcion';
                                        } elseif ($tipo === 'inscripcion') {
                                            $badge_class = 'badge-inscripcion';
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $tipo; ?></span>
                                    </td>
                                    <td>
                                        <span style="font-size: 12px; font-weight: 600; color: #5a189a; background: #f3e8ff; border: 1px solid #e0aaff; padding: 4px 10px; border-radius: 20px; display: inline-block;">
                                            <?php echo htmlspecialchars(isset($compra['metodo_pago']) ? $compra['metodo_pago'] : 'Tarjeta de Crédito'); ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 13px; color: #555;"><?php echo htmlspecialchars($compra['fecha']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #999;">No hay registros de compras o suscripciones.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <footer>
        <p>© 2026 BridgeUp Admin Panel | Gestión de Libros de Idiomas</p>
    </footer>

</body>
</html>
