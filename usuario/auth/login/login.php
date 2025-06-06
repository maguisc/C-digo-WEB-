<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios
include '../../includes/header.php';
include '../../../config/database.php';

// Verificar si ya hay una sesión activa
if (isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Variables para mensajes
$error = '';
$success = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Limpiar y validar datos de entrada
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del correo electrónico no es válido";
    } else {
        // Preparar la consulta
        $sql = "SELECT id_usuario, nombre, email, password, rol FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            
            // Verificar la contraseña
            if (password_verify($password, $usuario['password'])) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                
                // Redirigir según el rol
                if ($usuario['rol'] === 'admin') {
                    header("Location: /mascotas/admin/index.php");
                } else {
                    header("Location: /mascotas/usuario/index.php");
                }
                exit();
            } else {
                $error = "Contraseña incorrecta";
            }
        } else {
            $error = "No existe un usuario con ese correo";
        }
        $stmt->close();
    }
}

// Recuperar mensajes de sesión si existen
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Cerrar conexión a la base de datos si está abierta
if (isset($conn) && $conn) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Adoptame Saladilo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../../css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <img src="../../../uploads/mascostasinicio.png" alt="Perro y gato" class="img-fluid w-100 h-100" style="object-fit: cover;">
        </div>
        <div class="login-form">
            <div class="login-box">
                <h2 class="text-center mb-4">Inicio de Sesión</h2>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-login">Iniciar Sesión</button>
                    <div class="text-center mt-3 login-footer">
                        <p>¿No tenes una cuenta? <a href="../registro/registro.php">Regístrate</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
