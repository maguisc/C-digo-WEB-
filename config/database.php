<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mascotas_db";
$port = 3307; // Puerto configurado en XAMPP

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
