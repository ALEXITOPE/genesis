// Array de compradores extraído desde la base de datos
$compradores = [
    ['nombre' => 'Juan Pérez', 'domicilio' => 'Bogotá', 'cedula' => '12345678', 'expcc' => 'Bogotá', 'escivil' => 'Soltero'],
    ['nombre' => 'María Gómez', 'domicilio' => 'Medellín', 'cedula' => '87654321', 'expcc' => 'Medellín', 'escivil' => 'Casada'],
    // Agrega más compradores según sea necesario
];

// Inicializar variables para texto dinámico
$nombres = [];
$domicilios = [];
$cedulas = [];
$expcc = [];
$estados_civiles = [];

// Construir las listas de valores
foreach ($compradores as $index => $comprador) {
    $nombres[] = $comprador['nombre'];
    $domicilios[] = $comprador['domicilio'];
    $cedulas[] = $comprador['cedula'];
    $expcc[] = $comprador['expcc'];
    $estados_civiles[] = $comprador['escivil'];
}

// Función para unir elementos con "y" solo donde corresponde
function unir_con_y($elementos) {
    $ultimo = array_pop($elementos);
    if (count($elementos) > 0) {
        return implode(', ', $elementos) . " y " . $ultimo;
    }
    return $ultimo;
}

// Construir los textos finales
$nombres_texto = unir_con_y($nombres);
$domicilios_texto = unir_con_y($domicilios);
$cedulas_texto = unir_con_y($cedulas);
$expcc_texto = unir_con_y($expcc);
$estados_civiles_texto = unir_con_y($estados_civiles);

// Plantilla con placeholders
$plantilla = "de la otra parte: ${nombre_comp1}, mayor(es) de edad, domiciliado(a)(s) y residente(s) en ${dom_comp1}, identificado(a)(s) con la(s) cédula(s) de ciudadanía número(s) ${cc_comp1}, expedida(s) en ${expcc_comp1}, de estado civil ${escivil_comp1}.";

// Reemplazar los placeholders en la plantilla
$plantilla = str_replace('${nombre_comp1}', $nombres_texto, $plantilla);
$plantilla = str_replace('${dom_comp1}', $domicilios_texto, $plantilla);
$plantilla = str_replace('${cc_comp1}', $cedulas_texto, $plantilla);
$plantilla = str_replace('${expcc_comp1}', $expcc_texto, $plantilla);
$plantilla = str_replace('${escivil_comp1}', $estados_civiles_texto, $plantilla);

// Mostrar el texto final
echo $plantilla;
