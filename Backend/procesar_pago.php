<?php
require_once 'conexion.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre_cliente']) ? trim($_POST['nombre_cliente']) : '';
    $email = isset($_POST['email_cliente']) ? trim($_POST['email_cliente']) : '';
    $concepto = isset($_POST['concepto']) ? trim($_POST['concepto']) : '';
    $monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0.00;
    
    if (empty($nombre) || empty($email) || empty($concepto)) {
        echo json_encode(['status' => 'error', 'message' => 'Campos obligatorios incompletos.']);
        exit();
    }

    // Obtener ID de usuario de la sesión si está logueado
    $usuario_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

    // Determinar el tipo de registro
    $tipo = 'compra';
    if ($monto == 0.00 || stripos($concepto, 'Inscripción') !== false || stripos($concepto, 'Curso') !== false) {
        $tipo = 'inscripcion';
    } elseif (stripos($concepto, 'Suscripción') !== false || stripos($concepto, 'Plan') !== false) {
        $tipo = 'suscripcion';
    }

    // Obtener y formatear el método de pago
    $metodo_raw = isset($_POST['metodo_pago']) ? strtolower(trim($_POST['metodo_pago'])) : 'tarjeta';
    $metodo_pago = 'Tarjeta de Crédito';
    if ($metodo_raw === 'paypal') {
        $metodo_pago = 'PayPal';
    } elseif ($metodo_raw === 'oxxo') {
        $metodo_pago = 'OXXO Pay';
    }

    // Insertar en la base de datos
    $stmt = $conexion->prepare("INSERT INTO compras (usuario_id, nombre_cliente, email_cliente, concepto, monto, tipo, metodo_pago) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssdss", $usuario_id, $nombre, $email, $concepto, $monto, $tipo, $metodo_pago);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Registro de pago exitoso.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar en la base de datos: ' . $conexion->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>
