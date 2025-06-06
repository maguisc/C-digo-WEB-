<?php
session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Preparar la consulta
    $sql = "SELECT id_admin, nombre, email, password FROM administrador WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        // Verificar la contraseña
        if (password_verify($password, $admin['password'])) {
            // Iniciar sesión
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_nombre'] = $admin['nombre'];
            $_SESSION['admin_email'] = $admin['email'];
            
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Contraseña incorrecta";
        }
    } else {
        $_SESSION['error'] = "No existe un administrador con ese correo";
    }

    header("Location: login.php");
    $stmt->close();
}

$conn->close();
?>