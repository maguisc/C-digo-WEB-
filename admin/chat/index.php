<?php
session_start();
include_once '../includes/sidebar.php';
include_once(__DIR__ . '/../../config/database.php');

// Consulta para obtener chats activos
$consulta = $conn->query("SELECT email_usuario, nombre_usuario, MAX(id_chat) as id_chat, 
                          MAX(fecha_actualizacion) as fecha_actualizacion, ultimo_mensaje 
                          FROM chats 
                          GROUP BY email_usuario 
                          ORDER BY fecha_actualizacion DESC");
$chats = $consulta->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid mt-4">
  <div class="row">
    <div class="col-md-3"></div>
    
    <!-- Lista de chats -->
    <div class="col-md-3">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h4>Chats Activos</h4>
        </div>
        <div class="card-body p-0">
          <div style="max-height: 500px; overflow-y: auto;">
            <ul class="list-group" id="lista-chats">
              <?php foreach ($chats as $chat): ?>
              <li class="list-group-item chat-item" data-id="<?= $chat['id_chat'] ?>" data-email="<?= $chat['email_usuario'] ?>">
                <b><?= $chat['nombre_usuario'] ?></b><br>
                <small><?= $chat['email_usuario'] ?></small>
                <?php if (!empty($chat['ultimo_mensaje'])): ?>
                <p class="mb-0 text-muted"><small><?= $chat['ultimo_mensaje'] ?></small></p>
                <?php endif; ?>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel del chat -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h4>Chat</h4>
        </div>
        <div class="card-body">
          <div id="chat-box" style="height: 400px; overflow-y: auto; background-color: white; padding: 10px;"></div>
          
          <!-- Formulario -->
          <form id="chat-form" class="mt-3">
            <input type="hidden" name="id_chat" id="chat-id">
            <input type="hidden" name="email_usuario" id="email-usuario">
            <input type="hidden" name="imagen_url" id="imagen-url" value="">
            
            <div class="input-group mb-2">
              <input type="text" class="form-control" id="mensaje-input" name="mensaje" placeholder="Escribe un mensaje...">
              <button type="submit" class="btn btn-primary">Enviar</button>
            </div>
            
            <div>
              <button type="button" id="imagen-btn" class="btn btn-secondary btn-sm">Adjuntar imagen</button>
              <input type="file" id="imagen-input" style="display: none;" accept="image/jpeg,image/png,image/gif">
              <div id="imagen-preview" class="d-none mt-2">
                <img src="" style="max-height: 40px;">
                <button type="button" id="quitar-imagen" class="btn btn-sm btn-danger">X</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Socket.IO -->
<script src="https://cdn.socket.io/4.4.1/socket.io.min.js"></script>

<script>
// Variables globales
const TIEMPO_ACTUALIZACION = 5000; // 5 segundos
const SOCKET_URL = 'http://192.168.18.24:3000';
let socket;

// Inicializar Socket.IO
function iniciarSocket() {
  socket = io(SOCKET_URL);
  
  socket.on('connect', () => {
    console.log('Conectado a Socket.IO:', socket.id);
  });
  
  socket.on('nuevo_mensaje', (datos) => {
    console.log('Nuevo mensaje recibido:', datos);
    const chatId = document.getElementById('chat-id').value;
    const email = document.getElementById('email-usuario').value;
    
    if (chatId && email) {
      cargarMensajes(chatId, email, false);
    }
  });
  
  socket.on('notificacion', () => {
    // Simplemente recargamos la lista de chats para ver actualizaciones
    setTimeout(() => {
      window.location.reload();
    }, 2000);
  });
}

// Cargar mensajes
function cargarMensajes(chatId, email, desplazarAbajo = true) {
  fetch('cargar_mensajes.php?email=' + email)
    .then(response => response.text())
    .then(data => {
      document.getElementById('chat-box').innerHTML = data;
      if (desplazarAbajo) {
        document.getElementById('chat-box').scrollTop = 99999;
      }
    });
}

// Enviar mensaje
function enviarMensaje() {
  const mensaje = document.getElementById('mensaje-input').value.trim();
  const imagenUrl = document.getElementById('imagen-url').value;
  const chatId = document.getElementById('chat-id').value;
  const email = document.getElementById('email-usuario').value;
  
  if ((!mensaje && !imagenUrl) || !chatId || !email) return;
  
  const formData = new FormData();
  formData.append('id_chat', chatId);
  formData.append('email_usuario', email);
  formData.append('mensaje', mensaje);
  formData.append('imagen_url', imagenUrl);
  
  const boton = document.querySelector('#chat-form button[type="submit"]');
  boton.disabled = true;
  
  fetch('guardar_mensaje.php', {
    method: 'POST',
    body: formData
  })
  .then(() => {
    document.getElementById('mensaje-input').value = '';
    document.getElementById('imagen-url').value = '';
    document.getElementById('imagen-preview').classList.add('d-none');
    document.getElementById('imagen-input').value = '';
    
    if (socket && socket.connected) {
      socket.emit('mensaje_admin', {
        id_chat: chatId,
        mensaje: mensaje,
        imagen_url: imagenUrl
      });
    }
    
    cargarMensajes(chatId, email);
    boton.disabled = false;
  })
  .catch(() => {
    boton.disabled = false;
  });
}

// Subir imagen
function subirImagen(archivo) {
  const chatId = document.getElementById('chat-id').value;
  if (!chatId) return;
  
  // Vista previa
  const lector = new FileReader();
  lector.onload = function(e) {
    document.querySelector('#imagen-preview img').src = e.target.result;
    document.getElementById('imagen-preview').classList.remove('d-none');
  };
  lector.readAsDataURL(archivo);
  
  // Subir al servidor
  const formData = new FormData();
  formData.append('image', archivo);
  formData.append('chat_id', chatId);
  
  fetch('subir_imagen.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      document.getElementById('imagen-url').value = data.url;
      document.getElementById('mensaje-input').value = '[Imagen]';
    } else {
      document.getElementById('imagen-input').value = '';
      document.getElementById('imagen-preview').classList.add('d-none');
      alert('Error al subir la imagen');
    }
  });
}

