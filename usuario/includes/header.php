<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Base URL para enlaces
$base_url = '/mascotas/usuario/';

// Manejo automático de chat
if (strpos($_SERVER['PHP_SELF'], 'chat/index.php') !== false && !isset($_GET['id']) && isset($_SESSION['usuario_id'])) {
    include_once '../../config/database.php';
    
    // Buscar chat existente o crear uno nuevo
    $usuario_id = $_SESSION['usuario_id'];
    $query = "SELECT id_chat FROM chats WHERE id_usuario = ? ORDER BY fecha_creacion DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Usar chat existente
        $chat = $result->fetch_assoc();
        header("Location: index.php?id=" . $chat['id_chat']);
        exit;
    } else {
        // Crear nuevo chat
        $nombre_usuario = $_SESSION['usuario_nombre'];
        $email_usuario = $_SESSION['usuario_email'];
        $fecha_actual = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("INSERT INTO chats (id_usuario, nombre_usuario, email_usuario, estado, fecha_creacion, fecha_actualizacion) VALUES (?, ?, ?, 'activo', ?, ?)");
        $stmt->bind_param("issss", $usuario_id, $nombre_usuario, $email_usuario, $fecha_actual, $fecha_actual);
        $stmt->execute();
        
        $chat_id = $conn->insert_id;
        header("Location: index.php?id=" . $chat_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adoptame Saladillo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/mascotas/css/styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="container-fluid">
            <!-- Logo/Título según rol -->
            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                <a class="navbar-brand" href="/mascotas/admin/index.php">Panel Administrador</a>
            <?php else: ?>
                <a class="navbar-brand" href="/mascotas/usuario/index.php">Adoptame Saladillo</a>
            <?php endif; ?>

            <!-- Botón responsive -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Menú principal -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'mascotas_adopcion.php' ? 'active' : ''; ?>" 
                            href="<?php echo $base_url; ?>mascotas_adopcion.php">Mascotas en Adopción</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'mascotas_transito.php' ? 'active' : ''; ?>" 
                            href="<?php echo $base_url; ?>mascotas_transito.php">Mascotas en Tránsito</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'mascotas_perdidas.php' ? 'active' : ''; ?>" 
                            href="<?php echo $base_url; ?>mascotas_perdidas.php">Mascotas Perdidas</a>
                    </li>
                    <li class="nav-item">
                        <?php if(isset($_SESSION['usuario_id'])): ?>
                            <a class="nav-link" href="<?php echo $base_url; ?>chat/index.php">Reportar Mascota</a>
                        <?php else: ?>
                            <a class="nav-link" href="<?php echo $base_url; ?>auth/login/login.php" 
                            onclick="alert('Debes iniciar sesión para reportar una mascota'); return true;">Reportar Mascota</a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'realizar_donacion.php' ? 'active' : ''; ?>" 
                            href="<?php echo $base_url; ?>realizar_donacion.php">Realizar Donación</a>
                    </li>
                </ul>

                <!-- Botones de sesión -->
                <div class="ms-auto">
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <span class="navbar-text me-3">
                            Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?>
                        </span>
                        <a class="btn btn-outline-danger" href="<?php echo $base_url; ?>auth/logout/logout.php">Cerrar Sesión</a>
                    <?php else: ?>
                        <a class="btn btn-outline-danger" href="<?php echo $base_url; ?>auth/login/login.php">Iniciar Sesión</a>
                        <a class="btn btn-outline-danger" href="<?php echo $base_url; ?>auth/registro/registro.php">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</body>