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
    $datosCompletos = $_POST['informacion_completa'];

    // Verificar que el textarea no esté vacío
    if (empty(trim($datosCompletos))) {
        $_SESSION['mensaje'] = "El campo de información no puede estar vacío.";
        header("Location: 3B_INMUEBLESLinderos.php");
        exit;
    }

    // Dividir los datos en líneas
    $lineas = explode("\n", trim($datosCompletos));
    $errores = [];
    $exitos = 0;

    // Arreglo para almacenar los datos
    $lotes = [];

    foreach ($lineas as $linea) {
        // Dividir la línea en columnas (separador tabulación)
        $columnas = explode("\t", trim($linea));

        // Validar que haya al menos 2 columnas (matrícula y linderos)
        if (count($columnas) >= 2) {
            $matricula = trim($columnas[0]);
            $lindero = trim($columnas[1]);

            // Validar que matrícula y linderos no estén vacíos
            if (!empty($matricula) && !empty($lindero)) {
                $lotes[] = [
                    'matricula' => $matricula,
                    'lindero' => $lindero
                ];
            } else {
                $errores[] = "Datos incompletos en la línea: '$linea'.";
            }
        } else {
            $errores[] = "Formato incorrecto en la línea: '$linea'.";
        }
    }

    // Realizar actualizaciones en lotes
    if (!empty($lotes)) {
        // Iniciar la transacción
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("UPDATE inmuebles SET lind_inm = ? WHERE matr_inm = ?");

            foreach ($lotes as $lote) {
                $stmt->bind_param("ss", $lote['lindero'], $lote['matricula']);
                $stmt->execute();
                $exitos++;
            }

            // Confirmar la transacción
            $conn->commit();
            $_SESSION['mensaje'] = "Se actualizaron $exitos registros exitosamente.";
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $conn->rollback();
            $errores[] = "Error en la actualización de lotes: " . $e->getMessage();
        }

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

        // Cerrar el statement
        $stmt->close();
    }

    // Manejo de errores
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
    }

    // Redirigir a la misma página para mostrar los mensajes
    header("Location: 3B_INMUEBLESLinderos.php");
    exit;
}



?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inmuebles</title>
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
                <a href="1IndexGenesis.php" class="boton ">Inicio</a>
                <a href="2FormularioBaseDatos.php" class="boton active">Base datos</a>
                <a href="5Generarescritura.php" class="boton">Generar escritura</a>
            </div>
        </div>

        <div class="right-column">
            <div class="middle-column">
                <h1>Gestión de INMUEBLES</h1>
                <div class="table-buttons">
                    <a href="3A_INMUEBLESMatriculas.php" class="boton ">Matrículas</a>
                    <a href="3B_INMUEBLESLinderos.php" class="boton active">Linderos</a>
                    <a href="3C_INMUEBLESValores.php" class="boton">Valores</a>
                    <a href="3D_INMUEBLESComodidades.php" class="boton">Comodidades</a>
                </div>

                <!-- Mostrar el mensaje de éxito o error -->
                <?php if (isset($_SESSION['mensaje'])): ?>
                    <p style="color: red;"><?php echo $_SESSION['mensaje']; ?></p>
                    <?php unset($_SESSION['mensaje']); ?>
                <?php endif; ?>

                <!-- Formulario para pegar la información -->
                <form method="POST" action="">
                    <textarea id="informacion_completa" name="informacion_completa" rows="1000" cols="50" placeholder="Ingrese aquí la información a procesar"></textarea>
                    <button type="submit">Procesar Linderos</button>
                    </form><br><br><br><br><br><br><br><br><br><br><br>
                    <a href="2FormularioBaseDatos.php" class="boton">Regresar</a>

            </div>
        </div>
    </div>
</body>

</html>