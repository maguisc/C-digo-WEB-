<?php
// mascotas/admin/eliminar_publicacion.php
header('Content-Type: application/json');
include '../config/database.php';
include 'auth/verificar_sesion.php';

$id = isset($_GET['id_mascota']) ? (int) $_GET['id_mascota'] : 0;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM publicaciones WHERE id_mascota = ?");
$stmt->bind_param('i', $id);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => $ok]);
