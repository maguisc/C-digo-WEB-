<?php
// Cargar mensajes del chat
include '../../config/database.php';

$email = $_GET['email'] ?? '';
if (empty($email)) {
  echo '<div class="text-center">Selecciona un chat</div>';
  exit;
}

// Consultar ID de chats de este usuario
$sql_chats = "SELECT id_chat FROM chats WHERE email_usuario = ?";
$stmt_chats = $conn->prepare($sql_chats);
$stmt_chats->bind_param("s", $email);
$stmt_chats->execute();
$result_chats = $stmt_chats->get_result();

if ($result_chats->num_rows === 0) {
  echo '<div class="text-center">No hay mensajes</div>';
  exit;
}

// Obtener todos los IDs de chat
$ids_chat = [];
while ($row_chat = $result_chats->fetch_assoc()) {
  $ids_chat[] = $row_chat['id_chat'];
}

// Consultar mensajes de todos los chats del usuario
$ids_placeholders = str_repeat('?,', count($ids_chat) - 1) . '?';
$sql = "SELECT * FROM mensajes WHERE id_chat IN ($ids_placeholders) ORDER BY id_mensaje ASC";
$stmt = $conn->prepare($sql);

// Binding dinámico de parámetros
$types = str_repeat('i', count($ids_chat));
$stmt->bind_param($types, ...$ids_chat);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo '<div class="text-center">No hay mensajes</div>';
  exit;
}

// Mostrar mensajes
while ($row = $result->fetch_assoc()) {
  $es_admin = ($row['tipo_emisor'] === 'admin');
  
  // Clase según el tipo de mensaje
  $clase_div = $es_admin ? 'mensaje-admin' : 'mensaje-usuario';
  $clase_burbuja = $es_admin ? 'burbuja-admin' : 'burbuja-usuario';
  
  echo '<div class="' . $clase_div . '">';
  echo '<div class="' . $clase_burbuja . '">';
  
  // Imagen o texto
  if (!empty($row['imagen_url'])) {
    $img_ruta = '../../' . $row['imagen_url'];
    echo '<img src="' . $img_ruta . '" class="imagen-mensaje" onclick="window.open(\'' . $img_ruta . '\', \'_blank\')" style="max-width:100%; border-radius:8px; cursor:pointer;">';
  } else {
    echo htmlspecialchars($row['mensaje']);
  }
  
  // Hora
  $hora = date('H:i', strtotime($row['fecha_envio']));
  echo '<div class="hora-mensaje">' . $hora . '</div>';
  
  echo '</div>'; // fin burbuja
  echo '</div>'; // fin mensaje
}
?>

<style>
.mensaje-usuario {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 10px;
}

.mensaje-admin {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 10px;
}

.burbuja-usuario {
  background-color: #e8e8e8;
  color: #333;
  border-radius: 15px;
  padding: 10px 15px;
  max-width: 70%;
  position: relative;
}

.burbuja-admin {
  background-color: #e88861;
  color: white;
  border-radius: 15px;
  padding: 10px 15px;
  max-width: 70%;
  position: relative;
}

.hora-mensaje {
  font-size: 11px;
  text-align: right;
  margin-top: 4px;
  opacity: 0.7;
}
</style>