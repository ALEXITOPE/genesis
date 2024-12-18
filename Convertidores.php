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

    // Llamar a la función para aplicar el comportamiento al cargar la página
    toggleAdditionalBuyers();
</script>

<script>
    function mostrarBanco() {
        const tipoPago = document.getElementById('tipo_escritura').value;
        const opcionBanco = document.getElementById('opcion-banco');

        // Mostrar el campo de selección de banco solo si es Hipoteca o Leasing
        if (tipoPago === "Hipoteca" || tipoPago === "Leasing") {
            opcionBanco.style.display = "block"; // Mostrar
        } else {
            opcionBanco.style.display = "none"; // Ocultar
        }
    }
    // Llamar a la función para aplicar el comportamiento al cargar la página
    mostrarBanco();
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
