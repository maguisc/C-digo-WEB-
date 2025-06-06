<?php
// Arranco la sesión
session_status() == PHP_SESSION_NONE && session_start();

// Me fijo si el admin está logueado
if (!isset($_SESSION['admin_id'])) {
    // Si no está logueado, guardo el error
    $_SESSION['error'] = "Debes iniciar sesión para acceder";
    // Lo mando al login
    header("Location: login.php");
    // Corto la ejecución
    exit();
}
?>