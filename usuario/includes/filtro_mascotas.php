<?php 
include '../config/database.php';

// Obtener parámetros de filtro
$nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$edad = isset($_GET['edad']) ? $_GET['edad'] : '';
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
?>

<div class="container mt-4">
    <!-- Formulario de filtros -->
    <div class="contenedor-filtros">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="nombre" class="campo-filtro" placeholder="Nombre" value="<?php echo htmlspecialchars($nombre); ?>">
            </div>
            <div class="col-md-4">
                <input type="text" name="edad" class="campo-filtro" placeholder="Edad" value="<?php echo htmlspecialchars($edad); ?>">
            </div>
            <div class="col-md-3">
                <select name="tipo" class="campo-filtro">
                <option value="">Todos</option>
                <option value="Perro" <?php if (strtolower($tipo) == 'perro') echo 'selected'; ?>>Perro</option>
                <option value="Gato" <?php if (strtolower($tipo) == 'gato') echo 'selected'; ?>>Gato</option>
                </select>
            </div>
            <div class="col-md-1 text-end">
                <button type="submit" class="boton-filtrar w-100">Filtrar</button>
            </div>
        </form>
    </div>

    <!-- Listado de mascotas -->
    <div class="grilla-mascotas">
        <?php
        // Consulta SQL con filtros
        $sql = "SELECT m.*, p.fecha_publicacion 
                FROM mascotas m 
                INNER JOIN publicaciones p ON m.id_mascota = p.id_mascota 
                WHERE p.tipo_publicacion = '" . $conn->real_escape_string($tipoPublicacion) . "' 
                AND p.estado_publicacion = 'Activa'";

        // Aplicar filtros
        if (!empty($nombre)) {
            $sql .= " AND m.nombre LIKE '%" . $conn->real_escape_string($nombre) . "%'";
        }

        if (!empty($edad)) {
            $sql .= " AND m.edad LIKE '%" . $conn->real_escape_string($edad) . "%'";
        }

        if (!empty($tipo)) {
            $sql .= " AND LOWER(m.tipo) = LOWER('" . $conn->real_escape_string($tipo) . "')";
        }

        $result = $conn->query($sql);

        // Mostrar resultados
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
        ?>
            <div class="tarjeta-mascota">
                <a href="detalle_mascota.php?id=<?php echo $row['id_mascota']; ?>" class="text-decoration-none">
                    <img class="mascota-imagen" src="<?php echo '../' . $row['imagen']; ?>" alt="<?php echo $row['nombre']; ?>">
                    <div class="tarjeta-cuerpo">
                        <h5 class="tarjeta-mascota-titulo"><?php echo $row['nombre']; ?></h5>
                        <p class="tarjeta-mascota-texto"><?php echo $row['edad']; ?></p>
                        
                        <?php if ($tipoPublicacion == 'Adopción'): ?>
                            <button class="boton-adoptar">Adoptar</button>
                        <?php elseif ($tipoPublicacion == 'Tránsito'): ?>
                            <button class="boton-transito">Dar tránsito</button>
                        <?php elseif ($tipoPublicacion == 'Perdido'): ?>
                            <button class="boton-consultar">Consultar por <?php echo $row['nombre']; ?></button>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        <?php
            }
        } else {
            echo "<div class='sin-resultados'>No se encontraron mascotas que coincidan con los filtros.</div>";
        }
        ?>
    </div>
</div>