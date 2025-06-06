<?php
session_start();

// Eliminar todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Redirigir al inicio
header("Location: /mascotas/usuario/index.php");
exit;
?>