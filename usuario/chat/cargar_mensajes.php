<?php
include '../../config/database.php';
include '../auth/verificar_sesion.php';

header('Content-Type: application/json');

// Verificar parámetros
$idChat = isset($_GET['id_chat']) ? (int)$_GET['id_chat'] : 0;

// Validar ID de chat
if (!$idChat) {
  echo json_encode([
    'error' => 'ID de chat no proporcionado'
  ]);
  exit;
}

try {
  // Verificación de permisos
  $consultaVerificar = "SELECT 1 FROM chats WHERE id_chat = ? AND id_usuario = ?";
  $stmtVerificar = $conn->prepare($consultaVerificar);
  $stmtVerificar->bind_param("ii", $idChat, $_SESSION['usuario_id']);
  $stmtVerificar->execute();
  $resultadoVerificar = $stmtVerificar->get_result();
  
  if ($resultadoVerificar->num_rows === 0) {
    echo json_encode([
      'error' => 'No tienes permiso para acceder a este chat'
    ]);
    exit;
  }

  // Obtener mensajes ordenados por ID
  $consultaMensajes = "SELECT * FROM mensajes WHERE id_chat = ? ORDER BY id_mensaje ASC";
  $stmtMensajes = $conn->prepare($consultaMensajes);
  $stmtMensajes->bind_param("i", $idChat);
  $stmtMensajes->execute();
  $resultado = $stmtMensajes->get_result();

  // Convertir a array
  $mensajes = [];
  while ($fila = $resultado->fetch_assoc()) {
    $mensajes[] = $fila;
  }
  
  // Devolver resultados
  echo json_encode($mensajes);
  
} catch (Exception $e) {
  echo json_encode([
    'error' => 'Error en la consulta: ' . $e->getMessage()
  ]);
}
?>