<?php
$host     = "localhost";
$usuario  = "root";
$password = "root";
$base     = "bridgeup_db";

// Desactivar reporte de excepciones estricto temporalmente
mysqli_report(MYSQLI_REPORT_OFF);

// Conectar al servidor mysql sin seleccionar base de datos inicialmente
$conexion = new mysqli($host, $usuario, $password, "", 3306);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Crear la base de datos si no existe
$conexion->query("CREATE DATABASE IF NOT EXISTS `$base` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

// Seleccionar la base de datos
if (!$conexion->select_db($base)) {
    die("Error al seleccionar la base de datos '$base': " . $conexion->error);
}

// Asegurar que la columna es_admin existe en la tabla usuarios
$table_check = $conexion->query("SHOW TABLES LIKE 'usuarios'");
if ($table_check && $table_check->num_rows > 0) {
    $columns = $conexion->query("SHOW COLUMNS FROM usuarios LIKE 'es_admin'");
    if ($columns && $columns->num_rows === 0) {
        $conexion->query("ALTER TABLE usuarios ADD COLUMN es_admin TINYINT(1) DEFAULT 0");
    }
    
    // Asegurar que el administrador por defecto existe
    $check_admin = $conexion->query("SELECT id FROM usuarios WHERE email = 'admin@bridgeup.com'");
    if ($check_admin && $check_admin->num_rows === 0) {
        $hash_admin = password_hash('admin', PASSWORD_DEFAULT);
        $conexion->query("INSERT INTO usuarios (nombre, email, password, es_admin) VALUES ('Administrador', 'admin@bridgeup.com', '$hash_admin', 1)");
    }
}

// Asegurar que la columna metodo_pago existe en la tabla compras
$table_check_compras = $conexion->query("SHOW TABLES LIKE 'compras'");
if ($table_check_compras && $table_check_compras->num_rows > 0) {
    $columns_compras = $conexion->query("SHOW COLUMNS FROM compras LIKE 'metodo_pago'");
    if ($columns_compras && $columns_compras->num_rows === 0) {
        $conexion->query("ALTER TABLE compras ADD COLUMN metodo_pago VARCHAR(50) DEFAULT 'Tarjeta de Crédito'");
    }
}
?>
