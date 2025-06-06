<?php
include '../../config/database.php';
include '../auth/verificar_sesion.php';

// Configuro respuesta JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo = $_POST['tipo'];
    $id_formulario = $_POST['id'];
    $nuevo_estado = $_POST['estado'];
    
    // Determino la tabla según el tipo
    $tabla = match($tipo) {
        'Adopción' => 'formularios_adopcion',
        'Tránsito' => 'formularios_transito',
        'Contacto' => 'formularios_contacto',
        default => ''
    };

    // Me fijo que la tabla sea válida
    if ($tabla === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Tipo de formulario no válido'
        ]);
        exit;
    }

    // Defino estados válidos según el tipo
    $estados_validos = $tipo == 'Contacto' 
        ? ['Pendiente', 'Respondido', 'Cerrado'] 
        : ['Pendiente', 'Aprobado', 'Rechazado'];

    // Valido que el estado sea correcto
    if (!in_array($nuevo_estado, $estados_validos)) {
        echo json_encode([
            'success' => false,
            'message' => 'Estado no válido para este tipo de formulario'
        ]);
        exit;
    }
    
    // Determino el campo ID
    $campo_id = $tipo == 'Contacto' ? 'id_contacto' : 'id_formulario';
    
    // Actualizo el estado
    $sql = "UPDATE $tabla SET estado = ? WHERE $campo_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $id_formulario);
    
    // Ejecuto y respondo
    echo json_encode([
        'success' => $stmt->execute(),
        'message' => $stmt->execute() ? '' : 'Error al actualizar el estado'
    ]);
    
    $stmt->close();
    $conn->close();
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>