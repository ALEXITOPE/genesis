<?php
require 'vendor/autoload.php';
require_once 'ConexionBaseDatos.php';
require_once 'Convertidores.php';
session_start();

// FUNCION NUMERO A LETRAS
function numeroALetras($numero, $esMoneda = false, $convertirMayusculas = false)
{
    $unidades = ["", "uno", "dos", "tres", "cuatro", "cinco", "seis", "siete", "ocho", "nueve", "diez", "once", "doce", "trece", "catorce", "quince", "dieciseis", "diecisiete", "dieciocho", "diecinueve"];
    $decenas = ["", "", "veinte", "treinta", "cuarenta", "cincuenta", "sesenta", "setenta", "ochenta", "noventa"];
    $centenas = ["", "ciento", "doscientos", "trescientos", "cuatrocientos", "quinientos", "seiscientos", "setecientos", "ochocientos", "novecientos"];

    $resultado = "";

    if ($numero >= 0 && $numero <= 19) {
        $resultado = $unidades[$numero] . ($esMoneda && $numero == 1 ? "ún" : "");
    } elseif ($numero >= 20 && $numero <= 29) {
        $resultado = $numero == 20 ? "veinte" : "veinti" . $unidades[$numero - 20];
    } elseif ($numero >= 30 && $numero <= 99) {
        $resultado = $decenas[(int)($numero / 10)] . ($numero % 10 !== 0 ? " y " . $unidades[$numero % 10] : "");
    } elseif ($numero == 100) {
        $resultado = "cien";
    } elseif ($numero >= 101 && $numero <= 999) {
        $resultado = $centenas[(int)($numero / 100)] . ($numero % 100 !== 0 ? " " . numeroALetras($numero % 100, $esMoneda) : "");
    } elseif ($numero >= 1000 && $numero <= 999999) {
        $miles = (int)($numero / 1000);
        $resultado = ($miles == 1 ? "mil" : numeroALetras($miles, $esMoneda) . " mil") . ($numero % 1000 !== 0 ? " " . numeroALetras($numero % 1000, $esMoneda) : "");
    } elseif ($numero >= 1000000 && $numero <= 999999999) {
        $millones = (int)($numero / 1000000);
        $resultado = ($millones == 1 ? "un millón" : numeroALetras($millones, $esMoneda) . " millones") . ($numero % 1000000 !== 0 ? " " . numeroALetras($numero % 1000000, $esMoneda) : "");
    } elseif ($numero >= 1000000000 && $numero <= 9999999999) {
        $milmillones = (int)($numero / 1000000000);
        $resultado = ($milmillones == 1 ? "mil millones" : numeroALetras($milmillones, $esMoneda) . " mil millones") . ($numero % 1000000000 !== 0 ? " " . numeroALetras($numero % 1000000000, $esMoneda) : "");
    } else {
        $resultado = "Número fuera de rango";
    }

    return $convertirMayusculas ? strtoupper($resultado) : strtolower($resultado);
}

