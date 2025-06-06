<?php
// Arranco la sesión si todavía no está activa
session_status() == PHP_SESSION_NONE && session_start();

// Destruyo todos los datos de la sesión
session_destroy();

// Redirijo a la página principal
header("Location: /mascotas/usuario/index.php");

// Corto la ejecución
exit();
?>