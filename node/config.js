module.exports = {
    // configuración de la base de datos
    database: {
        host: 'localhost',
        user: 'root',
        password: '',
        database: 'mascotas_db',
        port: 3307 // mi puerto de mysql (igual que el que puse en la conexion a la base de datos)
    },
    
    // configuración del servidor WebSocket
    server: {
        port: 3000, // este queda asi porque es el puerto de websockets. no confundirlo con mi puerto mysql
        cors: {
            origin: "http://localhost",
            methods: ["GET", "POST"]
        }
    }
};