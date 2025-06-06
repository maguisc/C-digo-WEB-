<?php
include '../../config/database.php';
include '../auth/verificar_sesion.php';

header('Content-Type: application/json');

// Verificar datos
if (!isset($_FILES['image']) || !isset($_POST['chat_id'])) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos']);
    exit;
}

$idChat = $_POST['chat_id'];
$archivo = $_FILES['image'];

// Verificación de permisos
$consultaVerificar = "SELECT 1 FROM chats WHERE id_chat = ? AND id_usuario = ?";
$stmtVerificar = $conn->prepare($consultaVerificar);
$stmtVerificar->bind_param("ii", $idChat, $_SESSION['usuario_id']);
$stmtVerificar->execute();
if ($stmtVerificar->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'No tienes acceso a este chat']);
    exit;
}

// Crear directorio si no existe
$directorio = '../../uploads/chat/';
if (!file_exists($directorio)) {
    mkdir($directorio, 0777, true);
}

// Guardar archivo
$nombreArchivo = uniqid() . '_' . $archivo['name'];
$rutaCompleta = $directorio . $nombreArchivo;

if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
    echo json_encode([
        'success' => true,
        'url' => 'uploads/chat/' . $nombreArchivo
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar archivo']);
}
?>