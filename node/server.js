const express = require('express');
const app = express();
const http = require('http').createServer(app);
const io = require('socket.io')(http, {
    cors: {
        origin: ["http://localhost", "http://localhost:80", "http://127.0.0.1", "http://localhost/mascotas"],
        methods: ["GET", "POST"],
        credentials: true
    }
});
const mysql = require('mysql2');
const config = require('./config');

// Crear conexión a la base de datos
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'mascotas_db',
    port: 3307
});

// Conectar a la base de datos
db.connect((err) => {
    if (err) {
        console.error('Error conectando a la base de datos:', err);
        return;
    }
    console.log('Conectado a la base de datos MySQL');
});

io.on('connection', (socket) => {
    socket.on('enviar_mensaje_usuario', (data) => {
        console.log('Mensaje recibido desde usuario:', data);

        const buscarChatQuery = `SELECT * FROM chats WHERE id_usuario = ? LIMIT 1`;
        db.query(buscarChatQuery, [data.id_usuario], (err, result) => {
            if (err || result.length === 0) {
                console.error('Error buscando chat del usuario:', err || 'No encontrado');
                return;
            }

            const chat = result[0];
            const mensajeInsertar = `
                INSERT INTO mensajes 
                (id_chat, id_emisor, tipo_emisor, mensaje, nombre_emisor, email_emisor) 
                VALUES (?, ?, ?, ?, ?, ?)
            `;

            db.query(
                mensajeInsertar,
                [
                    chat.id_chat,
                    data.id_usuario,
                    'usuario',
                    data.mensaje,
                    data.nombre_usuario,
                    ''
                ],
                (err, resultMsg) => {
                    if (err) {
                        console.error('Error guardando mensaje del usuario:', err);
                        return;
                    }

                    io.to(chat.id_chat).emit('receive_message', {
                        chatId: chat.id_chat,
                        userId: data.id_usuario,
                        tipo_emisor: 'usuario',
                        mensaje: data.mensaje,
                        userName: data.nombre_usuario,
                        imagen_url: data.imagen_url || null,
                        messageId: resultMsg.insertId,
                        timestamp: new Date()
                    });

                    const actualizarChat = `
                        UPDATE chats SET ultimo_mensaje = ?, fecha_ultimo_mensaje = NOW() WHERE id_chat = ?
                    `;
                    db.query(actualizarChat, [data.mensaje, chat.id_chat]);
                }
            );
        });
    });
    console.log('Usuario conectado:', socket.id);

    socket.on('join_chat', (chatId) => {
        socket.join(chatId);
        console.log(`Usuario ${socket.id} se unió al chat ${chatId}`);
    });

    socket.on('send_message', async (messageData) => {
        console.log('Nuevo mensaje:', messageData);

        // Guardar mensaje en la base de datos
        const query = `
            INSERT INTO mensajes 
            (id_chat, id_emisor, tipo_emisor, mensaje, nombre_emisor, email_emisor) 
            VALUES (?, ?, ?, ?, ?, ?)
        `;

        db.query(
            query,
            [
                messageData.chatId,
                messageData.userId,
                messageData.tipo_emisor,
                messageData.message || messageData.mensaje,
                messageData.userName,
                messageData.userEmail
            ],
            (err, result) => {
                if (err) {
                    console.error('Error al guardar mensaje:', err);
                    return;
                }

                // Actualizar último mensaje del chat
                const updateQuery = `
                    UPDATE chats 
                    SET ultimo_mensaje = ?, 
                        fecha_ultimo_mensaje = NOW() 
                    WHERE id_chat = ?
                `;

                db.query(updateQuery,
                    [messageData.message || messageData.mensaje, messageData.chatId],
                    (updateErr) => {
                        if (updateErr) {
                            console.error('Error al actualizar chat:', updateErr);
                        }
                    }
                );

                // Emitir mensaje a todos los usuarios en el chat
                io.to(messageData.chatId).emit('receive_message', {
                    ...messageData,
                    imagen_url: messageData.imagen_url || null,
                    messageId: result.insertId,
                    timestamp: new Date()
                });
            }
        );
    });

    socket.on('disconnect', () => {
        console.log('Usuario desconectado:', socket.id);
    });
});

const PORT = 3000;
http.listen(PORT, () => {
    console.log(`Servidor corriendo en puerto ${PORT}`);
});