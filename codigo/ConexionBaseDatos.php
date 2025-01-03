<?php
// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Parámetros de conexión
$servername = "localhost";
$username = "root";
$password = "";
$database = "base_general_escrituras";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} else {
    // Conexión exitosa (opcional, si quieres verificar)
    // echo "Conexión exitosa";
}
?>

