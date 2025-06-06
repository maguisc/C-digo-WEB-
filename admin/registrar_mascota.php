<?php
// Arranco la sesión si no está activa
session_status() == PHP_SESSION_NONE && session_start();

// Incluyo los archivos necesarios
include '../config/database.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/auth/verificar_sesion.php';

// Busco datos de la mascota si es edición
$mascota = null;
if (isset($_GET['id'])) {
    $id_mascota = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM mascotas WHERE id_mascota = ?");
    $stmt->bind_param("i", $id_mascota);
    $stmt->execute();
    $mascota = $stmt->get_result()->fetch_assoc();
}
?>

<div class="content">
    <h2><?= $mascota ? 'Editar Mascota' : 'Registrar Nueva Mascota' ?></h2>
    <form action="guardar_mascota.php" method="POST" enctype="multipart/form-data">
        <?php if ($mascota): ?>
            <input type="hidden" name="id_mascota" value="<?= $mascota['id_mascota'] ?>">
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                        value="<?= $mascota ? $mascota['nombre'] : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-control" id="tipo" name="tipo" required>
                        <option value="Perro" <?= ($mascota && $mascota['tipo'] == 'Perro') ? 'selected' : '' ?>>Perro</option>
                        <option value="Gato" <?= ($mascota && $mascota['tipo'] == 'Gato') ? 'selected' : '' ?>>Gato</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="genero" class="form-label">Género</label>
                    <select class="form-control" id="genero" name="genero" required>
                        <option value="Macho" <?= ($mascota && $mascota['genero'] == 'Macho') ? 'selected' : '' ?>>Macho</option>
                        <option value="Hembra" <?= ($mascota && $mascota['genero'] == 'Hembra') ? 'selected' : '' ?>>Hembra</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="raza" class="form-label">Raza</label>
                    <input type="text" class="form-control" id="raza" name="raza" 
                        value="<?= $mascota ? $mascota['raza'] : '' ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="tamano" class="form-label">Tamaño</label>
                    <select class="form-control" id="tamano" name="tamano" required>
                        <option value="Pequeño" <?= ($mascota && $mascota['tamano'] == 'Pequeño') ? 'selected' : '' ?>>Chico</option>
                        <option value="Mediano" <?= ($mascota && $mascota['tamano'] == 'Mediano') ? 'selected' : '' ?>>Mediano</option>
                        <option value="Grande" <?= ($mascota && $mascota['tamano'] == 'Grande') ? 'selected' : '' ?>>Grande</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="edad" class="form-label">Edad</label>
                    <input type="text" class="form-control" id="edad" name="edad" 
                        value="<?= $mascota ? $mascota['edad'] : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="castrado" class="form-label">Castrado</label>
                    <select class="form-control" id="castrado" name="castrado" required>
                        <option value="Si" <?= ($mascota && $mascota['castrado'] == 'Si') ? 'selected' : '' ?>>Sí</option>
                        <option value="No" <?= ($mascota && $mascota['castrado'] == 'No') ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="vacunado" class="form-label">Vacunado</label>
                    <select class="form-control" id="vacunado" name="vacunado" required>
                        <option value="Si" <?= ($mascota && $mascota['vacunado'] == 'Si') ? 'selected' : '' ?>>Sí</option>
                        <option value="No" <?= ($mascota && $mascota['vacunado'] == 'No') ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
            </div>
            <div class="col-12">
                <div class="mb-3">
                    <label for="imagen" class="form-label">Imagen</label>
                    <?php if ($mascota && $mascota['imagen']): ?>
                        <div class="mb-3">
                            <img src="../<?= $mascota['imagen'] ?>" alt="Imagen actual" 
                                style="max-width: 200px; margin-bottom: 10px; display: block;">
                            <p class="text-muted">Selecciona una nueva imagen solo si deseas cambiar la actual</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="imagen" name="imagen" 
                        accept="image/*" <?= $mascota ? '' : 'required' ?>>
                </div>
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-control" id="estado" name="estado" required>
                        <option value="Adopción" <?= ($mascota && $mascota['estado'] == 'Adopción') ? 'selected' : '' ?>>Adopción</option>
                        <option value="Tránsito" <?= ($mascota && $mascota['estado'] == 'Tránsito') ? 'selected' : '' ?>>Tránsito</option>
                        <option value="Perdido" <?= ($mascota && $mascota['estado'] == 'Perdido') ? 'selected' : '' ?>>Perdido</option>
                    </select>
                </div>
                <div class="d-inline-flex gap-2">
                    <button type="submit" class="btn btn-nueva">
                        <?= $mascota ? 'Actualizar' : 'Guardar' ?>
                    </button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Validación de tipo de archivo de imagen
document.getElementById('imagen').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && !file.type.startsWith('image/')) {
        alert('Por favor, selecciona un archivo de imagen válido.');
        this.value = '';
    }
});
</script>

</body>
</html>