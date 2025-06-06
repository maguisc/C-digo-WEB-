<?php
// Subir imagen desde admin
include '../../config/database.php';

header('Content-Type: application/json');

// Verificar datos
if (!isset($_FILES['image']) || !isset($_POST['chat_id'])) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos']);
    exit;
}

$chat_id = $_POST['chat_id'];
$file = $_FILES['image'];

// Verificar tipo
$tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $tipos_permitidos)) {
    echo json_encode(['success' => false, 'error' => 'Tipo de archivo no permitido']);
    exit;
}

// Guardar archivo
$directorio = '../../uploads/chat/';
if (!file_exists($directorio)) {
    mkdir($directorio, 0777, true);
}

$nombre_archivo = uniqid() . '_' . $file['name'];
$ruta_completa = $directorio . $nombre_archivo;

if (move_uploaded_file($file['tmp_name'], $ruta_completa)) {
    echo json_encode([
        'success' => true,
        'url' => 'uploads/chat/' . $nombre_archivo
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar archivo']);
}