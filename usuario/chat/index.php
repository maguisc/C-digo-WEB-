<?php
$from_chat = true;
include '../includes/header.php';
include '../../config/database.php';
include '../auth/verificar_sesion.php';

// Validación
if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

$idChat = $_GET['id'];

// Verificación
$consulta = "SELECT 1 FROM chats WHERE id_chat = ? AND id_usuario = ?";
$stmt = $conn->prepare($consulta);
$stmt->bind_param("ii", $idChat, $_SESSION['usuario_id']);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    header('Location: ../index.php');
    exit;
}
?>

<style>
/* Estilos simplificados */
.mensaje-usuario {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 10px;
}

.mensaje-admin {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 10px;
}

.burbuja-usuario {
  background-color: #e88861;
  color: white;
  border-radius: 15px;
  padding: 10px 15px;
  max-width: 70%;
  word-wrap: break-word;
}

.burbuja-admin {
  background-color: #e8e8e8;
  color: #333;
  border-radius: 15px;
  padding: 10px 15px;
  max-width: 70%;
  word-wrap: break-word;
}

.hora-mensaje {
  font-size: 11px;
  opacity: 0.7;
  text-align: right;
  margin-top: 4px;
}

.imagen-mensaje {
  max-width: 100%;
  border-radius: 8px;
  cursor: pointer;
}

.chat-card {
  border-radius: 10px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.chat-intro {
  background-color: #fff0e6;
  border-radius: 10px;
  padding: 15px;
  margin-bottom: 20px;
}

.pata-icon {
  color: #e88861;
}
</style>

<div class="container mt-4">
    <!-- Panel informativo -->
    <div class="chat-intro">
        <h5><i class="fas fa-paw pata-icon"></i> ¿Perdiste o encontraste una mascota?</h5>
        <p>Contanos todo: si la mascota se perdió o la encontraste, cómo es (raza, color, tamaño), en qué zona fue, y cualquier seña particular que nos ayude a identificarla.</p>
        <p>Las fotos suman un montón. Nuestro equipo está atento para responder lo antes posible ¡Gracias por confiar en nosotros!</p>
    </div>

    <!-- Chat -->
    <div class="card chat-card">
        <div class="card-header">
            <h5>Comunicate con Adoptame Saladillo</h5>
        </div>
        <div class="card-body chat-messages" id="chat-body">
            <div id="chat-mensajes">
                <!-- Aquí se cargan los mensajes -->
            </div>
        </div>
        <div class="card-footer">
            <form id="chat-form">
                <div class="input-group">
                    <input type="file" id="imagen-input" accept="image/*" style="display: none">
                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('imagen-input').click()">
                        <i class="fas fa-image"></i>
                    </button>
                    <input type="text" class="form-control" id="mensaje-input" placeholder="Escribí tu mensaje...">
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Socket.IO -->
<script src="https://cdn.socket.io/4.4.1/socket.io.min.js"></script>

<script>
// Configuración
const idChat = <?php echo $idChat; ?>;
const idUsuario = <?php echo $_SESSION['usuario_id']; ?>;
const nombreUsuario = "<?php echo $_SESSION['usuario_nombre']; ?>";
const SOCKET_URL = 'http://192.168.18.24:3000';
let idUltimoMensaje = 0;
let socket;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    // Altura del chat
    document.getElementById('chat-body').style.height = '400px';
    
    // Cargar mensajes e iniciar socket
    cargarMensajes();
    iniciarSocket();
    
    // Actualización periódica
    setInterval(function() {
        verificarNuevosMensajes();
    }, 3000);
});

// Iniciar Socket.IO
function iniciarSocket() {
    console.log("Iniciando Socket.IO...");
    
    socket = io(SOCKET_URL);
    
    socket.on('connect', function() {
        console.log('Conectado a Socket.IO:', socket.id);
        
        // Notificar conexión
        socket.emit('usuario_conectado', {
            id_usuario: idUsuario,
            nombre_usuario: nombreUsuario
        });
        
        // Unirse a sala
        socket.emit('unirse_sala', idChat);
    });
    
    // Escuchar mensajes nuevos
    socket.on('nuevo_mensaje', function(datos) {
        console.log('Nuevo mensaje recibido:', datos);
        if (datos.id_chat == idChat) {
            verificarNuevosMensajes();
        }
    });
}

// Cargar mensajes iniciales
function cargarMensajes() {
    fetch(`cargar_mensajes.php?id_chat=${idChat}`)
        .then(respuesta => respuesta.json())
        .then(datos => {
            console.log("Mensajes cargados:", datos);
            if (!datos || datos.length === 0) return;
            
            mostrarMensajes(datos);
            
            // Actualizar ID del último mensaje
            if (datos.length > 0) {
                idUltimoMensaje = Math.max(...datos.map(m => parseInt(m.id_mensaje)));
            }
        })
        .catch(error => {
            console.error('Error cargando mensajes:', error);
        });
}

