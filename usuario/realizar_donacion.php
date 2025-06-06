<?php 
include 'includes/header.php';
include '../config/database.php';
?>

<!-- Banner de donaciones -->
<div class="hero-section">
    <img src="../uploads/donaciones.png" alt="Donación" class="hero-image">
    <div class="hero-text">
        <h1>Ayudanos a seguir ayudando</h1>
        <p>¡Tu aporte marca la diferencia en la vida de cientos de mascotas!</p>
    </div>
</div>

<!-- Opciones de donación -->
<div class="container py-5">
    <div class="row g-4 justify-content-center">
        <!-- Transferencia Bancaria -->
        <div class="col-md-6">
            <div class="card card-donacion-personalizada">
                <div class="card-body text-center">
                    <div class="icon-container mb-3">
                        <i class="bi bi-bank" style="font-size: 3rem; color: #e76f51;"></i>
                    </div>
                    <h3 class="mb-3" style="color:#e76f51;">Transferencia Bancaria</h3>
                    <p class="mb-1"><strong>Alias:</strong> adoptame.saladillo</p>
                    <p class="mb-1"><strong>CBU:</strong> 0000003100031234567890</p>
                    <p><strong>Titular:</strong> Asociación Adoptame Saladillo</p>
                </div>
            </div>
        </div>
        
        <!-- Mercado Pago -->
        <div class="col-md-6">
            <div class="card card-donacion-personalizada">
                <div class="card-body text-center">
                    <div class="icon-container mb-3">
                        <i class="bi bi-credit-card" style="font-size: 3rem; color: #e76f51;"></i>
                    </div>
                    <h3 class="mb-3" style="color:#e76f51;">Mercado Pago</h3>
                    <p class="mb-3">Hacé tu donación fácil y rápida con un solo clic</p>
                    <a href="https://www.mercadopago.com.ar/tu-link-de-donacion" target="_blank" class="btn btn-primary btn-lg px-5 btn-donar">Donar con Mercado Pago</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include 'includes/sidebar.php';
?>