// Inicialización y eventos
document.addEventListener('DOMContentLoaded', function() {
  // Iniciar Socket.IO
  iniciarSocket();
  
  // Seleccionar primer chat
  const elementosChat = document.querySelectorAll('.chat-item');
  if (elementosChat.length > 0) {
    elementosChat[0].classList.add('active');
    document.getElementById('chat-id').value = elementosChat[0].getAttribute('data-id');
    document.getElementById('email-usuario').value = elementosChat[0].getAttribute('data-email');
    cargarMensajes(elementosChat[0].getAttribute('data-id'), elementosChat[0].getAttribute('data-email'));
    
    // Unirse a sala
    if (socket && socket.connected) {
      socket.emit('unirse_sala', elementosChat[0].getAttribute('data-id'));
    }
  }
  
  // Eventos de click en chats
  document.querySelectorAll('.chat-item').forEach(item => {
    item.addEventListener('click', function() {
      document.querySelectorAll('.chat-item').forEach(c => c.classList.remove('active'));
      item.classList.add('active');
      
      const chatId = item.getAttribute('data-id');
      const email = item.getAttribute('data-email');
      document.getElementById('chat-id').value = chatId;
      document.getElementById('email-usuario').value = email;
      cargarMensajes(chatId, email);
      
      // Unirse a sala
      if (socket && socket.connected) {
        socket.emit('unirse_sala', chatId);
      }
    });
  });

  // Envío de mensajes
  document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    enviarMensaje();
  });
  
  // Manejo de imágenes
  document.getElementById('imagen-btn').addEventListener('click', function() {
    document.getElementById('imagen-input').click();
  });
  
  document.getElementById('imagen-input').addEventListener('change', function(e) {
    if (e.target.files[0]) subirImagen(e.target.files[0]);
  });
  
  document.getElementById('quitar-imagen').addEventListener('click', function() {
    document.getElementById('imagen-input').value = '';
    document.getElementById('imagen-url').value = '';
    document.getElementById('mensaje-input').value = '';
    document.getElementById('imagen-preview').classList.add('d-none');
  });
  
  // Actualización automática
  setInterval(function() {
    const chatId = document.getElementById('chat-id').value;
    const email = document.getElementById('email-usuario').value;
    if (chatId && email) cargarMensajes(chatId, email, false);
  }, TIEMPO_ACTUALIZACION);
});
</script>