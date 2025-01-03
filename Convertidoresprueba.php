<?php
require 'vendor/autoload.php';
require_once 'ConexionBaseDatos.php';
session_start();

function obtenerDatosInmuebleYCompradores($conexion, $matricula_ap, $matricula_pq, $matricula_dp) 
{
    // Validar que las matrículas no estén vacías
    if (empty($matricula_ap) && empty($matricula_pq) && empty($matricula_dp)) {
        return []; // Devuelve un array vacío si todas las matrículas están vacías
    }

    // Construir la consulta dinámicamente para manejar matrículas no proporcionadas
    $matriculas = [];
    if (!empty($matricula_ap)) $matriculas[] = $matricula_ap;
    if (!empty($matricula_pq)) $matriculas[] = $matricula_pq;
    if (!empty($matricula_dp)) $matriculas[] = $matricula_dp;
    $matriculas_in = "'" . implode("','", $matriculas) . "'";

    $query = "
        SELECT 
            inmuebles.tipo_inm, 
            inmuebles.num_inm, 
            inmuebles.torre_inm,
            inmuebles.vlr_inm,
            inmuebles.matr_inm,
            compradores.nombre_comp, 
            compradores.cc_comp, 
            compradores.expcc_comp, 
            compradores.dom_comp, 
            compradores.escivil_comp
        FROM 
            inmuebles
        LEFT JOIN 
            compradores 
        ON 
            inmuebles.matr_inm = compradores.matr_inm
        WHERE 
            inmuebles.matr_inm IN ($matriculas_in)
    ";

    $resultado = $conexion->query($query);

    $datos = [];
    if ($resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $datos[] = $fila; // Cada fila representa un comprador asociado a una matrícula
        }
    }
    return $datos;
}
?>

<?php
function generarFormularioDatos($datos_inmueble_compradores) {
    // Comprobar si hay datos disponibles
    if (empty($datos_inmueble_compradores)) {
        return "<p>No se encontraron datos para las matrículas proporcionadas.</p>";
    }

    $html = "<h2>Datos del Inmueble y Compradores</h2>";
    foreach ($datos_inmueble_compradores as $dato) {
        $html .= '<form method="POST" action="" class="styled-formuestra">';
        $html .= "<h3>Inmueble:</h3>";
        $html .= '<label>Tipo:</label>';
        $html .= '<input type="text" name="tipo_inm" value="' . htmlspecialchars($dato['tipo_inm']) . '"><br>';

        $html .= '<label>Numero:</label>';
        $html .= '<input type="text" name="num_inm" value="' . htmlspecialchars($dato['num_inm']) . '"><br>';

        $html .= '<label>Torre:</label>';
        $html .= '<input type="text" name="torre_inm" value="' . htmlspecialchars($dato['torre_inm']) . '"><br>';

        $html .= '<label>Valor:</label>';
        $html .= '<input type="text" name="vlr_inm" value="' . htmlspecialchars($dato['vlr_inm']) . '"><br>';

        $html .= '<label>Matrícula:</label>';
        $html .= '<input type="text" value="' . htmlspecialchars($dato['matr_inm']) . '" readonly><br>';

        // Generar campos dinámicos para los compradores
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($dato['nombre_comp'])) {
                $html .= "<h3>Comprador $i:</h3>";
                $html .= '<label>Nombre:</label>';
                $html .= '<input type="text" name="nombre_comp' . $i . '" value="' . htmlspecialchars($dato['nombre_comp']) . '"><br>';

                $html .= '<label>Cédula:</label>';
                $html .= '<input type="text" name="cc_comp' . $i . '" value="' . htmlspecialchars($dato['cc_comp']) . '"><br>';

                $html .= '<label>Expedición Cédula:</label>';
                $html .= '<input type="text" name="expcc_comp' . $i . '" value="' . htmlspecialchars($dato['expcc_comp']) . '"><br>';

                $html .= '<label>Dirección:</label>';
                $html .= '<input type="text" name="dom_comp' . $i . '" value="' . htmlspecialchars($dato['dom_comp']) . '"><br>';

                $html .= '<label>Estado Civil:</label>';
                $html .= '<input type="text" name="escivil_comp' . $i . '" value="' . htmlspecialchars($dato['escivil_comp']) . '"><br>';
            }
        }

        $html .= '<button type="submit" name="actualizar">Actualizar Datos</button>';
        $html .= '</form><hr>';
    }

    return $html;
}
?>
