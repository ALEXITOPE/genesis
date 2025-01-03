<?php
require 'vendor/autoload.php';
require_once 'ConexionBaseDatos.php';
session_start();

function log_error($message)
{
    file_put_contents('error_log.txt', $message . "\n", FILE_APPEND);
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['informacion'])) {
        $datos = $_POST['informacion'];

        // Verificar que el textarea no esté vacío
        if (empty(trim($datos))) {
            $_SESSION['mensaje'] = "El campo de información no puede estar vacío.";
            header("Location: 3C_INMUEBLESValores.php");
            exit;
        }

        // Dividir los datos en líneas
        $lineas = explode("\n", trim($datos));
        $valores = [];
        $errores = [];
        $exitos = 0;

        foreach ($lineas as $linea) {
            // Dividir la línea en columnas (separador tabulación)
            $columnas = explode("\t", trim($linea));

            // Validar que haya al menos 3 columnas (matrícula, coeficiente y valor)
            if (count($columnas) >= 3) {
                $matricula = trim($columnas[0]);
                $coeficiente = trim($columnas[1]);
                $valor = trim($columnas[2]);

                // Validar que matrícula y coeficiente no estén vacíos
                if (!empty($matricula) && !empty($coeficiente)) {
                    // Si el valor es "N/A", lo tratamos como NULL
                    if ($valor == "N/A") {
                        $valor = NULL;
                    } else {
                        // Formatear el valor (reemplazar coma por punto para bases de datos)
                        $valor = str_replace(",", ".", $valor);
                    }

                    // Formatear el coeficiente (reemplazar coma por punto)
                    $coeficiente = str_replace(",", ".", $coeficiente);

                    // Agregar valores al array para procesamiento en lote
                    $valores[] = "($matricula, $coeficiente, " . ($valor ? "$valor" : "NULL") . ")";
                } else {
                    $errores[] = "Datos incompletos en la línea: '$linea'.";
                }
            } else {
                $errores[] = "Formato incorrecto en la línea: '$linea'.";
            }
        }

        // Procesar las actualizaciones en lote
        if (!empty($valores)) {
            // Crear una consulta SQL para actualizar en lote
            $sql = "INSERT INTO inmuebles (matr_inm, coef_inm, vlr_inm) VALUES " . implode(",", $valores) .
                " ON DUPLICATE KEY UPDATE coef_inm = VALUES(coef_inm), vlr_inm = VALUES(vlr_inm)";

            if ($conn->query($sql) === TRUE) {
                $exitos = count($valores);
            } else {
                $errores[] = "Error en la ejecución de la consulta: " . $conn->error;
            }
        }

        // Registrar los resultados en la sesión
        if ($exitos > 0) {
            $_SESSION['mensaje'] = "Se actualizaron $exitos registros exitosamente.";
        }
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
        }

        // Redirigir a la misma página para mostrar los mensajes
        header("Location: 3C_INMUEBLESValores.php");
        exit;
    }
}
?>





























<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coeficientes y Valores</title>
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
                <a href="2FormularioBaseDatos.php" class="boton active">Base datos</a>
                <a href="5Generarescritura.php" class="boton">Generar escritura</a>
            </div>
        </div>

        <div class="right-column">
            <div class="middle-column">
                <h1>Gestión de INMUEBLES</h1>
                <div class="table-buttons">
                    <a href="3A_INMUEBLESMatriculas.php" class="boton ">Matrículas</a>
                    <a href="3B_INMUEBLESLinderos.php" class="boton">Linderos</a>
                    <a href="3C_INMUEBLESValores.php" class="boton active">Valores</a>
                    <a href="3D_INMUEBLESComodidades.php" class="boton">Comodidades</a>
                </div>


                <!-- Mostrar el mensaje de éxito o error -->
                <?php if (isset($_SESSION['mensaje'])): ?>
                    <p style="color: green;"><?php echo $_SESSION['mensaje']; ?></p>
                    <?php unset($_SESSION['mensaje']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['errores'])): ?>
                    <p style="color: red;"><?php echo implode("<br>", $_SESSION['errores']); ?></p>
                    <?php unset($_SESSION['errores']); ?>
                <?php endif; ?>

                <!-- Formulario para coeficientes y valores -->
                <form method="POST" action="">
                    <textarea id="informacion" name="informacion" rows="10" cols="50" placeholder="Ingrese Matrículas, Coeficientes y Valores)"></textarea>
                    <button type="submit">Procesar Matrículas, Valores y Coeficientes</button>
                </form><br><br><br><br><br><br><br><br><br><br><br>
                <a href="2FormularioBaseDatos.php" class="boton">Regresar</a>
            </div>
        </div>
    </div>
</body>

</html>