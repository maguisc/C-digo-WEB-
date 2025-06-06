<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../../config/database.php';
include '../includes/header.php';
include '../auth/verificar_sesion.php';

// Redirigir si no hay ID de mascota
if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$id_mascota = $_GET['id'];

// Obtener nombre de la mascota
$sql = "SELECT nombre FROM mascotas WHERE id_mascota = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mascota);
$stmt->execute();
$result = $stmt->get_result();
$mascota = $result->fetch_assoc();
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header" style="background-color: #E6E6FA;">
                    <h2 class="text-center mb-0">Formulario de Adopción</h2>
                </div>
                <div class="card-body">
                    <form action="procesar_adopcion.php" method="POST">
                        <input type="hidden" name="id_mascota" value="<?php echo $id_mascota; ?>">
                        <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['usuario_id']; ?>">
                        
                        <!-- Datos personales -->
                        <div class="mb-3">
                            <label for="nombre_completo" class="form-label">Nombre y apellido</label>
                            <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Número de teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" required>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required>
                        </div>

                        <div class="mb-3">
                            <label for="nombre_mascota" class="form-label">Nombre de la mascota a adoptar</label>
                            <input type="text" class="form-control" id="nombre_mascota" name="nombre_mascota" 
                                value="<?php echo htmlspecialchars($mascota['nombre']); ?>" readonly>
                        </div>

                        <!-- Preguntas sobre experiencia -->
                        <div class="mb-3">
                            <label class="form-label">¿Tiene experiencia previa en el cuidado de las mascotas?</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="experiencia_previa" value="Si" id="exp_si" required>
                                    <label class="form-check-label" for="exp_si">Si</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="experiencia_previa" value="No" id="exp_no" required>
                                    <label class="form-check-label" for="exp_no">No</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">¿Está de acuerdo en recibir una visita previa a la adopción?</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="acepta_visita" value="Si" id="visita_si" required>
                                    <label class="form-check-label" for="visita_si">Si</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="acepta_visita" value="No" id="visita_no" required>
                                    <label class="form-check-label" for="visita_no">No</label>
                                </div>
                            </div>
                        </div>

                        <!-- Condiciones de vivienda -->
                        <div class="mb-4">
                            <label class="form-label">Condiciones de vivienda</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_vivienda" value="Casa con patio" id="casa_patio" required>
                                <label class="form-check-label" for="casa_patio">Casa con patio</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_vivienda" value="Casa sin patio" id="casa_sin_patio">
                                <label class="form-check-label" for="casa_sin_patio">Casa sin patio</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_vivienda" value="Departamento chico" id="depto_chico">
                                <label class="form-check-label" for="depto_chico">Departamento chico</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_vivienda" value="Departamento grande" id="depto_grande">
                                <label class="form-check-label" for="depto_grande">Departamento grande</label>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex justify-content-center gap-3">
                            <button type="submit" class="btn btn-primary">Enviar formulario</button>
                            <a href="../detalle_mascota.php?id=<?php echo $id_mascota; ?>" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>