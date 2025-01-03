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
    $filas = explode("\n", trim($data)); // Remueve líneas vacías
    $errores = []; // Para almacenar los mensajes de error
    $procesados = 0;

    foreach ($filas as $index => $fila) {
        // Saltar líneas vacías
        if (trim($fila) === '') {
            continue;
        }

        // Dividir columnas por tabulación
        $columnas = explode("\t", trim($fila));

        // Obtener datos con valores predeterminados si están vacíos
        $matricula = $columnas[0] ?? null;

        // Crear las variables explícitas para cada comprador
        $nombre_comp1 = $columnas[1] ?? '';
        $cc_comp1 = $columnas[2] ?? '';
        // Asignamos vacíos a las columnas que deben ir vacías
        $expcc_comp1 = ''; // Columna vacía
        $dom_comp1 = '';   // Columna vacía
        $escivil_comp1 = ''; // Columna vacía

        $nombre_comp2 = $columnas[6] ?? '';
        $cc_comp2 = $columnas[7] ?? '';
        // Asignamos vacíos a las columnas que deben ir vacías
        $expcc_comp2 = ''; // Columna vacía
        $dom_comp2 = '';   // Columna vacía
        $escivil_comp2 = ''; // Columna vacía

        $nombre_comp3 = $columnas[11] ?? '';
        $cc_comp3 = $columnas[12] ?? '';
        // Asignamos vacíos a las columnas que deben ir vacías
        $expcc_comp3 = ''; // Columna vacía
        $dom_comp3 = '';   // Columna vacía
        $escivil_comp3 = ''; // Columna vacía

        $nombre_comp4 = $columnas[16] ?? '';
        $cc_comp4 = $columnas[17] ?? '';
        // Asignamos vacíos a las columnas que deben ir vacías
        $expcc_comp4 = ''; // Columna vacía
        $dom_comp4 = '';   // Columna vacía
        $escivil_comp4 = ''; // Columna vacía

        // Verificar campos obligatorios
        if (!empty($matricula) && !empty($nombre_comp1) && !empty($cc_comp1)) {
            // Verificar si la matrícula ya existe en la base de datos
            $sql_check = "SELECT COUNT(*) FROM compradores WHERE matr_inm = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $matricula);
            $stmt_check->execute();
            $stmt_check->bind_result($count);
            $stmt_check->fetch();
            $stmt_check->close();

            // Si la matrícula ya existe, actualizar el registro; si no, insertar uno nuevo
            if ($count > 0) {
                // Actualizar el registro existente
                $sql_update = "UPDATE compradores SET
                                nombre_comp1 = ?, cc_comp1 = ?, expcc_comp1 = ?, dom_comp1 = ?, escivil_comp1 = ?,
                                nombre_comp2 = ?, cc_comp2 = ?, expcc_comp2 = ?, dom_comp2 = ?, escivil_comp2 = ?,
                                nombre_comp3 = ?, cc_comp3 = ?, expcc_comp3 = ?, dom_comp3 = ?, escivil_comp3 = ?,
                                nombre_comp4 = ?, cc_comp4 = ?, expcc_comp4 = ?, dom_comp4 = ?, escivil_comp4 = ?
                            WHERE matr_inm = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param(
                    "sssssssssssssssssssss", 
                    $nombre_comp1, $cc_comp1, $expcc_comp1, $dom_comp1, $escivil_comp1,
                    $nombre_comp2, $cc_comp2, $expcc_comp2, $dom_comp2, $escivil_comp2,
                    $nombre_comp3, $cc_comp3, $expcc_comp3, $dom_comp3, $escivil_comp3,
                    $nombre_comp4, $cc_comp4, $expcc_comp4, $dom_comp4, $escivil_comp4,
                    $matricula
                );
                $stmt_update->execute();
            } else {
                // Insertar un nuevo registro
                $sql_insert = "INSERT INTO compradores (
                                matr_inm, 
                                nombre_comp1, cc_comp1, expcc_comp1, dom_comp1, escivil_comp1,
                                nombre_comp2, cc_comp2, expcc_comp2, dom_comp2, escivil_comp2,
                                nombre_comp3, cc_comp3, expcc_comp3, dom_comp3, escivil_comp3,
                                nombre_comp4, cc_comp4, expcc_comp4, dom_comp4, escivil_comp4
                            ) VALUES (
                                ?, ?, ?, ?, ?, ?,
                                ?, ?, ?, ?, ?,
                                ?, ?, ?, ?, ?,
                                ?, ?, ?, ?, ?
                            )";

                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param(
                    "sssssssssssssssssssss", // 21 's' para los 21 parámetros
                    $matricula,
                    $nombre_comp1, $cc_comp1, $expcc_comp1, $dom_comp1, $escivil_comp1,
                    $nombre_comp2, $cc_comp2, $expcc_comp2, $dom_comp2, $escivil_comp2,
                    $nombre_comp3, $cc_comp3, $expcc_comp3, $dom_comp3, $escivil_comp3,
                    $nombre_comp4, $cc_comp4, $expcc_comp4, $dom_comp4, $escivil_comp4
                );
                $stmt_insert->execute();
            }

            $procesados++;
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
