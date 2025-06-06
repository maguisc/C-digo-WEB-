<?php
header('Content-Type: application/json');
include '../../config/database.php';
include '../auth/verificar_sesion.php';

// Verificación básica
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Obtener datos
$id_chat = isset($_POST['id_chat']) ? intval($_POST['id_chat']) : 0;
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
$imagen_url = isset($_POST['imagen_url']) ? trim($_POST['imagen_url']) : '';

// Validación básica
if (!$id_chat || (empty($mensaje) && empty($imagen_url))) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// Verificar permisos
$sql_verificar = "SELECT 1 FROM chats WHERE id_chat = ? AND id_usuario = ?";
$stmt_verificar = $conn->prepare($sql_verificar);
$stmt_verificar->bind_param("ii", $id_chat, $_SESSION['usuario_id']);
$stmt_verificar->execute();
if ($stmt_verificar->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'No tienes acceso a este chat']);
    exit;
}

// Preparar datos del mensaje
$id_usuario = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario_nombre'] ?? '';
$email_usuario = $_SESSION['usuario_email'] ?? '';
$tipo_emisor = 'usuario';

// Obtener fecha/hora actual (Argentina)
date_default_timezone_set('America/Argentina/Buenos_Aires');
$fecha_hora = date('Y-m-d H:i:s');

// Insertar mensaje
$sql_insertar = "INSERT INTO mensajes (id_chat, id_emisor, nombre_emisor, email_emisor, tipo_emisor, mensaje, imagen_url, fecha_envio) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_insertar = $conn->prepare($sql_insertar);
$stmt_insertar->bind_param("iissssss", $id_chat, $id_usuario, $nombre_usuario, $email_usuario, $tipo_emisor, $mensaje, $imagen_url, $fecha_hora);
$resultado = $stmt_insertar->execute();

if ($resultado) {
    // ID del mensaje insertado
    $id_mensaje = $stmt_insertar->insert_id;
    
    // Actualizar último mensaje del chat
    $texto_resumen = !empty($imagen_url) ? "[Imagen]" : $mensaje;
    $sql_actualizar = "UPDATE chats SET ultimo_mensaje = ?, fecha_ultimo_mensaje = ?, fecha_actualizacion = ? WHERE id_chat = ?";
    $stmt_actualizar = $conn->prepare($sql_actualizar);
    $stmt_actualizar->bind_param("sssi", $texto_resumen, $fecha_hora, $fecha_hora, $id_chat);
    $stmt_actualizar->execute();
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'mensaje_id' => $id_mensaje,
        'timestamp' => $fecha_hora
    ]);
} else {
    // Error
    echo json_encode([
        'success' => false,
        'error' => 'Error al guardar el mensaje: ' . $stmt_insertar->error
    ]);
}
?>