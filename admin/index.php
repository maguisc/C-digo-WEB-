<?php
// Incluyo los archivos necesarios
include '../config/database.php';
include 'includes/sidebar.php';
include 'auth/verificar_sesion.php';

// Filtro por estado
$estadoSeleccionado = $_GET['estado'] ?? 'Todas';

// Armo la consulta según el filtro
$sql = "SELECT * FROM mascotas";
if ($estadoSeleccionado === "Sin publicar") {
    $sql .= " WHERE estado IS NULL OR estado = ''";
} elseif ($estadoSeleccionado !== "Todas") {
    $sql .= " WHERE estado = '" . $conn->real_escape_string($estadoSeleccionado) . "'";
}
$sql .= " ORDER BY id_mascota DESC";

// Ejecuto la consulta
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mascotas - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content">
    <h2>Mascotas</h2>
    <a href="registrar_mascota.php" class="btn btn-nueva">Nueva</a>
    
    <form method="GET" style="margin-bottom: 15px;">
        <label for="estado">Filtrar por estado:</label>
        <select name="estado" id="estado" onchange="this.form.submit()">
            <option value="Todas" <?= ($_GET['estado'] ?? 'Todas') == 'Todas' ? 'selected' : '' ?>>Todas</option>
            <option value="Sin publicar" <?= ($_GET['estado'] ?? '') == 'Sin publicar' ? 'selected' : '' ?>>Sin publicar</option>
            <option value="Adopción" <?= ($_GET['estado'] ?? '') == 'Adopción' ? 'selected' : '' ?>>Adopción</option>
            <option value="Tránsito" <?= ($_GET['estado'] ?? '') == 'Tránsito' ? 'selected' : '' ?>>Tránsito</option>
            <option value="Perdido" <?= ($_GET['estado'] ?? '') == 'Perdido' ? 'selected' : '' ?>>Perdido</option>
        </select>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID Mascota</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Edad</th>
                    <th>Estado</th>
                    <th>Crear publicación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row["id_mascota"] ?></td>
                            <td><img src="../<?= $row["imagen"] ?>" alt="Imagen de <?= $row["nombre"] ?>" 
                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                            <td><?= $row["nombre"] ?></td>
                            <td><?= $row["edad"] ?></td>
                            <td><?= $row["estado"] ?></td>
                            <td>
                                <button class="btn btn-publicar" 
                                    onclick="abrirModalPublicacion(<?= $row["id_mascota"] ?>, '<?= addslashes($row["nombre"]) ?>')">
                                    Publicar
                                </button>
                                <!-- Eliminar publicación como botón amarillo -->
                                <button class="btn btn-warning btn-sm btn-eliminar-publicacion"
                                    onclick="eliminarPublicacion(<?= $row["id_mascota"] ?>, this)">
                                    Eliminar publicación
                                </button>
                            </td>
                            <td>
                                <button class="btn btn-editar" onclick="editarMascota(<?= $row["id_mascota"] ?>)">Editar</button>
                                <button class="btn btn-eliminar" onclick="eliminarMascota(<?= $row["id_mascota"] ?>)">Eliminar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7">No se encontraron resultados</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de publicación -->
<div class="modal fade" id="modalPublicacion" tabindex="-1" aria-labelledby="modalPublicacionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPublicacionLabel">Crear Publicación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Seleccione el tipo de publicación para <span id="nombreMascota"></span>:</p>
                <input type="hidden" id="mascotaId" value="">
                <div class="d-grid gap-2">
                    <button class="btn btn-primary mb-2" onclick="crearPublicacion('Adopción')">Mascota en Adopción</button>
                    <button class="btn btn-info mb-2" onclick="crearPublicacion('Tránsito')">Mascota en Tránsito</button>
                    <button class="btn btn-warning" onclick="crearPublicacion('Perdido')">Mascota Perdida</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función para abrir el modal de publicación
function abrirModalPublicacion(id, nombre) {
    document.getElementById('mascotaId').value = id;
    document.getElementById('nombreMascota').textContent = nombre;
    new bootstrap.Modal(document.getElementById('modalPublicacion')).show();
}

// Función para crear una publicación
function crearPublicacion(tipo) {
    const mascotaId = document.getElementById('mascotaId').value;
    
    fetch('crear_publicacion.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_mascota=${mascotaId}&tipo=${tipo}`
    })
    .then(response => response.json())
    .then(data => {
        alert(data.success ? 'Publicación creada exitosamente' : 'Error: ' + data.message);
        data.success && location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al crear la publicación');
    });
}

// Función para eliminar una mascota
function eliminarMascota(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta mascota?')) {
        fetch('guardar_mascota.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `accion=eliminar&id_mascota=${id}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            data.success && window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar la mascota');
        });
    }
}

// Función para editar una mascota
function editarMascota(id) {
    window.location.href = `registrar_mascota.php?id=${id}`;
}

// Nueva función para eliminar publicación con alerta de éxito
function eliminarPublicacion(id, btn) {
    if (!confirm('¿Estás seguro de que deseas eliminar la publicación?')) return;
    fetch(`eliminar_publicacion.php?id_mascota=${id}`)
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert('Publicación eliminada con éxito');
          btn.disabled = true;
        } else {
          alert('No se pudo eliminar la publicación');
        }
      })
      .catch(() => alert('Error de red'));
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

