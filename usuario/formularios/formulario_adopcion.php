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
            <div class="tarjeta-formulario">
                <div class="encabezado-formulario">
                    <h2 class="titulo-formulario">Formulario de Adopción</h2>
                </div>
                <div class="cuerpo-formulario">
                    <form action="procesar_adopcion.php" method="POST">
                        <input type="hidden" name="id_mascota" value="<?php echo $id_mascota; ?>">
                        <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['usuario_id']; ?>">
                        
                        <!-- Datos personales -->
                        <div class="grupo-campo">
                            <label for="nombre_completo" class="etiqueta-campo">Nombre y apellido</label>
                            <input type="text" class="campo-entrada" id="nombre_completo" name="nombre_completo" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="email" class="etiqueta-campo">Correo electrónico</label>
                            <input type="email" class="campo-entrada" id="email" name="email" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="telefono" class="etiqueta-campo">Número de teléfono</label>
                            <input type="tel" class="campo-entrada" id="telefono" name="telefono" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="direccion" class="etiqueta-campo">Dirección</label>
                            <input type="text" class="campo-entrada" id="direccion" name="direccion" required>
                        </div>

                        <div class="grupo-campo">
                            <label for="nombre_mascota" class="etiqueta-campo">Nombre de la mascota a adoptar</label>
                            <input type="text" class="campo-entrada campo-solo-lectura" id="nombre_mascota" name="nombre_mascota" 
                                value="<?php echo htmlspecialchars($mascota['nombre']); ?>" readonly>
                        </div>

                        <!-- Preguntas sobre experiencia -->
                        <div class="grupo-campo">
                            <label class="etiqueta-campo">¿Tiene experiencia previa en el cuidado de las mascotas?</label>
                            <div class="grupo-opciones">
                                <div class="opcion-radio">
                                    <input type="radio" name="experiencia_previa" value="Si" id="exp_si" required>
                                    <label class="etiqueta-radio" for="exp_si">Si</label>
                                </div>
                                <div class="opcion-radio">
                                    <input type="radio" name="experiencia_previa" value="No" id="exp_no" required>
                                    <label class="etiqueta-radio" for="exp_no">No</label>
                                </div>
                            </div>
                        </div>

                        <div class="grupo-campo">
                            <label class="etiqueta-campo">¿Está de acuerdo en recibir una visita previa a la adopción?</label>
                            <div class="grupo-opciones">
                                <div class="opcion-radio">
                                    <input type="radio" name="acepta_visita" value="Si" id="visita_si" required>
                                    <label class="etiqueta-radio" for="visita_si">Si</label>
                                </div>
                                <div class="opcion-radio">
                                    <input type="radio" name="acepta_visita" value="No" id="visita_no" required>
                                    <label class="etiqueta-radio" for="visita_no">No</label>
                                </div>
                            </div>
                        </div>

                        <!-- Condiciones de vivienda -->
                        <div class="grupo-campo">
                            <label class="etiqueta-campo">Condiciones de vivienda</label>
                            <div class="opciones-vivienda">
                                <div class="opcion-vivienda">
                                    <input type="radio" name="tipo_vivienda" value="Casa con patio" id="casa_patio" required>
                                    <label class="etiqueta-radio" for="casa_patio">Casa con patio</label>
                                </div>
                                <div class="opcion-vivienda">
                                    <input type="radio" name="tipo_vivienda" value="Casa sin patio" id="casa_sin_patio">
                                    <label class="etiqueta-radio" for="casa_sin_patio">Casa sin patio</label>
                                </div>
                                <div class="opcion-vivienda">
                                    <input type="radio" name="tipo_vivienda" value="Departamento chico" id="depto_chico">
                                    <label class="etiqueta-radio" for="depto_chico">Departamento chico</label>
                                </div>
                                <div class="opcion-vivienda">
                                    <input type="radio" name="tipo_vivienda" value="Departamento grande" id="depto_grande">
                                    <label class="etiqueta-radio" for="depto_grande">Departamento grande</label>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="contenedor-botones-formulario">
                            <button type="submit" class="btn boton-enviar-formulario">Enviar formulario</button>
                            <a href="../detalle_mascota.php?id=<?php echo $id_mascota; ?>" class="btn boton-cancelar-formulario">Cancelar</a>
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