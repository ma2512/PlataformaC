<?php
require_once 'conexion.php';

// Crear tabla usuarios
$query_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    es_admin TINYINT(1) DEFAULT 0,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$conexion->query($query_usuarios)) {
    die("Error al crear la tabla usuarios: " . $conexion->error);
}

// Intentar agregar es_admin por si la tabla ya existe
$conexion->query("ALTER TABLE usuarios ADD COLUMN es_admin TINYINT(1) DEFAULT 0;");

// Crear tabla mensajes de contacto
$query_mensajes = "CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$conexion->query($query_mensajes)) {
    die("Error al crear la tabla mensajes: " . $conexion->error);
}

// Crear tabla productos
$query_productos = "CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    imagen VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$conexion->query($query_productos)) {
    die("Error al crear la tabla productos: " . $conexion->error);
}

// Crear tabla compras y suscripciones
$query_compras = "CREATE TABLE IF NOT EXISTS compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    nombre_cliente VARCHAR(100) NOT NULL,
    email_cliente VARCHAR(100) NOT NULL,
    concepto VARCHAR(255) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    tipo VARCHAR(50) DEFAULT 'compra',
    metodo_pago VARCHAR(50) DEFAULT 'Tarjeta de Crédito',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$conexion->query($query_compras)) {
    die("Error al crear la tabla compras: " . $conexion->error);
}

// Insertar productos por defecto si la tabla está vacía
$check_empty = "SELECT COUNT(*) AS total FROM productos";
$result = $conexion->query($check_empty);
$row = $result->fetch_assoc();

if ($row['total'] == 0) {
    $insert_productos = "INSERT INTO productos (titulo, descripcion, precio, imagen) VALUES
        ('Inglés desde cero', 'Guía completa para principiantes.', 200.00, '/Frontend/IMG/libro-ingles-cero.jpg'),
        ('Francés básico', 'Aprende vocabulario esencial.', 670.00, '/Frontend/IMG/libro-frances-basico.webp'),
        ('Alemán práctico', 'Frases y gramática esencial.', 900.00, '/Frontend/IMG/libro-aleman-prac.webp'),
        ('Japonés para principiantes', 'Aprende hiragana y vocabulario.', 220.00, '/Frontend/IMG/libro-japones-princi.webp'),
        ('Diccionario Italiano', 'Más de 50,000 definiciones y frases.', 350.00, '/Frontend/IMG/diccionario-italiano.avif'),
        ('Portugués Fluido', 'Guía de conversación para viajes.', 280.00, '/Frontend/IMG/libro-portugues-fluido.jpg'),
        ('Gramática Avanzada', 'Domina las estructuras complejas.', 550.00, '/Frontend/IMG/libro-gramatica.jpg'),
        ('Escritura China', 'Cuaderno de práctica de caracteres.', 310.00, '/Frontend/IMG/libro-escriturachi.jpg')";
    
    if (!$conexion->query($insert_productos)) {
        die("Error al insertar productos iniciales: " . $conexion->error);
    }
}

// Insertar compras por defecto si la tabla está vacía
$check_compras_empty = "SELECT COUNT(*) AS total FROM compras";
$result_compras = $conexion->query($check_compras_empty);
$row_compras = $result_compras->fetch_assoc();

if ($row_compras['total'] == 0) {
    $insert_compras = "INSERT INTO compras (nombre_cliente, email_cliente, concepto, monto, tipo) VALUES
        ('Juan Pérez', 'juan.perez@email.com', 'Suscripción mensual: Plan Pro', 19.00, 'suscripcion'),
        ('María Gómez', 'maria.gomez@email.com', 'Inglés desde cero (Libro)', 200.00, 'compra'),
        ('Carlos Ruiz', 'carlos.ruiz@email.com', 'Inscripción: Inglés Profesional (Certificación A1/A2)', 0.00, 'inscripcion'),
        ('Sofía Silva', 'sofia.silva@email.com', 'Suscripción mensual: Plan Premium', 29.00, 'suscripcion')";
    
    if (!$conexion->query($insert_compras)) {
        die("Error al insertar compras iniciales: " . $conexion->error);
    }
}

// Insertar administrador por defecto
$check_admin = "SELECT COUNT(*) AS total FROM usuarios WHERE email = 'admin@bridgeup.com'";
$res_admin = $conexion->query($check_admin);
$row_admin = $res_admin->fetch_assoc();
if ($row_admin['total'] == 0) {
    $hash_admin = password_hash('admin', PASSWORD_DEFAULT);
    $conexion->query("INSERT INTO usuarios (nombre, email, password, es_admin) VALUES ('Administrador', 'admin@bridgeup.com', '$hash_admin', 1)");
}

echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 30px; border-radius: 12px; background: #e0f2fe; border: 1px solid #bae6fd; text-align: center; color: #0369a1;'>";
echo "<h2>✅ Base de datos inicializada con éxito</h2>";
echo "<p>Las tablas necesarias han sido creadas (usuarios, mensajes, productos, compras) y los datos por defecto se han cargado.</p>";
echo "<a href='../Frontend/HTML/Home.html' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #0284c7; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>Volver al Inicio</a>";
echo "</div>";
?>
