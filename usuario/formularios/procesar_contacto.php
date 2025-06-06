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
    $ubicacion_vista = trim($_POST['ubicacion_vista']);
    $fecha_vista = $_POST['fecha_vista'];
    $informacion_adicional = trim($_POST['informacion_adicional']);

    try {
        // Insertar datos en la base
        $sql = "INSERT INTO formularios_contacto (
                    id_mascota, id_usuario, nombre_completo, email, telefono,
                    ubicacion_vista, fecha_vista, informacion_adicional
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iissssss",
            $id_mascota, $id_usuario, $nombre_completo, $email, $telefono,
            $ubicacion_vista, $fecha_vista, $informacion_adicional
        );

        if ($stmt->execute()) {
            // Éxito
            $stmt->close();
            $conn->close();
            $_SESSION['success'] = "¡Gracias por tu información! Nos pondremos en contacto con vos a la brevedad.";
            header("Location: ../index.php");
            exit();
        } else {
            throw new Exception("Error al procesar la información");
        }
    } catch (Exception $e) {
        // Error
        $conn->close();
        $_SESSION['error'] = "Error al enviar el formulario: " . $e->getMessage();
        header("Location: contactar.php?id=" . $id_mascota);
        exit();
    }
} else {
    // Acceso incorrecto
    $conn->close();
    header("Location: ../index.php");
    exit();
}
?>