<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../../../config/database.php';
include '../../includes/header.php';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Verificar si el email ya está registrado
    $sql_check = "SELECT COUNT(*) as total FROM usuarios WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();

    if($row['total'] > 0) {
        $_SESSION['error'] = "Este correo electrónico ya está registrado";
    } else {
        // Validar contraseña
        if (!(
            strlen($password) >= 8 &&
            preg_match('/[A-Z]/', $password) &&
            preg_match('/[a-z]/', $password) &&
            preg_match('/[0-9]/', $password) &&
            preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)
        )) {
            $_SESSION['error'] = "La contraseña no cumple con los requisitos de seguridad";
        } else {
            // Encriptar contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar usuario
            $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $nombre, $email, $password_hash);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Registro exitoso. Por favor, inicia sesión.";
                header("Location: ../login/login.php");
                exit();
            } else {
                $_SESSION['error'] = "Error al registrar: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Cerrar conexión a la base de datos si está abierta
if (isset($conn) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Usuario - Adoptame Saladillo</title>
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
                <h2 class="text-center mb-4">Registro de Usuario</h2>
                <?php
                if(isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                    unset($_SESSION['error']);
                }
                ?>
                <form id="registroForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" onsubmit="return validarFormulario()">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div id="passwordHelp" class="form-text">
                            La contraseña debe contener:
                            <ul class="requirements-list">
                                <li id="length" class="invalid">Mínimo 8 caracteres</li>
                                <li id="uppercase" class="invalid">Una letra mayúscula</li>
                                <li id="lowercase" class="invalid">Una letra minúscula</li>
                                <li id="number" class="invalid">Un número</li>
                                <li id="special" class="invalid">Un carácter especial</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-registrate">Registrarse</button>
                    </div>
                    <div class="text-center mt-3">
                        <p>¿Ya estás registrado? <a href="../login/login.php">Iniciar Sesión</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function validarPassword(password) {
        const requisitos = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        for (const [requisito, cumplido] of Object.entries(requisitos)) {
            const li = document.getElementById(requisito);
            if (cumplido) {
                li.classList.remove('invalid');
                li.classList.add('valid');
            } else {
                li.classList.remove('valid');
                li.classList.add('invalid');
            }
        }

        return Object.values(requisitos).every(Boolean);
    }

    function validarFormulario() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (!validarPassword(password)) {
            alert('La contraseña no cumple con todos los requisitos');
            return false;
        }

        if (password !== confirmPassword) {
            alert('Las contraseñas no coinciden');
            return false;
        }

        return true;
    }

    document.getElementById('password').addEventListener('input', function() {
        validarPassword(this.value);
    });
    </script>
</body>
</html>