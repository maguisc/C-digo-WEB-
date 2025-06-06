<?php
// Arranco la sesión si todavía no está activa
session_status() == PHP_SESSION_NONE && session_start();

// Me fijo si el usuario es admin y está logueado
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    // Si no es admin o no está logueado, guardo el error
    $_SESSION['error'] = "Debes iniciar sesión como administrador para acceder";
    // Lo mando al login
    header("Location: /mascotas/usuario/auth/login/login.php");
    // Corto la ejecución
    exit();
}
?>