<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../../config/database.php';
include '../auth/verificar_sesion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $id_mascota = $_POST['id_mascota'];
    $id_usuario = $_SESSION['usuario_id'];
    $nombre_completo = trim($_POST['nombre_completo']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $nombre_mascota = trim($_POST['nombre_mascota']);
    $cubre_gastos = $_POST['cubre_gastos'];
    $acepta_visita = $_POST['acepta_visita'];
    $tipo_vivienda = $_POST['tipo_vivienda'];

    try {
        // Insertar datos en la base
        $sql = "INSERT INTO formularios_transito (
                    id_mascota, id_usuario, nombre_completo, email, telefono,
                    direccion, nombre_mascota, cubre_gastos, acepta_visita, tipo_vivienda
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iissssssss", 
            $id_mascota, $id_usuario, $nombre_completo, $email, $telefono,
            $direccion, $nombre_mascota, $cubre_gastos, $acepta_visita, $tipo_vivienda
        );

        if ($stmt->execute()) {
            // Éxito
            $stmt->close();
            $conn->close();
            $_SESSION['success'] = "¡Formulario de tránsito enviado con éxito! Nos pondremos en contacto con vos a la brevedad.";
            header("Location: ../index.php");
            exit();
        } else {
            throw new Exception("Error al procesar la solicitud");
        }
    } catch (Exception $e) {
        // Error
        $conn->close();
        $_SESSION['error'] = "Error al procesar el formulario: " . $e->getMessage();
        header("Location: formulario_transito.php?id=" . $id_mascota);
        exit();
    }
} else {
    // Acceso incorrecto
    $conn->close();
    header("Location: ../index.php");
    exit();
}
?>