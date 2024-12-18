<?php
require 'vendor/autoload.php';
require_once 'ConexionBaseDatos.php'; // Archivo donde defines $conn
require_once 'Convertidores.php';
session_start();

// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar conexión a la base de datos
if (!isset($conn) || $conn->connect_error) {
    die('Error en la conexión a la base de datos: ' . $conn->connect_error);
}

// Consulta para obtener los municipios
$sql_municipios = "SELECT nombre_mun FROM municipios";  // Reemplaza "municipio_nombre" con el nombre real de la columna
$result_municipios = $conn->query($sql_municipios);

$municipios = []; // Array para almacenar los resultados
if ($result_municipios && $result_municipios->num_rows > 0) {
    while ($row = $result_municipios->fetch_assoc()) {
        $municipios[] = $row['nombre_mun']; // Usa el nombre real de la columna
    }
} else {
    echo "No se encontraron resultados en la tabla municipios.";
}

// Ejemplo de impresión de municipios (para verificar)
echo "<pre>";
print_r($municipios);
echo "</pre>";
?>
