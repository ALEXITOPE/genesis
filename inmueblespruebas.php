<?php
require 'vendor/autoload.php';
require_once 'ConexionBaseDatos.php';
require_once 'Convertidores.php'; // Para convertir números a letras
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($conn) || $conn->connect_error) {
    die('Error en la conexión a la base de datos.');
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_escritura = $_POST['tipo_escritura'] ?? 'contado';
    $matricula_ap = $_POST['matr_ap'] ?? null;
    $matricula_pq = $_POST['matr_pq'] ?? null;
    $matricula_dp = $_POST['matr_dp'] ?? null;
    $tipo_afec = $_POST['tipo_afec'] ?? null;

    if (!$matricula_ap || !$matricula_pq || !$matricula_dp || !$tipo_afec) {
        $error_message = 'Todos los campos son necesarios.';
    } else {
        $matriculas = [
            'ap' => $conn->real_escape_string($matricula_ap),
            'pq' => $conn->real_escape_string($matricula_pq),
            'dp' => $conn->real_escape_string($matricula_dp)
        ];

        $inmuebles = [
            'ap' => ['tipo' => '', 'num' => '', 'torre' => '', 'vlr' => '', 'num_letras' => '', 'torre_letras' => ''],
            'pq' => ['tipo' => '', 'num' => '', 'torre' => '', 'vlr' => '', 'num_letras' => '', 'torre_letras' => ''],
            'dp' => ['tipo' => '', 'num' => '', 'torre' => '', 'vlr' => '', 'num_letras' => '', 'torre_letras' => '']
        ];

        $query_inmueble = "
        SELECT 
            tipo_inm, num_inm, torre_inm, vlr_inm, matr_inm
        FROM inmuebles
        WHERE matr_inm IN ('{$matriculas['ap']}', '{$matriculas['pq']}', '{$matriculas['dp']}')
        ";
        $result_inmueble = $conn->query($query_inmueble);

        if ($result_inmueble && $result_inmueble->num_rows > 0) {
            while ($row = $result_inmueble->fetch_assoc()) {
                foreach (['ap', 'pq', 'dp'] as $key) {
                    if ($row['matr_inm'] == $matriculas[$key]) {
                        $inmuebles[$key] = [
                            'tipo' => $row['tipo_inm'],
                            'num' => $row['num_inm'],
                            'torre' => $row['torre_inm'],
                            'vlr' => $row['vlr_inm'],
                            'num_letras' => convertirNumeroALetras($row['num_inm']),
                            'torre_letras' => convertirNumeroALetras($row['torre_inm'])
                        ];
                    }
                }
            }

            $afecta_query = "SELECT tipo_afec FROM afectacion WHERE id_afec = '$tipo_afec'";
            $afecta_result = $conn->query($afecta_query);
            $afecta_text = ($afecta_result && $afecta_result->num_rows > 0) ? $afecta_result->fetch_assoc()['tipo_afec'] : 'N/A';

            $templatePath = '';
            switch (strtolower($tipo_escritura)) {
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
                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

                foreach (['ap', 'pq', 'dp'] as $key) {
                    $templateProcessor->setValue("tipo_$key", $inmuebles[$key]['tipo']);
                    $templateProcessor->setValue("num_$key", $inmuebles[$key]['num']);
                    $templateProcessor->setValue("num_{$key}_letras", $inmuebles[$key]['num_letras']);
                    $templateProcessor->setValue("torre_$key", $inmuebles[$key]['torre']);
                    $templateProcessor->setValue("torre_{$key}_letras", $inmuebles[$key]['torre_letras']);
                }

                $templateProcessor->setValue('tipo_afec', $afecta_text);

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
            $error_message = 'No se encontraron resultados para las matrículas proporcionadas.';
        }
    }
}
?>
