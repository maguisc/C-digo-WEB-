<?php
include '../../config/database.php';
include '../includes/sidebar.php';
include '../auth/verificar_sesion.php';

// Consulta para obtener todas las solicitudes
$sql = "SELECT 
            'Adopción' as tipo_solicitud,
            f.id_formulario,
            f.fecha_solicitud,
            f.estado,
            f.id_mascota,
            f.id_usuario,
            f.nombre_completo,
            f.email,
            m.nombre as nombre_mascota, 
            m.imagen as imagen_mascota,
            u.nombre as nombre_usuario,
            u.email as email_usuario
        FROM formularios_adopcion f
        LEFT JOIN mascotas m ON f.id_mascota = m.id_mascota
        LEFT JOIN usuarios u ON f.id_usuario = u.id_usuario
        
        UNION ALL
        
        SELECT 
            'Tránsito' as tipo_solicitud,
            t.id_formulario,
            t.fecha_solicitud,
            t.estado,
            t.id_mascota,
            t.id_usuario,
            t.nombre_completo,
            t.email,
            m.nombre as nombre_mascota, 
            m.imagen as imagen_mascota,
            u.nombre as nombre_usuario,
            u.email as email_usuario
        FROM formularios_transito t
        LEFT JOIN mascotas m ON t.id_mascota = m.id_mascota
        LEFT JOIN usuarios u ON t.id_usuario = u.id_usuario
        
        UNION ALL
        
        SELECT 
            'Contacto' as tipo_solicitud,
            c.id_contacto as id_formulario,
            c.fecha_contacto as fecha_solicitud,
            c.estado,
            c.id_mascota,
            c.id_usuario,
            c.nombre_completo,
            c.email,
            m.nombre as nombre_mascota, 
            m.imagen as imagen_mascota,
            u.nombre as nombre_usuario,
            u.email as email_usuario
        FROM formularios_contacto c
        LEFT JOIN mascotas m ON c.id_mascota = m.id_mascota
        LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario
        
        ORDER BY fecha_solicitud DESC";

// Ejecuto la consulta
$result = mysqli_query($conn, $sql);
?>

<div class="content">
    <div class="container-fluid p-4">
        <h2 class="titulo-solicitudes mb-4">Gestión de Solicitudes</h2>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-solicitudes">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Mascota</th>
                                <th>Solicitante</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($row['fecha_solicitud'])) ?></td>
                                        <td>
                                            <span class="badge <?= $row['tipo_solicitud'] == 'Adopción' ? 'bg-primary' : 
                                                ($row['tipo_solicitud'] == 'Tránsito' ? 'bg-info' : 'bg-warning') ?>">
                                                <?= $row['tipo_solicitud'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="../../<?= $row['imagen_mascota'] ?>" 
                                                    alt="<?= $row['nombre_mascota'] ?>" 
                                                    class="mascota-imagen me-2" 
                                                    style="max-width: 120px; max-height: 80px; object-fit: cover; border-radius: 8px;" />
                                                <span class="fw-semibold"><?= $row['nombre_mascota'] ?></span>
                                            </div>
                                        </td>
                                        <td class="text-break"><?= $row['email_usuario'] ?></td>
                                        <td>
                                            <span class="badge <?= match($row['estado']) {
                                                'Pendiente' => 'bg-warning',
                                                'Aprobado' => 'bg-success',
                                                'Cerrado' => 'bg-secondary',
                                                'Rechazado' => 'bg-danger',
                                                default => 'bg-secondary'
                                            } ?>">
                                                <?= $row['estado'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-info btn-sm" 
                                                    onclick="verDetalles('<?= $row['tipo_solicitud'] ?>', <?= $row['id_formulario'] ?>)">
                                                Ver detalles
                                            </button>
                                            <?php if($row['estado'] == 'Pendiente'): ?>
                                                <?php if($row['tipo_solicitud'] == 'Contacto'): ?>
                                                    <button class="btn btn-secondary btn-sm" 
                                                            onclick="actualizarEstado('<?= $row['tipo_solicitud'] ?>', <?= $row['id_formulario'] ?>, 'Cerrado')">
                                                        Cerrar consulta
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-success btn-sm" 
                                                            onclick="actualizarEstado('<?= $row['tipo_solicitud'] ?>', <?= $row['id_formulario'] ?>, 'Aprobado')">
                                                        Aprobar
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" 
                                                            onclick="actualizarEstado('<?= $row['tipo_solicitud'] ?>', <?= $row['id_formulario'] ?>, 'Rechazado')">
                                                        Rechazar
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="sin-solicitudes">No hay solicitudes pendientes</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalles -->
<div class="modal fade" id="detallesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detallesContenido"></div>
        </div>
    </div>
</div>

<script>
// Función para ver detalles de una solicitud
function verDetalles(tipo, idFormulario) {
    fetch('ver_solicitud.php?tipo=' + tipo + '&id=' + idFormulario)
        .then(response => response.text())
        .then(data => {
            document.getElementById('detallesContenido').innerHTML = data;
            new bootstrap.Modal(document.getElementById('detallesModal')).show();
        });
}

// Función para actualizar estado de una solicitud
function actualizarEstado(tipo, idFormulario, nuevoEstado) {
    // Armo el mensaje según el tipo
    const mensaje = tipo == 'Contacto' 
        ? '¿Estás seguro de que deseas cerrar esta consulta?'
        : '¿Estás seguro de que deseas ' + nuevoEstado.toLowerCase() + ' esta solicitud?';

    if(confirm(mensaje)) {
        fetch('actualizar_estado.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'tipo=' + tipo + '&id=' + idFormulario + '&estado=' + nuevoEstado
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error al actualizar el estado: ' + data.message);
            }
        });
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>