// Verificar mensajes nuevos
function verificarNuevosMensajes() {
    const timestamp = new Date().getTime();
    fetch(`cargar_mensajes.php?id_chat=${idChat}&t=${timestamp}`)
        .then(respuesta => respuesta.json())
        .then(datos => {
            if (!datos || datos.length === 0) return;
            
            // Verificar si hay mensajes nuevos
            const ultimoMensajeRecibido = Math.max(...datos.map(m => parseInt(m.id_mensaje)));
            
            if (ultimoMensajeRecibido > idUltimoMensaje) {
                console.log(`Hay mensajes nuevos`);
                mostrarMensajes(datos);
                idUltimoMensaje = ultimoMensajeRecibido;
            }
        })
        .catch(error => {
            console.error('Error verificando mensajes:', error);
        });
}

// Mostrar mensajes
function mostrarMensajes(mensajes) {
    const contenedor = document.getElementById('chat-mensajes');
    contenedor.innerHTML = '';
    
    mensajes.forEach(mensaje => {
        const esAdmin = mensaje.tipo_emisor === 'admin';
        const div = document.createElement('div');
        div.className = esAdmin ? 'mensaje-admin' : 'mensaje-usuario';
        
        let contenido = `<div class="${esAdmin ? 'burbuja-admin' : 'burbuja-usuario'}">`;
        
        // Mostrar imagen o texto
        if (mensaje.imagen_url) {
            contenido += `<img src="../../${mensaje.imagen_url}" class="imagen-mensaje" onclick="window.open('../../${mensaje.imagen_url}', '_blank')">`;
        }
        
        if (mensaje.mensaje && mensaje.mensaje !== 'Imagen enviada') {
            contenido += `${mensaje.mensaje}`;
        }
        
        // Mostrar hora
        const hora = new Date(mensaje.fecha_envio).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        contenido += `<div class="hora-mensaje">${hora}</div>`;
        
        contenido += '</div>';
        
        div.innerHTML = contenido;
        contenedor.appendChild(div);
    });
    
    // Scroll al final
    const chatBox = document.getElementById('chat-body');
    chatBox.scrollTop = chatBox.scrollHeight;
}

// Enviar mensaje de texto
document.getElementById('chat-form').addEventListener('submit', function(evento) {
    evento.preventDefault();
    
    const mensaje = document.getElementById('mensaje-input').value.trim();
    
    if (!mensaje) return;
    
    const datos = new FormData();
    datos.append('id_chat', idChat);
    datos.append('mensaje', mensaje);
    
    const boton = document.querySelector('#chat-form button[type="submit"]');
    boton.disabled = true;
    
    fetch('guardar_mensaje.php', {
        method: 'POST',
        body: datos
    })
    .then(respuesta => respuesta.json())
    .then(datos => {
        if (datos.success) {
            document.getElementById('mensaje-input').value = '';
            
            // Emitir mensaje por Socket.IO
            if (socket && socket.connected) {
                socket.emit('mensaje_usuario', {
                    id_usuario: idUsuario,
                    nombre_usuario: nombreUsuario,
                    mensaje: mensaje
                });
            }
            
            // Actualizar mensajes
            verificarNuevosMensajes();
        }
        boton.disabled = false;
    })
    .catch(error => {
        console.error('Error enviando mensaje:', error);
        boton.disabled = false;
    });
});

// Subir imagen
document.getElementById('imagen-input').addEventListener('change', function(evento) {
    const archivo = evento.target.files[0];
    if (!archivo) return;
    
    const datos = new FormData();
    datos.append('image', archivo);
    datos.append('chat_id', idChat);
    
    fetch('subir_imagen.php', {
        method: 'POST',
        body: datos
    })
    .then(respuesta => respuesta.json())
    .then(datos => {
        if (datos.success) {
            const datosMensaje = new FormData();
            datosMensaje.append('id_chat', idChat);
            datosMensaje.append('mensaje', '[Imagen]');
            datosMensaje.append('imagen_url', datos.url);
            
            // Guardar mensaje con imagen
            return fetch('guardar_mensaje.php', {
                method: 'POST',
                body: datosMensaje
            });
        }
    })
    .then(respuesta => {
        if (respuesta) return respuesta.json();
    })
    .then(datos => {
        if (datos && datos.success) {
            // Notificar
            if (socket && socket.connected) {
                socket.emit('mensaje_usuario', {
                    id_usuario: idUsuario,
                    nombre_usuario: nombreUsuario,
                    mensaje: '[Imagen]'
                });
            }
            
            // Actualizar
            verificarNuevosMensajes();
        }
        document.getElementById('imagen-input').value = '';
    })
    .catch(error => {
        console.error('Error subiendo imagen:', error);
        document.getElementById('imagen-input').value = '';
    });
});
</script>

<?php 
include "../includes/sidebar.php";
?>