<?php
require 'vendor/autoload.php';
require_once 'ConexionBaseDatos.php';
session_start();

// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar conexión a la base de datos
if (!isset($conn) || $conn->connect_error) {
    die('Error en la conexión a la base de datos.');
}

// Inicializar variables del formulario
$matricula_ap = $matricula_pq = $matricula_dp = $cedula_comprador = $tipo_afec = '';
$error_message = '';

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $tipo_escritura = $_POST['tipo_escritura'] ?? 'contado';
    $matricula_ap = $_POST['matr_ap'] ?? null;
    $matricula_pq = $_POST['matr_pq'] ?? null;
    $matricula_dp = $_POST['matr_dp'] ?? null;
    $cedula_comprador = $_POST['cedula_comprador'] ?? null;
    $tipo_afec = $_POST['tipo_afec'] ?? null;

    // Validar campos requeridos
    if (!$matricula_ap || !$matricula_pq || !$matricula_dp || !$cedula_comprador || !$tipo_afec) {
        $error_message = 'Todos los campos son necesarios.';
    } else {
        // Sanitizar entradas
        $matricula_ap = $conn->real_escape_string($matricula_ap);
        $matricula_pq = $conn->real_escape_string($matricula_pq);
        $matricula_dp = $conn->real_escape_string($matricula_dp);
        $cedula_comprador = $conn->real_escape_string($cedula_comprador);

        // Construir la consulta SQL
        $query = "
        SELECT 
            inmuebles.vlr_inm, 
            comprador_1.nombre_comp1, 
            comprador_1.cc_comp1 
        FROM inmuebles
        LEFT JOIN comprador_1 ON comprador_1.cc_comp1 = '$cedula_comprador'
        WHERE 
            (inmuebles.matr_inm = '$matricula_ap' AND inmuebles.tipo_inm = 'APARTAMENTO NUMERO') 
            OR (inmuebles.matr_inm = '$matricula_pq' AND inmuebles.tipo_inm = 'PARQUEADERO NUMERO') 
            OR (inmuebles.matr_inm = '$matricula_dp' AND inmuebles.tipo_inm = 'DEPOSITO NUMERO');
        ";

        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $total_vlr_vta = 0;
            $nombre_comprador = $cc_comprador = '';

            while ($row = $result->fetch_assoc()) {
                $vlr_inm = $row['vlr_inm'];
                $nombre_comprador = $row['nombre_comp1'];
                $cc_comprador = $row['cc_comp1'];
                $total_vlr_vta += $vlr_inm;
            }

            // Consulta adicional para obtener el tipo de afectación
            $afecta_query = "SELECT `tipo_afec` FROM `afectacion` WHERE `id_afec` = '$tipo_afec'";
            $afecta_result = $conn->query($afecta_query);
            $afecta_text = ($afecta_result && $afecta_result->num_rows > 0) ? $afecta_result->fetch_assoc()['tipo_afec'] : 'N/A';

            // Determinar la plantilla a utilizar
            $tipo_escritura = strtolower($tipo_escritura);
            $templatePath = '';

            switch ($tipo_escritura) {
                case 'contado':
                    $templatePath = 'D:\\xampp\\htdocs\\genesis\\PLANTILLAS\\Arborea Contado.docx';
                    break;
                case 'hipoteca':
                    $templatePath = 'D:\\xampp\\htdocs\\genesis\\PLANTILLAS\\Arborea Hipoteca.docx';
                    break;
                case 'leasing':
                    $templatePath = 'D:\\xampp\\htdocs\\genesis\\PLANTILLAS\\Arborea Leasing.docx';
                    break;
                default:
                    $error_message = 'Tipo de escritura no válido.';
                    break;
            }

            if ($templatePath && !$error_message) {
                // Generar documento
                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

                // Reemplazar valores en la plantilla
                $templateProcessor->setValue('vlr_vta', number_format($total_vlr_vta, 0, ',', '.'));
                $templateProcessor->setValue('tipo_afec', $afecta_text ?: 'N/A'); // Valor predeterminado
                $templateProcessor->setValue('nombre_comp1', $nombre_comprador ?: 'No definido');
                $templateProcessor->setValue('cc_comp1', $cc_comprador ?: 'No definido');

                // Guardar y descargar archivo
                $outputDir = "D:\\xampp\\htdocs\\genesis\\archivos_generados\\";
                if (!file_exists($outputDir)) {
                    mkdir($outputDir, 0777, true);
                }

                $outputFile = $outputDir . "escritura_" . date('Ymd_His') . ".docx";
                $templateProcessor->saveAs($outputFile);

                if (file_exists($outputFile)) {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                    header('Content-Disposition: attachment; filename="' . basename($outputFile) . '"');
                    header('Content-Length: ' . filesize($outputFile));
                    readfile($outputFile);
                    exit;
                } else {
                    $error_message = 'Error al generar el archivo.';
                }
            }
        } else {
            $error_message = 'No se encontraron resultados.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTARÍA 71</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="AllStyles.css">
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
                <a href="2FormularioBaseDatos.php" class="boton">Base de Datos</a>
                <a href="3FormularioPlantilla.php" class="boton">Crear nueva plantilla</a>
                <a href="10Generarescritura.php" class="boton">Generar nueva escritura</a>
            </div>
        </div>

        <div class="right-column">
            <div class="middle-column">
                <h1>GESTIÓN DE PLANTILLAS - COMPRADORES</h1>
                <form action="10compradores.php" method="POST" class="styled-form">
                    <label for="compradores">¿Cuántos compradores?</label>
                    <select name="compradores" id="compradores" onchange="toggleAdditionalBuyers()" required>
                        <option value="1">1 comprador</option>
                        <option value="2">2 compradores</option>
                        <option value="3">3 compradores</option>
                        <option value="4">4 compradores</option>
                    </select>

                    <!-- Campo de Cédula para el Comprador 1 -->
                    <label for="cedula_comprador">CC COMPRADOR1:</label>
                    <input type="text" id="cedula_comprador" name="cedula_comprador" required>

                    <!-- Lista de los demás compradores, inicialmente ocultos -->
                    <div id="additional-buyers" style="display:none;">
                        <div id="buyer2" style="display:none;">
                            <label for="cedula_comprador2">CC COMPRADOR2:</label>
                            <input type="text" id="cedula_comprador2" name="cedula_comprador2">
                        </div>
                        <div id="buyer3" style="display:none;">
                            <label for="cedula_comprador3">CC COMPRADOR3:</label>
                            <input type="text" id="cedula_comprador3" name="cedula_comprador3">
                        </div>
                        <div id="buyer4" style="display:none;">
                            <label for="cedula_comprador4">CC COMPRADOR4:</label>
                            <input type="text" id="cedula_comprador4" name="cedula_comprador4">
                        </div>
                    </div>

                </form>

                <!-- Mostrar mensaje de error si existe -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?= htmlspecialchars($error_message) ?>
                                <!-- Botón para regresar a la sección anterior -->
                                <a href="10bancos.php" class="boton">Siguiente: Datos de Bancos</a>
                </div>
                <?php endif; ?>
                <!-- Botón para ir a la siguiente sección -->
                <a href="10bancos.php" class="boton">regresar</a>

            </div>
        </div>
    </div>

    <script>
        // Función para mostrar u ocultar los campos de los compradores adicionales
        function toggleAdditionalBuyers() {
            var buyersCount = document.getElementById("compradores").value;

            // Ocultar todos los campos adicionales inicialmente
            document.getElementById("additional-buyers").style.display = "none"; // Ocultar todos los campos adicionales

            // Mostrar los campos necesarios según la cantidad de compradores
            if (buyersCount >= 2) {
                document.getElementById("additional-buyers").style.display = "block"; // Mostrar los campos adicionales
                document.getElementById("buyer2").style.display = "block"; // Mostrar el campo del comprador 2
            }
            if (buyersCount >= 3) {
                document.getElementById("buyer3").style.display = "block"; // Mostrar el campo del comprador 3
            }
            if (buyersCount >= 4) {
                document.getElementById("buyer4").style.display = "block"; // Mostrar el campo del comprador 4
            }
        }

        // Llamar a la función para aplicar el comportamiento al cargar la página
        toggleAdditionalBuyers();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>