<?php
session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Verificar si ya existe un administrador
    $sql_check = "SELECT COUNT(*) as total FROM administrador";
    $result = $conn->query($sql_check);
    $row = $result->fetch_assoc();

    if($row['total'] > 0) {
        $_SESSION['error'] = "Ya existe un administrador registrado";
        header("Location: registro.php");
        exit();
    }

    // Validar contraseña
    if (!(
        strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[0-9]/', $password) &&
        preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)
    )) {
        $_SESSION['error'] = "La contraseña no cumple con los requisitos de seguridad";
        header("Location: registro.php");
        exit();
    }

    // Encriptar contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar administrador
    $sql = "INSERT INTO administrador (nombre, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nombre, $email, $password_hash);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registro exitoso. Por favor, inicia sesión.";
        header("Location: login.php");
    } else {
        $_SESSION['error'] = "Error al registrar: " . $conn->error;
        header("Location: registro.php");
    }

    $stmt->close();
}

$conn->close();
?>