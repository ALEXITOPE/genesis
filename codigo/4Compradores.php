<?php
// Conexión a la base de datos
include("ConexionBaseDatos.php"); // Asegúrate de que la conexión esté correcta

// Procesar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_comprador'])) {
        procesarFormulario($_POST['data_comprador']);
    }
}

// Función para procesar los datos e insertarlos en la base de datos
function procesarFormulario($data)
{
    global $conn;

    // Dividir las líneas del textarea
    $filas = explode("\n", trim($data));
    $errores = []; // Para almacenar los mensajes de error
    $procesados = 0;

    foreach ($filas as $index => $fila) {
        // Saltar líneas vacías
        if (trim($fila) === '') {
            continue;
        }

        // Dividir columnas por tabulación o espacios múltiples
        $columnas = preg_split("/\t+|\s{2,}/", trim($fila));

        // Obtener datos de los compradores con valores predeterminados si están vacíos
        $matricula = isset($columnas[0]) ? $columnas[0] : null;

        // Datos del comprador 1
        $nombre_comp1 = isset($columnas[1]) ? $columnas[1] : null;
        $cc_comp1 = isset($columnas[2]) ? $columnas[2] : null;
        $expcc_comp1 = ""; // Campo vacío
        $dom_comp1 = isset($columnas[3]) ? $columnas[3] : null;
        $escivil_comp1 = "";

        // Datos del comprador 2
        $nombre_comp2 = isset($columnas[4]) ? $columnas[4] : null;
        $cc_comp2 = isset($columnas[5]) ? $columnas[5] : null;
        $expcc_comp2 = ""; // Campo vacío
        $dom_comp2 = isset($columnas[6]) ? $columnas[6] : null;
        $escivil_comp2 = "";

        // Datos del comprador 3
        $nombre_comp3 = isset($columnas[7]) ? $columnas[7] : null;
        $cc_comp3 = isset($columnas[8]) ? $columnas[8] : null;
        $expcc_comp3 = ""; // Campo vacío
        $dom_comp3 = isset($columnas[9]) ? $columnas[9] : null;
        $escivil_comp3 = "";

        // Datos del comprador 4
        $nombre_comp4 = isset($columnas[10]) ? $columnas[10] : null;
        $cc_comp4 = isset($columnas[11]) ? $columnas[11] : null;
        $expcc_comp4 = ""; // Campo vacío
        $dom_comp4 = isset($columnas[12]) ? $columnas[12] : null;
        $escivil_comp4 = "";

        // Verificar que los campos obligatorios no estén vacíos
        if (!empty($matricula) && !empty($nombre_comp1) && !empty($cc_comp1)) {
            // Insertar datos en la base de datos
            $sql = "INSERT INTO compradores (matr_inm, nombre_comp1, cc_comp1, expcc_comp1, dom_comp1, escivil_comp1, nombre_comp2, cc_comp2, expcc_comp2, dom_comp2, escivil_comp2, nombre_comp3, cc_comp3, expcc_comp3, dom_comp3, escivil_comp3, nombre_comp4, cc_comp4, expcc_comp4, dom_comp4, escivil_comp4) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if ($stmt) {
                // Asegúrate de pasar todos los valores
                $stmt->bind_param(
                    "sssssssssssssssssssss",
                    $matricula,
                    $nombre_comp1,
                    $cc_comp1,
                    $expcc_comp1,
                    $dom_comp1,
                    $escivil_comp1,
                    $nombre_comp2,
                    $cc_comp2,
                    $expcc_comp2,
                    $dom_comp2,
                    $escivil_comp2,
                    $nombre_comp3,
                    $cc_comp3,
                    $expcc_comp3,
                    $dom_comp3,
                    $escivil_comp3,
                    $nombre_comp4,
                    $cc_comp4,
                    $expcc_comp4,
                    $dom_comp4,
                    $escivil_comp4
                );
                $stmt->execute();
                $procesados++;
            } else {
                $errores[] = "Línea " . ($index + 1) . ": Error al preparar la consulta.";
            }
        } else {
            $errores[] = "Línea " . ($index + 1) . ": Matrícula, nombre o cédula vacíos.";
        }
    }

    // Mostrar resultados
    if ($procesados > 0) {
        echo "<p>Se procesaron correctamente $procesados compradores.</p>";
    }
    if (!empty($errores)) {
        echo "<p>Errores encontrados:</p><ul>";
        foreach ($errores as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    }
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BASE DE DATOS</title>
    <link rel="stylesheet" href="AllStyles.css">
</head>

<body>
    <div class="container">
        <!-- Columna izquierda -->
        <div class="left-column">
            <h1>NOTARÍA</h1>
            <div class="logo">
                <img src="img/Nota71.jpg" alt="Logo de la Notaría" />
            </div>
            <h1>ESCRITURACIÓN</h1><br>
            <div id="floating-menu" class="floating-menu">
                <a href="1IndexGenesis.php" class="boton">Inicio</a>
                <a href="2FormularioBaseDatos.php" class="boton active">Base datos</a>
                <a href="5Generarescritura.php" class="boton">Generar escritura</a>
            </div>
        </div>

        <!-- Columna derecha -->
        <div class="right-column">
            <div class="middle-column">
                <h1>Gestión de COMPRADORES</h1>

                <!-- Formulario único para ingresar datos -->
                <form method="POST">
                    <textarea name="data_comprador" rows="10" placeholder="Pegue aquí la información de los compradores (una línea por fila, con datos separados por tabulaciones o espacios dobles)"></textarea>
                    <button type="submit" name="submit_comprador">Registrar Compradores</button>
                </form>
                <br>
                <a href="2FormularioBaseDatos.php" class="boton">Regresar</a>
            </div>
        </div>
    </div>
</body>

</html>