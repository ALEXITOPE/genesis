<?php
require 'vendor/autoload.php';
require_once 'ConexionBaseDatos.php';
require_once 'Convertidores.php';
session_start();

// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar conexión a la base de datos
if (!isset($conn) || $conn->connect_error) {
    die('Error en la conexión a la base de datos.');
}

// Inicializar variables del formulario
$error_message = '';

// Obtener los datos de los compradores
$compradores = [
    $_POST['cedula_comprador'] ?? null,
    $_POST['cedula_comprador2'] ?? null,
    $_POST['cedula_comprador3'] ?? null,
    $_POST['cedula_comprador4'] ?? null
];

// Validar que al menos un comprador esté presente
if (!$compradores[0]) {
    $error_message = 'Debe ingresar al menos un comprador.';
}

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $tipo_escritura = $_POST['tipo_escritura'] ?? 'contado';
    $matricula_ap = $_POST['matr_ap'] ?? null;
    $matricula_pq = $_POST['matr_pq'] ?? null;
    $matricula_dp = $_POST['matr_dp'] ?? null;
    $cedula_comprador = $_POST['cedula_comprador'] ?? null;

    // Validar campos requeridos
    if (!$matricula_ap || !$matricula_pq || !$matricula_dp || !$cedula_comprador) {
        $error_message = 'Todos los campos son necesarios.';
    } else {
        // Sanitizar entradas
        $matricula_ap = $conn->real_escape_string($matricula_ap);
        $matricula_pq = $conn->real_escape_string($matricula_pq);
        $matricula_dp = $conn->real_escape_string($matricula_dp);
        $cedula_comprador = $conn->real_escape_string($cedula_comprador);

        // Consultar información de los compradores
        $compradores_info = [];
        foreach ($compradores as $index => $comprador_cc) {
            if ($comprador_cc) {
                $query = "SELECT nombre_comp" . ($index + 1) . " AS nombre, cc_comp" . ($index + 1) . " AS cedula FROM comprador_" . ($index + 1) . " WHERE cc_comp" . ($index + 1) . " = '$comprador_cc'";
                $result = $conn->query($query);
                if ($result && $result->num_rows > 0) {
                    $compradores_info[$index] = $result->fetch_assoc();
                } else {
                    $compradores_info[$index] = ['nombre' => 'No definido', 'cedula' => ''];
                }
            }
        }

        // Construir la consulta SQL para obtener detalles adicionales del inmueble
        $query_inmueble = "
        SELECT 
            tipo_inm, 
            num_inm, 
            torre_inm,
            vlr_inm,
            matr_inm
        FROM inmuebles
        WHERE matr_inm IN ('$matricula_ap', '$matricula_pq', '$matricula_dp')
        ";

        // Ejecutar la consulta y obtener los resultados
        $result_inmueble = $conn->query($query_inmueble);

        // Inicializar variables para tipo, número, torre, y valor
        $inmuebles = [
            'ap' => ['tipo' => null, 'num' => null, 'torre' => null, 'vlr' => 0],
            'pq' => ['tipo' => null, 'num' => null, 'torre' => null, 'vlr' => 0],
            'dp' => ['tipo' => null, 'num' => null, 'torre' => null, 'vlr' => 0],
        ];
        $total_vlr_vta = 0;

        // Verificar si la consulta tuvo resultados
        if ($result_inmueble && $result_inmueble->num_rows > 0) {
            // Recorrer los resultados y asignar los valores a cada matrícula
            while ($row = $result_inmueble->fetch_assoc()) {
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

                // Asignar los datos de los compradores solo si tienen cédula
                foreach ($compradores_info as $index => $comprador) {
                    if ($comprador['cedula']) {
                        $templateProcessor->setValue("nombre_comp" . ($index + 1), $comprador['nombre']);
                        $templateProcessor->setValue("cc_comp" . ($index + 1), 'C.C. No. ' . $comprador['cedula']);
                    } else {
                        $templateProcessor->deleteBlock("comprador" . ($index + 1)); // Elimina el bloque si no hay datos
                    }
                }

                // Rellenar los campos de la plantilla solo si hay datos válidos
                $templateProcessor->setValue('nombre_comp1', $comprador1['nombre_comp1'] ?? 'No definido');
                $templateProcessor->setValue('cc_comp1', $comprador1['cc_comp1'] ?? 'No definido');

                if ($comprador_2_cc) {
                    $templateProcessor->setValue('nombre_comp2', $comprador2['nombre_comp2'] ?? 'No definido');
                    $templateProcessor->setValue('cc_comp2', $comprador2['cc_comp2'] ?? 'No definido');
                } else {
                    // Eliminar los campos si no hay segundo comprador
                    $templateProcessor->setValue('nombre_comp2', '');
                    $templateProcessor->setValue('cc_comp2', '');
                }

                if ($comprador_3_cc) {
                    $templateProcessor->setValue('nombre_comp3', $comprador3['nombre_comp3'] ?? 'No definido');
                    $templateProcessor->setValue('cc_comp3', $comprador3['cc_comp3'] ?? 'No definido');
                } else {
                    // Eliminar los campos si no hay tercer comprador
                    $templateProcessor->setValue('nombre_comp3', '');
                    $templateProcessor->setValue('cc_comp3', '');
                }

                if ($comprador_4_cc) {
                    $templateProcessor->setValue('nombre_comp4', $comprador4['nombre_comp4'] ?? 'No definido');
                    $templateProcessor->setValue('cc_comp4', $comprador4['cc_comp4'] ?? 'No definido');
                } else {
                    // Eliminar los campos si no hay cuarto comprador
                    $templateProcessor->setValue('nombre_comp4', '');
                    $templateProcessor->setValue('cc_comp4', '');
                }

                // Reemplazar las matrículas en el documento
                foreach (['ap', 'pq', 'dp'] as $key) {
                    $templateProcessor->setValue("matr_$key", ${"matricula_$key"});
                    $templateProcessor->setValue("tipo_$key", $inmuebles[$key]['tipo']);
                    $templateProcessor->setValue("num_$key", $inmuebles[$key]['num']);
                    $templateProcessor->setValue("torre_$key", $inmuebles[$key]['torre']);
                    $templateProcessor->setValue("vlr_$key", $inmuebles[$key]['vlr']);
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
                <h1>GESTIÓN DE PLANTILLAS</h1>
                <form action="10Generarescritura.php" method="POST" class="styled-form">

                    <div class="fpago">
                        <label for="tipo_escritura">FORMA PAGO:</label>
                        <select name="tipo_escritura" id="tipo_escritura" required onchange="mostrarBanco()">
                            <option value="Contado">CONTADO</option>
                            <option value="Hipoteca">HIPOTECA</option>
                            <option value="Leasing">LEASING</option>
                        </select><br>

                        <!-- Contenedor para elegir el banco -->
                        <div class="opcion-banco" id="opcion-banco">
                            <select name="banco" id="nombre_bco" required>
                                <option value="" disabled selected>BANCO:</option>
                                <?php
                                if ($result_banco->num_rows > 0) {
                                    while ($row = $result_banco->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row['nombre_bco']) . "'>" . htmlspecialchars($row['nombre_bco']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Contenedor de Matrículas -->
                    <div class="inmuebles">
                        <div class="matricula">
                            <label for="matr_ap">MATR.AP:</label>
                            <input type="text" id="matr_ap" name="matr_ap" value="<?= htmlspecialchars($matricula_ap ?? '') ?>" required>
                        </div>
                        <div class="matricula">
                            <label for="matr_pq">MATR.PQ:</label>
                            <input type="text" id="matr_pq" name="matr_pq" value="<?= htmlspecialchars($matricula_pq ?? '') ?>" required>
                        </div>
                        <div class="matricula">
                            <label for="matr_dp">MATR.DP:</label>
                            <input type="text" id="matr_dp" name="matr_dp" value="<?= htmlspecialchars($matricula_dp ?? '') ?>" required>
                        </div>
                    </div>

                    <!-- Selector para añadir más compradores -->
                    <label for="compradores">CANT. COMPR.</label>
                    <select name="compradores" id="compradores" onchange="toggleAdditionalBuyers()" required>
                        <option value="1">Solo 1 comprador</option>
                        <option value="2">2 compradores</option>
                        <option value="3">3 compradores</option>
                        <option value="4">4 compradores</option>
                    </select>


                    <!-- CC COMPRADOR 1 -->
                    <div class="form-row">
                        <label for="cedula_comprador">CC COMPRADOR 1:</label>
                        <input type="text" id="cedula_comprador" name="cedula_comprador" value="<?= htmlspecialchars($cedula_comprador ?? '') ?>" required>

                        <select name="expcc_comp1" id="expcc_comp1" required>
                            <option value="" disabled selected>Expedida en:</option>
                            <?php foreach ($municipios as $municipio): ?>
                                <option value="<?= htmlspecialchars($municipio) ?>"><?= htmlspecialchars($municipio) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- ESTADO CIVIL -->
                    <div class="form-row">
                        <label for="estado_civil">ESTADO CIVIL:</label>
                        <select name="estado_civil" id="estado_civil" required>
                            <option value="" disabled selected>Seleccione opción</option>
                            <?php foreach ($estados_civiles as $estado): ?>
                                <option value="<?= htmlspecialchars($estado) ?>"><?= htmlspecialchars($estado) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- LUGAR EXP.CC -->
                    <div class="form-row">
                        <label for="lugar_expcc">LUGAR EXP.CC:</label>
                        <select name="lugar_expcc" id="lugar_expcc" required>
                            <option value="" disabled selected>Expedida en:</option>
                            <?php foreach ($municipios as $municipio): ?>
                                <option value="<?= htmlspecialchars($municipio) ?>"><?= htmlspecialchars($municipio) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- LUGAR DOMICILIO -->
                    <div class="form-row">
                        <label for="lugar_domicilio">LUGAR DOMICILIO:</label>
                        <select name="lugar_domicilio" id="lugar_domicilio" required>
                            <option value="" disabled selected>Seleccione opción</option>
                            <?php foreach ($municipios as $municipio): ?>
                                <option value="<?= htmlspecialchars($municipio) ?>"><?= htmlspecialchars($municipio) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Campos adicionales para otros compradores, inicialmente ocultos -->
                    <div id="additional-buyers">
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

                    <button type="submit" name="ejecutar">GENERAR DOCUMENTO</button>
                </form>

                <!-- Mostrar mensaje de error si existe -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Mostrar los campos adicionales para los compradores
        function toggleAdditionalBuyers() {
            const numBuyers = document.getElementById("compradores").value;
            for (let i = 2; i <= 4; i++) {
                const buyerDiv = document.getElementById("buyer" + i);
                if (i <= numBuyers) {
                    buyerDiv.style.display = "block";
                } else {
                    buyerDiv.style.display = "none";
                }
            }
        }

        // Mostrar banco solo si se selecciona Hipoteca o Leasing
        function mostrarBanco() {
            const tipoEscritura = document.getElementById("tipo_escritura").value;
            const bancoDiv = document.getElementById("opcion-banco");
            if (tipoEscritura === "Hipoteca" || tipoEscritura === "Leasing") {
                bancoDiv.style.display = "block";
            } else {
                bancoDiv.style.display = "none";
            }
        }
    </script>
</body>

</html>