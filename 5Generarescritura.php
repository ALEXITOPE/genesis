<?php
// PRIMERA SECCIÓN - 5Generarescritura.php
require 'vendor/autoload.php';
require_once 'ConexionBaseDatos.php';
require_once 'Convertidores.php';

// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar conexión a la base de datos
if (!isset($conn) || $conn->connect_error) {
    die('Error en la conexión a la base de datos.');
}

// Inicializar variables del formulario
$error_message = '';

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $tipo_escritura = $_POST['tipo_escritura'] ?? 'contado';
    $matricula_ap = $_POST['matr_ap'] ?? null;
    $matricula_pq = $_POST['matr_pq'] ?? null;
    $matricula_dp = $_POST['matr_dp'] ?? null;

    // Validar campos requeridos
    if (!$matricula_ap || !$matricula_pq || !$matricula_dp) {
        $error_message = 'Todos los campos son necesarios.';
    } else {
        // Sanitizar entradas
        $matricula_ap = $conn->real_escape_string($matricula_ap);
        $matricula_pq = $conn->real_escape_string($matricula_pq);
        $matricula_dp = $conn->real_escape_string($matricula_dp);





        // Obtener datos del inmueble y comprador
        $datos_inmueble_comprador = obtenerDatosInmuebleYCompradores($conn, $matricula_ap, $matricula_pq, $matricula_dp);

        // Inicializar variables para tipo, número, torre, y valor
        $inmuebles = [
            'ap' => ['tipo' => null, 'num' => null, 'torre' => null, 'vlr' => 0],
            'pq' => ['tipo' => null, 'num' => null, 'torre' => null, 'vlr' => 0],
            'dp' => ['tipo' => null, 'num' => null, 'torre' => null, 'vlr' => 0],
        ];
        $total_vlr_vta = 0;

        // Verificar si la consulta de inmuebles tuvo resultados
        if ($datos_inmueble_comprador && count($datos_inmueble_comprador) > 0) {
            // Asignar los valores a cada matrícula
            foreach ($datos_inmueble_comprador as $row) {
                foreach (['ap', 'pq', 'dp'] as $key) {
                    if ($row['matr_inm'] == ${"matricula_$key"}) {
                        $inmuebles[$key] = [
                            'tipo' => $row['tipo_inm'],
                            'num' => $row['num_inm'],
                            'torre' => $row['torre_inm'],
                            'vlr' => $row['vlr_inm']
                        ];
                        $total_vlr_vta += $row['vlr_inm'];
                    }
                }
            }


            // Verificar si la consulta de compradores tuvo resultados
            $compradores = obtenerDatosInmuebleYCompradores($conn, $matricula_ap, $matricula_pq, $matricula_dp);
            if (!$compradores) {
                $error_message = 'No se encontraron resultados en la tabla compradores.';
            }

            // Determinar la plantilla a utilizar
            $templatePath = match (strtolower($tipo_escritura)) {
                'contado' => 'D:\\xampp\\htdocs\\genesis\\PLANTILLAS\\Arborea Contado.docx',
                'hipoteca' => 'D:\\xampp\\htdocs\\genesis\\PLANTILLAS\\Arborea Hipoteca.docx',
                'leasing' => 'D:\\xampp\\htdocs\\genesis\\PLANTILLAS\\Arborea Leasing.docx',
                default => null,
            };

            if ($templatePath) {
                // Generar documento
                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

                // Preparar los valores (convertir a letras y configurar mayúsculas/minúsculas)
                $valoresPreparados = prepararValores($inmuebles, $total_vlr_vta);
                $inmuebles = $valoresPreparados['inmuebles'];
                $total_vlr_vta_letras_min = $valoresPreparados['total_vlr_vta_letras_min'];
                $total_vlr_vta_letras_may = $valoresPreparados['total_vlr_vta_letras_may'];

                // Asignar valores a la plantilla
                $templateProcessor->setValue('vlr_vta_letras', $total_vlr_vta_letras_min); // Minúsculas
                $templateProcessor->setValue('VLR_VTA_LETRAS', $total_vlr_vta_letras_may); // Mayúsculas

                foreach (['ap', 'pq', 'dp'] as $key) {
                    $templateProcessor->setValue("vlr_{$key}_letras", $inmuebles[$key]['vlr_letras_min']); // Minúsculas
                    $templateProcessor->setValue("VLR_{$key}_LETRAS", $inmuebles[$key]['vlr_letras_may']); // Mayúsculas
                    $templateProcessor->setValue("num_{$key}_letras", $inmuebles[$key]['num_letras_min']); // Minúsculas
                    $templateProcessor->setValue("NUM_{$key}_LETRAS", $inmuebles[$key]['num_letras_may']); // Mayúsculas
                    $templateProcessor->setValue("torre_{$key}_letras", $inmuebles[$key]['torre_letras_min']); // Minúsculas
                    $templateProcessor->setValue("TORRE_{$key}_LETRAS", $inmuebles[$key]['torre_letras_may']); // Mayúsculas
                }

                // Asignar valores en minúsculas
                $templateProcessor->setValue('vlr_ap_letras', $inmuebles['ap']['vlr_letras_min']);
                $templateProcessor->setValue('num_ap_letras', $inmuebles['ap']['num_letras_min']);
                $templateProcessor->setValue('torre_ap_letras', $inmuebles['ap']['torre_letras_min']);
                $templateProcessor->setValue('vlr_pq_letras', $inmuebles['pq']['vlr_letras_min']);
                $templateProcessor->setValue('num_pq_letras', $inmuebles['pq']['num_letras_min']);
                $templateProcessor->setValue('vlr_dp_letras', $inmuebles['dp']['vlr_letras_min']);
                $templateProcessor->setValue('num_dp_letras', $inmuebles['dp']['num_letras_min']);

                // Asignar valores en mayúsculas
                $templateProcessor->setValue('VLR_AP_LETRAS', $inmuebles['ap']['vlr_letras_may']);
                $templateProcessor->setValue('NUM_AP_LETRAS', $inmuebles['ap']['num_letras_may']);
                $templateProcessor->setValue('TORRE_AP_LETRAS', $inmuebles['ap']['torre_letras_may']);
                $templateProcessor->setValue('VLR_PQ_LETRAS', $inmuebles['pq']['vlr_letras_may']);
                $templateProcessor->setValue('NUM_PQ_LETRAS', $inmuebles['pq']['num_letras_may']);
                $templateProcessor->setValue('VLR_DP_LETRAS', $inmuebles['dp']['vlr_letras_may']);
                $templateProcessor->setValue('NUM_DP_LETRAS', $inmuebles['dp']['num_letras_may']);

                $templateProcessor->setValue('vlr_vta', number_format($total_vlr_vta, 0, ',', '.'));

                // Rellenar el campo general de torre
                $templateProcessor->setValue('num_torre', $inmuebles['ap']['torre'] ?? ''); // Valor numérico
                $templateProcessor->setValue('num_torre_letras', $inmuebles['ap']['torre_letras'] ?? ''); // Valor en letras

                // Preparar los valores (convertir a letras y configurar mayúsculas/minúsculas)
                $valoresPreparados = prepararValores($inmuebles, $total_vlr_vta);
                $inmuebles = $valoresPreparados['inmuebles'];
                $total_vlr_vta_letras_min = $valoresPreparados['total_vlr_vta_letras_min'];
                $total_vlr_vta_letras_may = $valoresPreparados['total_vlr_vta_letras_may'];

                // Asignar valores a la plantilla
                $templateProcessor->setValue('vlr_vta_letras', $total_vlr_vta_letras_min); // Minúsculas
                $templateProcessor->setValue('VLR_VTA_LETRAS', $total_vlr_vta_letras_may); // Mayúsculas

                foreach (['ap', 'pq', 'dp'] as $key) {
                    $templateProcessor->setValue("vlr_{$key}_letras", $inmuebles[$key]['vlr_letras_min']); // Minúsculas
                    $templateProcessor->setValue("VLR_{$key}_LETRAS", $inmuebles[$key]['vlr_letras_may']); // Mayúsculas
                    $templateProcessor->setValue("num_{$key}_letras", $inmuebles[$key]['num_letras_min']); // Minúsculas
                    $templateProcessor->setValue("NUM_{$key}_LETRAS", $inmuebles[$key]['num_letras_may']); // Mayúsculas
                    $templateProcessor->setValue("torre_{$key}_letras", $inmuebles[$key]['torre_letras_min']); // Minúsculas
                    $templateProcessor->setValue("TORRE_{$key}_LETRAS", $inmuebles[$key]['torre_letras_may']); // Mayúsculas
                }

                // Asignar valores en minúsculas
                foreach (['ap', 'pq', 'dp'] as $key) {
                    $templateProcessor->setValue("vlr_{$key}_letras", $inmuebles[$key]['vlr_letras_min']);
                    $templateProcessor->setValue("num_{$key}_letras", $inmuebles[$key]['num_letras_min']);
                    $templateProcessor->setValue("torre_{$key}_letras", $inmuebles[$key]['torre_letras_min']);
                }

                // Rellenar el campo general de torre
                $templateProcessor->setValue('num_torre', $inmuebles['ap']['torre'] ?? ''); // Valor numérico
                $templateProcessor->setValue('num_torre_letras', $inmuebles['ap']['torre_letras'] ?? ''); // Valor en letras


                // Rellenar los campos de la plantilla solo si hay datos válidos
                $templateProcessor->setValue('vlr_vta', number_format($total_vlr_vta, 0, ',', '.'));
                // Reemplazar las matrículas en el documento
                foreach (['ap', 'pq', 'dp'] as $key) {
                    $templateProcessor->setValue("matr_$key", ${"matricula_$key"});
                    $templateProcessor->setValue("tipo_$key", $inmuebles[$key]['tipo']);
                    $templateProcessor->setValue("num_$key", $inmuebles[$key]['num']);
                    $templateProcessor->setValue("torre_$key", $inmuebles[$key]['torre']);
                    $templateProcessor->setValue("vlr_$key", $inmuebles[$key]['vlr']);
                }


                // Procesamiento de COMPRADORES
                $resultadosCompradores = obtenerDatosInmuebleYCompradores($conn, $matricula_ap, $matricula_pq, $matricula_dp);

                if ($resultadosCompradores && count($resultadosCompradores) > 0) {
                    foreach ($resultadosCompradores as $comprador) {
                        // Procesar todos los compradores para cada fila de resultados
                        for ($compradorIndex = 1; $compradorIndex <= 4; $compradorIndex++) {
                            $nombre = isset($comprador["nombre_comp$compradorIndex"]) ? $comprador["nombre_comp$compradorIndex"] : null;
                            $cc = isset($comprador["cc_comp$compradorIndex"]) ? $comprador["cc_comp$compradorIndex"] : null;
                            $expcc = isset($comprador["expcc_comp$compradorIndex"]) ? $comprador["expcc_comp$compradorIndex"] : null;
                            $dom = isset($comprador["dom_comp$compradorIndex"]) ? $comprador["dom_comp$compradorIndex"] : null;
                            $escivil = isset($comprador["escivil_comp$compradorIndex"]) ? $comprador["escivil_comp$compradorIndex"] : null;

                            // Asignar valores de COMPRADORES al template
                            $templateProcessor->setValue("nombre_comp$compradorIndex", !empty($nombre) ? $nombre : "N/A");
                            $templateProcessor->setValue("cc_comp$compradorIndex", !empty($cc) ? $cc : "N/A");
                            $templateProcessor->setValue("expcc_comp$compradorIndex", !empty($expcc) ? $expcc : "N/A");
                            $templateProcessor->setValue("dom_comp$compradorIndex", !empty($dom) ? $dom : "N/A");
                            $templateProcessor->setValue("escivil_comp$compradorIndex", !empty($escivil) ? $escivil : "N/A");
                        }
                    }
                }

                // Procesamiento del parrafo_comp
                // Obtener los datos de los compradores
                $resultados = obtenerDatosInmuebleYCompradores($conn, $matricula_ap, $matricula_pq, $matricula_dp);

                // Verificar que se obtuvieron resultados
                if (!empty($resultados)) {
                    // Generar el párrafo basado en los datos obtenidos
                    $parrafoComp = generarParrafoDesdeDatos($resultados[0]);

                    // Reemplazar el campo ${parrafo_comp} en la plantilla
                    $templateProcessor->setValue('parrafo_comp', htmlspecialchars($parrafoComp));
                }

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

// Obtener los datos para las listas desplegables
$sql_municipios = "SELECT nombre_mun FROM municipios";
$result_municipios = $conn->query($sql_municipios);

$sql_estado_civil = "SELECT nombre_escivil FROM estados_civiles";
$result_estado_civil = $conn->query($sql_estado_civil);

$sql_banco = "SELECT nombre_bco FROM bancos";
$result_banco = $conn->query($sql_banco);
?>




<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Plantillas</title>
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
                <h1>GESTIÓN DE PLANTILLAS</h1>

                <form action="5Generarescritura.php" method="POST" class="styled-form">
                    <!-- Formulario -->
                    <div class="fpago">
                        <label for="tipo_escritura">FORMA PAGO:</label><br><br>
                        <select name="tipo_escritura" id="tipo_escritura" required onchange="mostrarBanco()">
                            <option value="Contado">CONTADO</option>
                            <option value="Hipoteca">HIPOTECA</option>
                            <option value="Leasing">LEASING</option>
                        </select><br><br>

                        <div class="opcion-banco" id="opcion-banco">
                            <select name="banco" id="nombre_bco">
                                <option value="" disabled selected>BANCO:</option>
                                <?php if ($result_banco->num_rows > 0): ?>
                                    <?php while ($row = $result_banco->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($row['nombre_bco']) ?>">
                                            <?= htmlspecialchars($row['nombre_bco']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="inmuebles">
                        <label for="matr_ap">MATR.AP:</label>
                        <input type="text" id="matr_ap" name="matr_ap" value="<?= htmlspecialchars($matricula_ap ?? '') ?>" required><br>
                        <label for="matr_pq">MATR.PQ:</label>
                        <input type="text" id="matr_pq" name="matr_pq" value="<?= htmlspecialchars($matricula_pq ?? '') ?>" required><br>
                        <label for="matr_dp">MATR.DP:</label>
                        <input type="text" id="matr_dp" name="matr_dp" value="<?= htmlspecialchars($matricula_dp ?? '') ?>" required>
                    </div>

                    <button type="submit" name="ejecutar">GENERAR DOCUMENTO</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>