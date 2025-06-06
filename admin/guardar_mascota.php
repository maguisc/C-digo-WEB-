<?php
// Arranco la sesión si no está activa
session_status() == PHP_SESSION_NONE && session_start();

// Incluyo los archivos necesarios
include 'auth/verificar_sesion.php';
include '../config/database.php';

// Lógica para eliminar mascota
if (isset($_POST['accion']) && $_POST['accion'] == 'eliminar') {
    header('Content-Type: application/json');
    
    $id_mascota = $_POST['id_mascota'];
    $respuesta = ['success' => false, 'message' => ''];
    
    try {
        // Arranco una transacción para hacer todo junto
        $conn->begin_transaction();
        
        // 1. Borro formularios de tránsito
        $stmt_transito = $conn->prepare("DELETE FROM formularios_transito WHERE id_mascota = ?");
        $stmt_transito->bind_param("i", $id_mascota);
        $stmt_transito->execute();
        
        // 2. Borro publicaciones
        $stmt_pub = $conn->prepare("DELETE FROM publicaciones WHERE id_mascota = ?");
        $stmt_pub->bind_param("i", $id_mascota);
        $stmt_pub->execute();
        
        // 3. Guardo la imagen antes de borrar
        $stmt_imagen = $conn->prepare("SELECT imagen FROM mascotas WHERE id_mascota = ?");
        $stmt_imagen->bind_param("i", $id_mascota);
        $stmt_imagen->execute();
        $mascota = $stmt_imagen->get_result()->fetch_assoc();
        
        // 4. Borro la mascota
        $stmt_mascota = $conn->prepare("DELETE FROM mascotas WHERE id_mascota = ?");
        $stmt_mascota->bind_param("i", $id_mascota);
        
        if ($stmt_mascota->execute()) {
            // Confirmo todos los cambios
            $conn->commit();
            
            // Borro la imagen del disco
            if ($mascota && $mascota['imagen']) {
                $ruta_imagen = "../" . $mascota['imagen'];
                file_exists($ruta_imagen) && unlink($ruta_imagen);
            }
            
            $respuesta = ['success' => true, 'message' => 'Mascota eliminada exitosamente'];
        } else {
            throw new Exception('Error al eliminar la mascota');
        }
        
    } catch (Exception $e) {
        // Si algo falla, deshago todos los cambios
        $conn->rollback();
        $respuesta['message'] = 'Error al eliminar la mascota: ' . $e->getMessage();
    }
    
    echo json_encode($respuesta);
    exit;
}

// Lógica para crear o actualizar mascota
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtengo todos los datos del formulario
    $datos = [
        'nombre' => $_POST['nombre'],
        'tipo' => $_POST['tipo'],
        'genero' => $_POST['genero'],
        'raza' => $_POST['raza'],
        'tamano' => $_POST['tamano'],
        'edad' => $_POST['edad'],
        'castrado' => $_POST['castrado'],
        'vacunado' => $_POST['vacunado'],
        'estado' => $_POST['estado']
    ];
    
    // Verifico si es actualización o creación
    $id_mascota = isset($_POST['id_mascota']) ? $_POST['id_mascota'] : null;
    
    // Manejo de la imagen
    $imagen = "";
    if(isset($_FILES['imagen']) && $_FILES['imagen']['size'] > 0) {
        $extension = pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION);
        $nombre_imagen = uniqid() . '.' . $extension;
        $target_dir = "../uploads/";
        $target_file = $target_dir . $nombre_imagen;
        
        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
            $imagen = "uploads/" . $nombre_imagen;
        } else {
            echo "Error al subir la imagen.";
            exit;
        }
    }
    
    // Preparo la consulta SQL según sea actualización o creación
    if ($id_mascota) {
        // Actualización
        if ($imagen) {
            // Con nueva imagen
            $sql = "UPDATE mascotas SET 
                    nombre = ?, tipo = ?, genero = ?, raza = ?, 
                    tamano = ?, edad = ?, castrado = ?, vacunado = ?, 
                    estado = ?, imagen = ? WHERE id_mascota = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssssi", 
                $datos['nombre'], $datos['tipo'], $datos['genero'], $datos['raza'],
                $datos['tamano'], $datos['edad'], $datos['castrado'], $datos['vacunado'],
                $datos['estado'], $imagen, $id_mascota
            );
        } else {
            // Sin nueva imagen
            $sql = "UPDATE mascotas SET 
                    nombre = ?, tipo = ?, genero = ?, raza = ?, 
                    tamano = ?, edad = ?, castrado = ?, vacunado = ?, 
                    estado = ? WHERE id_mascota = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssssi", 
                $datos['nombre'], $datos['tipo'], $datos['genero'], $datos['raza'],
                $datos['tamano'], $datos['edad'], $datos['castrado'], $datos['vacunado'],
                $datos['estado'], $id_mascota
            );
        }
    } else {
        // Creación nueva
        $sql = "INSERT INTO mascotas (nombre, tipo, genero, raza, tamano, edad, castrado, vacunado, imagen, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssss", 
            $datos['nombre'], $datos['tipo'], $datos['genero'], $datos['raza'],
            $datos['tamano'], $datos['edad'], $datos['castrado'], $datos['vacunado'],
            $imagen, $datos['estado']
        );
    }
    
    // Ejecuto la consulta y redirijo o muestro error
    if ($stmt->execute()) {
        header("Location: index.php");
    } else {
        echo "Error: " . $conn->error;
    }
    
    $stmt->close();
}

$conn->close();
?>