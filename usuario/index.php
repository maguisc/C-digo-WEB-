<?php 
include 'includes/header.php';
include '../config/database.php';
?>

<!-- Mensaje de éxito (si existe) -->
<?php if(isset($_SESSION['success'])): ?>
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Banner principal -->
<div class="seccion-principal">
    <img src="/mascotas/uploads/portada.jpg" alt="Portada Adoptame Saladillo" class="imagen-principal">
    <div class="texto-principal">
        <h1>Bienvenidos a Adoptame Saladillo</h1>
        <p>¡Encontrá a tu compañero perfecto, o ayudá a una mascota a encontrar su hogar!</p>
    </div>
</div>

<!-- Tarjetas de servicios -->
<div class="container mb-5">
    <div class="row g-4">
        <!-- Primera fila: 3 tarjetas -->
        <div class="col-md-4">
            <div class="tarjeta-servicio tarjeta-servicio-adopcion">
                <div class="tarjeta-servicio-cuerpo">
                    <i class="fas fa-paw icono-servicio"></i>
                    <h3 class="titulo-servicio">Mascotas en Adopción</h3>
                    <p class="descripcion-servicio">Encontrá a tu próximo compañero y dale un hogar lleno de amor.</p>
                    <a href="mascotas_adopcion.php" class="btn boton-ver-mascotas">Ver mascotas</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="tarjeta-servicio tarjeta-servicio-transito h-100">
                <div class="tarjeta-servicio-cuerpo">
                    <i class="fas fa-home icono-servicio"></i>
                    <h3 class="titulo-servicio">Mascotas en Tránsito</h3>
                    <p class="descripcion-servicio">Ayudá temporalmente a una mascota mientras encuentra su hogar definitivo.</p>
                    <a href="mascotas_transito.php" class="btn boton-ver-mascotas">Ver mascotas</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="tarjeta-servicio tarjeta-servicio-perdidas h-100">
                <div class="tarjeta-servicio-cuerpo">
                    <i class="fas fa-search icono-servicio"></i>
                    <h3 class="titulo-servicio">Mascotas Perdidas</h3>
                    <p class="descripcion-servicio">Ayudanos a reunir mascotas perdidas con sus familias.</p>
                    <a href="mascotas_perdidas.php" class="btn boton-ver-mascotas">Ver mascotas</a>
                </div>
            </div>
        </div>

        <!-- Segunda fila: 2 tarjetas centradas -->
        <div class="col-md-6">
            <div class="tarjeta-servicio tarjeta-servicio-reportar h-100">
                <div class="tarjeta-servicio-cuerpo">
                    <i class="fas fa-bullhorn icono-servicio"></i>
                    <h3 class="titulo-servicio">Reportar Mascota</h3>
                    <p class="descripcion-servicio">¿Encontraste o perdiste una mascota? Reportala acá y te ayudamos a difundir.</p>
                    <a href="#" onclick="iniciarChat(); return false;" class="btn boton-reportar">Reportar</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="tarjeta-servicio tarjeta-servicio-donacion h-100">
                <div class="tarjeta-servicio-cuerpo">
                    <i class="fas fa-heart icono-servicio"></i>
                    <h3 class="titulo-servicio">Realizar Donación</h3>
                    <p class="descripcion-servicio">Tu ayuda es fundamental para continuar con nuestra labor de rescate y cuidado.</p>
                    <a href="realizar_donacion.php" class="btn boton-donar">Donar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include 'includes/sidebar.php';
?>