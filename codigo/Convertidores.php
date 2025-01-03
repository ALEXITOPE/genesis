<?php

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
    // Función para mostrar u ocultar los campos de los compradores adicionales
    function toggleAdditionalBuyers() {
        var buyersCount = document.getElementById("compradores").value;

        // Ocultar todos los campos adicionales inicialmente
        document.getElementById("buyer2").style.display = "none";
        document.getElementById("buyer3").style.display = "none";
        document.getElementById("buyer4").style.display = "none";

        // Mostrar los campos necesarios según la cantidad de compradores
        if (buyersCount >= 2) {
            document.getElementById("buyer2").style.display = "block"; // Mostrar el campo del comprador 2
        }
        if (buyersCount >= 3) {
            document.getElementById("buyer3").style.display = "block"; // Mostrar el campo del comprador 3
        }
        if (buyersCount >= 4) {
            document.getElementById("buyer4").style.display = "block"; // Mostrar el campo del comprador 4
        }
    }
</script>

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
// Obtener los datos para las listas desplegables
$sql_municipios = "SELECT nombre_mun FROM municipios";
$result_municipios = $conn->query($sql_municipios);
$municipios = [];
if ($result_municipios->num_rows > 0) {
    while ($row = $result_municipios->fetch_assoc()) {
        $municipios[] = $row['nombre_mun'];
    }

    $sql_estado_civil = "SELECT nombre_escivil FROM estados_civiles";
    $result_estado_civil = $conn->query($sql_estado_civil);
    $estados_civiles = [];
    if ($result_estado_civil->num_rows > 0) {
        while ($row = $result_estado_civil->fetch_assoc()) {
            $estados_civiles[] = $row['nombre_escivil'];
        }
    }
}

//función para traer datos de los COMPRADORES y de los INMUEBLES
function obtenerDatosInmuebleYCompradores($conexion, $matricula_ap, $matricula_pq, $matricula_dp)
{
    // Validar que las matrículas no estén vacías
    if (empty($matricula_ap) || empty($matricula_pq) || empty($matricula_dp)) {
        return []; // Devuelve un array vacío si alguna matrícula está vacía
    }

    $query_unificada = "
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
        inmuebles.matr_inm IN ('$matricula_ap', '$matricula_pq', '$matricula_dp')
    ";

    $resultado = $conexion->query($query_unificada);

    $datos = [];
    if ($resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $datos[] = $fila;
        }
    }
    return $datos;
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


function modificarDatosInmuebleYCompradores($conexion, $matricula_ap, $matricula_pq, $matricula_dp, $datos_inmuebles, $datos_compradores)
{
    // Validar que las matrículas no estén vacías
    if (empty($matricula_ap) || empty($matricula_pq) || empty($matricula_dp)) {
        return 'Las matrículas no pueden estar vacías.';
    }

    // Preparar la consulta unificada de actualización
    $query_unificada = "
    SELECT 
        inmuebles.id_inmueble, inmuebles.tipo_inm, inmuebles.num_inm, inmuebles.torre_inm,
        inmuebles.vlr_inm, inmuebles.matr_inm,
        compradores.id_comp, compradores.nombre_comp1, compradores.cc_comp1, compradores.expcc_comp1, 
        compradores.dom_comp1, compradores.escivil_comp1, compradores.nombre_comp2, compradores.cc_comp2,
        compradores.expcc_comp2, compradores.dom_comp2, compradores.escivil_comp2, 
        compradores.nombre_comp3, compradores.cc_comp3, compradores.expcc_comp3, compradores.dom_comp3,
        compradores.escivil_comp3, compradores.nombre_comp4, compradores.cc_comp4, compradores.expcc_comp4, 
        compradores.dom_comp4, compradores.escivil_comp4
    FROM 
        inmuebles
    LEFT JOIN 
        compradores ON inmuebles.matr_inm = compradores.matr_inm
    WHERE 
        inmuebles.matr_inm IN ('$matricula_ap', '$matricula_pq', '$matricula_dp')
    ";

    $resultado = $conexion->query($query_unificada);
    $datos = [
        'inmuebles' => [],
        'compradores' => []
    ];

    if ($resultado && $resultado->num_rows > 0) {
        // Actualización de los datos de inmuebles
        foreach ($datos_inmuebles as $inmueble) {
            $id_inmueble = $inmueble['id_inmueble'];
            $tipo = $conexion->real_escape_string($inmueble['tipo_inm']);
            $numero = $conexion->real_escape_string($inmueble['num_inm']);
            $torre = $conexion->real_escape_string($inmueble['torre_inm']);
            $valor = $conexion->real_escape_string($inmueble['vlr_inm']);
            $matricula = $conexion->real_escape_string($inmueble['matr_inm']);

            // Realizar el UPDATE en la tabla inmuebles
            $sql_inmueble = "UPDATE inmuebles SET tipo_inm='$tipo', num_inm='$numero', torre_inm='$torre', 
                             vlr_inm='$valor', matr_inm='$matricula' WHERE id_inmueble='$id_inmueble'";
            $conexion->query($sql_inmueble);
        }

        // Actualización de los datos de compradores
        foreach ($datos_compradores as $comprador) {
            $id_comprador = $comprador['id_comp'];
            $nombre = $conexion->real_escape_string($comprador['nombre_comp']);
            $cedula = $conexion->real_escape_string($comprador['cc_comp']);
            $expedicion = $conexion->real_escape_string($comprador['expcc_comp']);
            $domicilio = $conexion->real_escape_string($comprador['dom_comp']);
            $estado_civil = $conexion->real_escape_string($comprador['escivil_comp']);

            // Realizar el UPDATE en la tabla compradores
            $sql_comprador = "UPDATE compradores SET nombre_comp='$nombre', cc_comp='$cedula', expcc_comp='$expedicion', 
                             dom_comp='$domicilio', escivil_comp='$estado_civil' WHERE id_comp='$id_comprador'";
            $conexion->query($sql_comprador);
        }

        return 'Datos actualizados correctamente.';
    } else {
        return 'No se encontraron registros para actualizar.';
    }
}