// FUNCION FECHA A LETRAS
function fechaALetras($fecha, $convertirMayusculas = false)
{
    $dia = (int)date("d", strtotime($fecha));
    $mes = (int)date("m", strtotime($fecha));
    $anio = (int)date("Y", strtotime($fecha));

    $meses = ["", "enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];

    $diaLetras = $dia == 1 ? "primero" : numeroALetras($dia);
    $anioLetras = numeroALetras($anio);

    $resultado = "$diaLetras de {$meses[$mes]} de $anioLetras";

    return $convertirMayusculas ? strtoupper($resultado) : strtolower($resultado);
}

// FUNCION CONVERTIR MONEDA
function convertirMoneda($numero, $convertirMayusculas = false)
{
    $resultado = numeroALetras($numero, true) . " pesos moneda corriente";

    return $convertirMayusculas ? strtoupper($resultado) : strtolower($resultado);
}

// FUNCION GENERICA PARA CAMPOS
function convertirCampo($campo, $convertirMayusculas = false)
{
    return $convertirMayusculas ? strtoupper($campo) : strtolower($campo);
}

function convertirTexto($texto, $convertir_a_mayusculas = true)
{
    if ($convertir_a_mayusculas) {
        return strtoupper($texto); // Convierte a mayúsculas
    } else {
        return strtolower($texto); // Convierte a minúsculas
    }
}

function prepararValores($inmuebles, $total_vlr_vta)
{
    // Convertir total de venta
    $total_vlr_vta_letras = numeroALetras($total_vlr_vta);
    $total_vlr_vta_letras_min = strtolower($total_vlr_vta_letras);
    $total_vlr_vta_letras_may = strtoupper($total_vlr_vta_letras);

    // Procesar cada tipo de inmueble
    foreach (['ap', 'pq', 'dp'] as $key) {
        $inmuebles[$key]['vlr_letras'] = numeroALetras($inmuebles[$key]['vlr']);
        $inmuebles[$key]['vlr_letras_min'] = strtolower($inmuebles[$key]['vlr_letras']);
        $inmuebles[$key]['vlr_letras_may'] = strtoupper($inmuebles[$key]['vlr_letras']);

        $inmuebles[$key]['num_letras'] = numeroALetras($inmuebles[$key]['num']);
        $inmuebles[$key]['num_letras_min'] = strtolower($inmuebles[$key]['num_letras']);
        $inmuebles[$key]['num_letras_may'] = strtoupper($inmuebles[$key]['num_letras']);

        $inmuebles[$key]['torre_letras'] = $inmuebles[$key]['torre'] ? numeroALetras($inmuebles[$key]['torre']) : '';
        $inmuebles[$key]['torre_letras_min'] = strtolower($inmuebles[$key]['torre_letras']);
        $inmuebles[$key]['torre_letras_may'] = strtoupper($inmuebles[$key]['torre_letras']);
    }

    return [
        'total_vlr_vta_letras_min' => $total_vlr_vta_letras_min,
        'total_vlr_vta_letras_may' => $total_vlr_vta_letras_may,
        'inmuebles' => $inmuebles
    ];
}
?>

<script>
    // Mostrar/Ocultar la opción de banco según la forma de pago
    function mostrarBanco() {
        const tipoPago = document.getElementById('tipo_escritura').value;
        const opcionBanco = document.getElementById('opcion-banco');
        opcionBanco.style.display = (tipoPago === 'Hipoteca' || tipoPago === 'Leasing') ? 'block' : 'none';
    }
    // Inicializar
    document.addEventListener('DOMContentLoaded', mostrarBanco);
</script>

<?php
function generarOpciones($opciones, $valorSeleccionado) {
    $html = '';
    foreach ($opciones as $opcion) {
        $seleccionado = ($opcion['id'] == $valorSeleccionado) ? 'selected' : '';
        $html .= "<option value=\"{$opcion['id']}\" $seleccionado>{$opcion['nombre']}</option>";
    }
    return $html;
}

function generarParrafoDesdeDatos($datos)
{
    // Inicializar arreglos para almacenar datos dinámicos
    $nombres = [];
    $domicilios = [];
    $cedulas = [];
    $expediciones = [];
    $estadosCiviles = [];

    // Iterar sobre los compradores para agregar solo los que existan
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($datos["nombre_comp$i"])) {
            $nombres[] = $datos["nombre_comp$i"];
            $domicilios[] = $datos["dom_comp$i"];
            $cedulas[] = $datos["cc_comp$i"];
            $expediciones[] = $datos["expcc_comp$i"];
            $estadosCiviles[] = $datos["escivil_comp$i"];
        }
    }

    // Contar la cantidad inicial de compradores
    $cantidadCompradores = count($nombres);

    // Función auxiliar para formatear listas con "y"
    function formatearListaConY($lista)
    {
        if (count($lista) > 1) {
            $ultimoElemento = array_pop($lista); // Obtener el último elemento
            return implode(", ", $lista) . " y " . $ultimoElemento; // Reunir la lista
        } else {
            return implode(", ", $lista); // Si es uno solo, mantenerlo
        }
    }

    // Aplicar el formato a cada arreglo
    $nombresFormateados = formatearListaConY($nombres);
    $domiciliosFormateados = formatearListaConY($domicilios);
    $cedulasFormateadas = formatearListaConY($cedulas);
    $expedicionesFormateadas = formatearListaConY($expediciones);
    $estadosCivilesFormateados = formatearListaConY($estadosCiviles);

    // Construir el párrafo dinámico
    $parrafo =
        $nombresFormateados .
        ", mayor(es) de edad, domiciliado(a)(s) y residente(s) en " .
        $domiciliosFormateados .
        ", identificado(a)(s) con la(s) cédula(s) de ciudadanía número(s) " .
        $cedulasFormateadas .
        ", expedida(s) en " .
        $expedicionesFormateadas .
        ", de estado civil " .
        $estadosCivilesFormateados .
        ", " .
        "respectivamente, quien(es) obra(n) en nombre propio y en adelante se denominará(n) EL (LA, LOS, LAS) COMPRADOR (A, ES, AS) y manifestó(aron) que ha(n) celebrado contrato de compraventa contenido en las siguientes cláusulas, previas las siguientes:";

    // Eliminar la palabra "respectivamente" si hay un solo comprador
    if ($cantidadCompradores === 1) {
        $parrafo = str_replace(", respectivamente", "", $parrafo);
    }

    return $parrafo;
}


