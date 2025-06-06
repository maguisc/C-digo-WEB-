<?php
// Arranco la sesión si todavía no está activa
session_status() == PHP_SESSION_NONE && session_start();

// Calculo la ruta relativa al CSS
$profundidad = substr_count(dirname($_SERVER['PHP_SELF']), '/') - 1;
$rutaCss = str_repeat('../', $profundidad) . 'css/styles.css';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - Gestión de Mascotas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= $rutaCss ?>" rel="stylesheet">
</head>
<body>
    <!-- Botón para menú móvil -->
    <button id="sidebarToggle" class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Menú lateral -->
    <div id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <h4>Adoptame Saladillo</h4>
            <!-- Botón de cierre para móvil -->
            <button class="close-sidebar d-md-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="user-info mb-3">
            <?php if(isset($_SESSION['admin_nombre'])): ?>
                <p class="mb-0">Bienvenido,</p>
                <p class="fw-bold"><?= $_SESSION['admin_nombre'] ?></p>
            <?php endif; ?>
        </div>
        
        <div class="menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="/mascotas/usuario/index.php">
                        <i class="fas fa-globe me-2"></i> Web
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="/mascotas/admin/index.php">
                        <i class="fas fa-paw me-2"></i> Mascotas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="/mascotas/admin/registrar_mascota.php">
                        <i class="fas fa-plus-circle me-2"></i> Registrar Mascotas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="/mascotas/admin/solicitudes/index.php">
                        <i class="fas fa-clipboard-list me-2"></i> Solicitudes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="/mascotas/admin/chat/index.php">
                        <i class="fas fa-comments me-2"></i> Chats Activos
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link d-flex align-items-center text-danger" href="/mascotas/admin/auth/logout/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Referencias a elementos del DOM
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const closeSidebar = document.querySelector('.close-sidebar');
        
        // Creo overlay para fondo oscuro al abrir menú
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    
        // Función para alternar la visibilidad del sidebar
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
            overlay.classList.toggle('active');
        }
    
        // Agrego eventos a los botones
        sidebarToggle.addEventListener('click', toggleSidebar);
        closeSidebar.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
    
        // En móvil, cerrar sidebar al hacer clic en un enlace
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                window.innerWidth < 992 && toggleSidebar();
            });
        });
    });
    </script>