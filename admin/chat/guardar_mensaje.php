<?php
include '../../config/database.php';

header('Content-Type: application/json');

// Verificación mínima
$id_chat = $_POST['id_chat'] ?? null;
$email_usuario = $_POST['email_usuario'] ?? null;
$mensaje = $_POST['mensaje'] ?? null;
$imagen_url = $_POST['imagen_url'] ?? '';
$tipo_emisor = 'admin';

if (!$id_chat || empty($email_usuario) || (empty($mensaje) && empty($imagen_url))) {
  echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
  exit;
}

// Insertar mensaje con fecha automática de MySQL
$stmt = $conn->prepare(
  "INSERT INTO mensajes (id_chat, tipo_emisor, mensaje, imagen_url, fecha_envio)
   VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)"
);
$stmt->bind_param("isss", $id_chat, $tipo_emisor, $mensaje, $imagen_url);

if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'error' => 'Error al guardar el mensaje']);
}
?>
