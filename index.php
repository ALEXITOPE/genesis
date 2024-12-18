<?php
// Incluir la conexión a la base de datos
include("ConexionBaseDatos.php");
require 'vendor/autoload.php';
use PhpOffice\PhpWord\IOFactory;

// Verificar si el formulario fue enviado
if (isset($_POST['procesar_archivo'])) {
    // Verificar si se ha subido un archivo y no hay errores
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        // Obtener el archivo temporal
        $archivoTemp = $_FILES['archivo']['tmp_name'];

        try {
            // Cargar el archivo Word
            $phpWord = IOFactory::load($archivoTemp);
            $text = "";

            // Extraer todo el texto del archivo Word
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                        $text .= $element->getText() . "\n";
                    }
                }
            }

            // Procesar el texto para extraer los datos
            $datosTabla = [];
            $lineas = explode("\n", $text); // Dividimos el texto en líneas

            foreach ($lineas as $linea) {
                $linea = trim($linea);

                // Si la línea contiene datos relevantes (incluyendo LOCAL)
                if (preg_match('/(PARQUEADERO|DEPOSITO|APARTAMENTO|LOCAL)/', $linea)) {
                    $partes = preg_split('/\s+/', $linea);

                    // Asegurarnos de que hay suficientes partes para procesar
                    if (count($partes) >= 5) {
                        // Concatenar "NRO. " antes del número del inmueble
                        $nombre_inm = $partes[0] . ' NRO. ' . $partes[1]; // Ejemplo: "LOCAL NRO. 102"
                        $coeficiente = isset($partes[5]) ? $partes[5] : '';

                        // Agregar el nombre y coeficiente a la tabla
                        $datosTabla[] = [
                            'nombre_inm' => $nombre_inm,
                            'coeficiente' => $coeficiente
                        ];
                    }
                }
            }

            // Insertar o actualizar los coeficientes en la base de datos
            foreach ($datosTabla as $dato) {
                $nombre_inm = $dato['nombre_inm'];
                $coeficiente = $dato['coeficiente'];

                $sql = "UPDATE inmuebles SET coef_inm = :coeficiente WHERE nombre_inm = :nombre_inm";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':coeficiente' => $coeficiente, ':nombre_inm' => $nombre_inm]);
            }

            // Redirigir después de procesar
            header("Location: inmueblespruebas.php?status=success");
            exit;

        } catch (Exception $e) {
            echo "Error al procesar el archivo: " . $e->getMessage();
        }

    } else {
        echo "Error al subir el archivo. Asegúrate de seleccionar un archivo válido.";
    }
}
?>

<!-- Mostrar mensaje de éxito si la actualización fue exitosa -->
<?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
    <p>Datos procesados y actualizados en la base de datos.</p>
<?php endif; ?>