function obtenerDatosInmuebleYCompradores($conn, $matricula_ap, $matricula_pq, $matricula_dp) {
    // Preparar la consulta SQL con parámetros
    $query = "
        SELECT 
            inmuebles.tipo_inm, 
            inmuebles.num_inm, 
            inmuebles.torre_inm,
            inmuebles.vlr_inm,
            inmuebles.matr_inm,
            compradores.nombre_comp1, 
            compradores.cc_comp1, 
            compradores.expcc_comp1, 
            compradores.dom_comp1, 
            compradores.escivil_comp1,
            compradores.nombre_comp2, 
            compradores.cc_comp2, 
            compradores.expcc_comp2, 
            compradores.dom_comp2, 
            compradores.escivil_comp2,
            compradores.nombre_comp3, 
            compradores.cc_comp3, 
            compradores.expcc_comp3, 
            compradores.dom_comp3, 
            compradores.escivil_comp3,
            compradores.nombre_comp4, 
            compradores.cc_comp4, 
            compradores.expcc_comp4, 
            compradores.dom_comp4, 
            compradores.escivil_comp4
        FROM 
            inmuebles
        LEFT JOIN 
            compradores 
        ON 
            inmuebles.matr_inm = compradores.matr_inm
        WHERE 
            inmuebles.matr_inm IN (?, ?, ?)";
    
    // Preparar la sentencia
    if ($stmt = $conn->prepare($query)) {
        // Vincular los parámetros
        $stmt->bind_param('sss', $matricula_ap, $matricula_pq, $matricula_dp);
        
        // Ejecutar la consulta
        if (!$stmt->execute()) {
            return "Error en la ejecución de la consulta: " . $stmt->error;
        }

        // Obtener los resultados
        $result = $stmt->get_result();
        
        // Verificar si hay resultados
        if ($result && $result->num_rows > 0) {
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            return $data;
        } else {
            return "No se encontraron resultados para las matrículas proporcionadas.";
        }
        
        // Cerrar la sentencia
        $stmt->close();
    } else {
        // Manejar el error en la preparación de la consulta
        return "Error en la preparación de la consulta: " . $conn->error;
    }
    echo $query;  // Depuración
}


// Función para actualizar los datos en la base de datos
function actualizarBaseDatos($conn, $datos_inmuebles, $datos_compradores) {
    // Inicia la transacción
    $conn->begin_transaction();

    try {
        // Actualización de inmuebles y compradores en una sola consulta
        $update_sql = "
            UPDATE inmuebles AS i
            JOIN compradores AS c ON c.matr_inm = i.matr_inm
            SET
                i.tipo_inm = ?, 
                i.num_inm = ?, 
                i.torre_inm = ?, 
                i.vlr_inm = ?, 
                c.nombre_comp = ?, 
                c.cc_comp = ?, 
                c.expcc_comp = ?, 
                c.escivil_comp = ?, 
                c.domicilio_comp = ?
            WHERE i.matr_inm = ?;
        ";

        // Preparar la consulta para actualización conjunta
        if ($stmt = $conn->prepare($update_sql)) {
            foreach ($datos_inmuebles as $index => $inmueble) {
                $comprador = $datos_compradores[$index]; // Se asume que los datos de compradores están alineados con los de inmuebles
                
                // Asignar los parámetros
                $stmt->bind_param(
                    'ssssssssss', 
                    $inmueble['tipo_inm'], 
                    $inmueble['num_inm'], 
                    $inmueble['torre_inm'], 
                    $inmueble['vlr_inm'], 
                    $comprador['nombre_comp'], 
                    $comprador['cc_comp'], 
                    $comprador['expcc_comp'], 
                    $comprador['escivil_comp'], 
                    $comprador['domicilio_comp'], 
                    $inmueble['matr_inm'] // Matrícula para la cláusula WHERE
                );

                // Ejecutar la consulta
                if (!$stmt->execute()) {
                    throw new Exception("Error al actualizar inmueble y comprador: " . $stmt->error);
                }
            }
            $stmt->close();
        } else {
            throw new Exception("Error en la preparación de la consulta de actualización conjunta: " . $conn->error);
        }

        // Confirma la transacción
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Si ocurre un error, revierte los cambios
        $conn->rollback();
        error_log("Error al actualizar datos: " . $e->getMessage());
        return false;
    }
}