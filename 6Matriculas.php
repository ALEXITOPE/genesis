<?php
require 'vendor/autoload.php';
require_once 'ConexionBaseDatos.php';
session_start();

function log_error($message)
{
    file_put_contents('error_log.txt', $message . "\n", FILE_APPEND);
}
// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST["informacion_completa"] ?? null;

    if (empty($data)) {
        $_SESSION['mensaje'] = "No se ingresaron datos para procesar.";
        header("Location: 6Matriculas.php");
        exit;
    }

    $lines = explode("\n", $data);
    $valores = [];
    $errores = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Dividir la línea en columnas usando tabulaciones o espacios múltiples
        $columns = preg_split('/\s{2,}|\t/', $line);

        if (count($columns) === 4) {
            // Asignar las columnas a las variables correspondientes
            $matricula = trim($columns[0]);
            $tipo_inm = trim($columns[1]);
            $num_inm = trim($columns[2]);
            $torre_inm = strtoupper(trim($columns[3])) === 'NO APLICA' ? 'NULL' : "'" . $conn->real_escape_string(trim($columns[3])) . "'";

            // Validar matrícula
            if (!is_numeric($matricula) || $matricula <= 0) {
                $errores[] = "Matrícula inválida: $matricula";
                continue;
            }

            // Agregar el registro al lote
            $valores[] = "('$matricula', '$tipo_inm', '$num_inm', $torre_inm)";
        } else {
            $errores[] = "Formato incorrecto en la línea: '$line'";
        }
    }

    // Insertar los valores válidos en la base de datos
    if (!empty($valores)) {
        $query = "INSERT IGNORE INTO inmuebles (matr_inm, tipo_inm, num_inm, torre_inm) VALUES " . implode(", ", $valores);

        if ($conn->query($query)) {
            $_SESSION['mensaje'] = "Datos procesados exitosamente. Registros insertados: " . $conn->affected_rows;
        } else {
            log_error("Error al insertar datos: " . $conn->error);
            $_SESSION['mensaje'] = "Error al insertar datos en la base de datos.";
        }
    } else {
        $_SESSION['mensaje'] = "No se encontraron datos válidos para procesar.";
    }

    // Manejo de errores
    if (!empty($errores)) {
        $_SESSION['mensaje'] .= "<br>Errores encontrados:<br>" . implode("<br>", $errores);
    }

    $conn->close();
    header("Location: 6Matriculas.php");
    exit;

    if (!empty($registros_no_ingresados)) {
        echo "<h3>Registros no ingresados a la base de datos:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Número de Apartamento</th><th>Texto Asociado</th><th>Razón</th></tr>";
        foreach ($registros_no_ingresados as $registro) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($registro['numero']) . "</td>";
            echo "<td>" . htmlspecialchars($registro['Novedad']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Todos los registros fueron ingresados correctamente.</p>";
    }
}
?>




<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Matrículas</title>
    <link rel="stylesheet" href="AllStyles.css">
</head>

<body>
    <div class="container">
        <div class="left-column">
            <h1>NOTARÍA</h1>
            <div class="logo">
                <img src="img/Nota71.jpg" alt="Logo de la Notaría" />
            </div>
            <h1>ESCRITURACIÓN</h1><br>
            <div id="floating-menu" class="floating-menu">
                <a href="1IndexGenesis.php" class="boton">Inicio</a>
                <a href="2FormularioBaseDatos.php" class="boton active">Base de Datos</a>
                <a href="3FormularioPlantilla.php" class="boton">Crear nueva plantilla</a>
                <a href="10Generarescritura.php" class="boton">Generar nueva escritura</a>
            </div>
        </div>

        <div class="right-column">
            <div class="middle-column">
                <h1>Gestión de INMUEBLES</h1>

                <div class="table-buttons">
                    <a href="6Matriculas.php" class="boton active">Matrículas</a>
                    <a href="3Linderos.php" class="boton">Linderos</a>
                    <a href="2_1Valores.php" class="boton">Valores</a>
                    <a href="5Comodidades.php" class="boton">Comodidades</a>
                </div>

                <!-- Mostrar el mensaje de éxito o error -->
                <?php if (isset($_SESSION['mensaje'])): ?>
                    <p style="color: red;"><?php echo $_SESSION['mensaje']; ?></p>
                    <?php unset($_SESSION['mensaje']); ?>
                <?php endif; ?>

                <!-- Formulario para pegar la información -->
                <form method="POST" action="">
                    <textarea id="informacion_completa" name="informacion_completa" rows="10" cols="50" placeholder="Ingrese aquí la información a procesar"></textarea>
                    <button type="submit">Procesar Matrículass</button>
                    <a href="2FormularioBaseDatos.php" class="boton">Regresar</a>
                </form>
            </div>
        </div>
    </div>
</body>

</html>