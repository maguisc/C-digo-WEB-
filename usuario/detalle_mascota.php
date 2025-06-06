<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../config/database.php';
include 'includes/header.php';

// Redirigir si no hay ID de mascota
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_mascota = $_GET['id'];

// Obtener detalles de la mascota
$sql = "SELECT m.*, p.tipo_publicacion 
        FROM mascotas m 
        INNER JOIN publicaciones p ON m.id_mascota = p.id_mascota 
        WHERE m.id_mascota = ? AND p.estado_publicacion = 'Activa'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mascota);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$mascota = $result->fetch_assoc();
?>

<div class="container">
    <div class="row detalle-mascota-container">
        <!-- Imagen de la mascota -->
        <div class="col-md-6">
            <img src="<?php echo '../' . $mascota['imagen']; ?>" 
                class="detalle-mascota-imagen" 
                alt="<?php echo $mascota['nombre']; ?>">
        </div>
        
        <!-- Información y botones -->
        <div class="col-md-6">
            <div class="card detalle-mascota-card">
                <div class="card-header">
                    <h4>DESCRIPCIÓN</h4>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Nombre:</th>
                            <td><?php echo $mascota['nombre']; ?></td>
                        </tr>
                        <tr>
                            <th>Tipo:</th>
                            <td><?php echo $mascota['tipo']; ?></td>
                        </tr>
                        <tr>
                            <th>Género:</th>
                            <td><?php echo $mascota['genero']; ?></td>
                        </tr>
                        <tr>
                            <th>Raza:</th>
                            <td><?php echo $mascota['raza']; ?></td>
                        </tr>
                        <tr>
                            <th>Tamaño:</th>
                            <td><?php echo $mascota['tamano']; ?></td>
                        </tr>
                        <tr>
                            <th>Edad:</th>
                            <td><?php echo $mascota['edad']; ?></td>
                        </tr>
                        <tr>
                            <th>Castrado:</th>
                            <td><?php echo $mascota['castrado']; ?></td>
                        </tr>
                        <tr>
                            <th>Vacunado:</th>
                            <td><?php echo $mascota['vacunado']; ?></td>
                        </tr>
                    </table>

                    <!-- Botón de acción según tipo de publicación -->
                    <?php if($mascota['tipo_publicacion'] == 'Adopción'): ?>
                        <button class="btn btn-primary" onclick="location.href='formularios/formulario_adopcion.php?id=<?php echo $id_mascota; ?>'">
                            Enviar formulario de Adopción
                        </button>

                    <?php elseif($mascota['tipo_publicacion'] == 'Tránsito'): ?>
                        <button class="btn btn-primary" onclick="location.href='formularios/formulario_transito.php?id=<?php echo $id_mascota; ?>'">
                            Enviar formulario de Tránsito
                        </button>

                    <?php else: ?>
                        <button class="btn btn-primary" onclick="location.href='formularios/contactar.php?id=<?php echo $id_mascota; ?>'">
                            Preguntar por <?php echo $mascota['nombre']; ?>
                        </button>
                        
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include 'includes/sidebar.php';
?>