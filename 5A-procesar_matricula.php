<?php
require 'vendor/autoload.php';
require_once 'ConexionBaseDatos.php';
require_once 'Convertidores.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($conn) || $conn->connect_error) {
    die('Error en la conexión a la base de datos.');
}

// Inicializar variables del formulario
$error_message = '';
$success_message = '';
$matricula_ap = $matricula_pq = $matricula_dp = '';

// Obtener los datos para las listas desplegables
$sql_municipios = "SELECT nombre_mun FROM municipios";
$result_municipios = $conn->query($sql_municipios);

$sql_estado_civil = "SELECT nombre_escivil FROM estados_civiles";
$result_estado_civil = $conn->query($sql_estado_civil);

$sql_banco = "SELECT nombre_bco FROM bancos";
$result_banco = $conn->query($sql_banco);

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['confirmar']) && !isset($_POST['new_query'])) {
    // Obtener datos del formulario
    $tipo_escritura = $_POST['tipo_escritura'] ?? 'contado';
    $matricula_ap = $_POST['matr_ap'] ?? '';
    $matricula_pq = $_POST['matr_pq'] ?? '';
    $matricula_dp = $_POST['matr_dp'] ?? '';
    $datos_inmuebles = $_POST['datos_inmuebles'] ?? [];
    $datos_compradores = $_POST['datos_compradores'] ?? [];

    // Validar campos requeridos
    if (!$matricula_ap || !$matricula_pq || !$matricula_dp) {
        $error_message = 'Todos los campos son necesarios.';
    } else {
        // Procesamiento de datos y búsqueda de compradores
        $resultadosCompradores = obtenerDatosInmuebleYCompradores($conn, $matricula_ap, $matricula_pq, $matricula_dp);
        
        // Verificar si se encontraron resultados
        if ($resultadosCompradores) {
            $data = $resultadosCompradores;
        } else {
            $error_message = 'No se encontraron resultados para las matrículas proporcionadas.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar'])) {
    $matricula_ap = $_POST['matr_ap'] ?? '';
    $matricula_pq = $_POST['matr_pq'] ?? '';
    $matricula_dp = $_POST['matr_dp'] ?? '';
    $datos_inmuebles = $_POST['datos_inmuebles'] ?? [];
    $datos_compradores = $_POST['datos_compradores'] ?? [];
    // Procesamiento de los compradores
    if ($datos_inmuebles && count($datos_inmuebles) > 0) {
        foreach ($datos_inmuebles as $compradores) {
            for ($compradorIndex = 1; $compradorIndex <= 4; $compradorIndex++) {
                // Asegurarse de que los compradores existen
                $nombre = $compradores["nombre_comp$compradorIndex"] ?? null;
                $cc = $compradores["cc_comp$compradorIndex"] ?? null;
                $expcc = $compradores["expcc_comp$compradorIndex"] ?? null;
                $dom = $compradores["dom_comp$compradorIndex"] ?? null;
                $escivil = $compradores["escivil_comp$compradorIndex"] ?? null;
                
                // Validar cada comprador
                if (!$nombre || !$cc || !$dom || !$escivil) {
                    $error_message = "Faltan datos del comprador $compradorIndex.";
                    break;
                }
            }
        }
    }

    // Validaciones de matrículas
    if (!$matricula_ap || !$matricula_pq || !$matricula_dp) {
        $error_message = 'Todos los campos de matrícula son necesarios.';
    } elseif (empty($datos_inmuebles)) {
        $error_message = 'No se encontraron datos para actualizar. Verifique los campos de datos.';
    } else {
        // Llamar a la función para actualizar la base de datos
        $resultado_actualizacion = actualizarBaseDatos(
            $conn,
            $datos_inmuebles,
            $datos_compradores,
            $matricula_ap
        );

        // Verificar si la actualización fue exitosa
        if ($resultado_actualizacion) {
            $success_message = 'Los datos se actualizaron correctamente.';
        } else {
            $error_message = 'Error al actualizar los datos.';
        }
    }
} elseif (isset($_POST['new_query'])) {
    // Si se hace una nueva consulta, reiniciar las variables
    $data = [];
    $matricula_ap = $matricula_pq = $matricula_dp = ''; // Restablecer las variables
} elseif (isset($_POST['reset_form'])) {
    // Restablecer el formulario (limpiar los campos)
    $matricula_ap = $matricula_pq = $matricula_dp = '';
    $data = [];
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inmuebles</title>
    <link rel="stylesheet" href="AllStyles.css">
</head>

<body>
    <div class="container">
        <!-- Columna Izquierda -->
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
        <!-- Columna Derecha -->
        <div class="right-column">
            <div class="middle-column">
                <form method="POST" action="5A-procesar_matricula.php">
                    <?php if ($error_message): ?>
                        <p style="color: red;"><?php echo $error_message; ?></p>
                    <?php endif; ?>
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
                    <?php if (empty($data)): ?>
                        <table>
                            <tr>
                                <td><label for="matr_ap">Matrícula Apartamento:</label></td>
                                <td><input type="text" name="matr_ap" value="<?php echo htmlspecialchars($matricula_ap ?? ''); ?>" required></td>
                            </tr>
                            <tr>
                                <td><label for="matr_pq">Matrícula Parqueadero:</label></td>
                                <td><input type="text" name="matr_pq" value="<?php echo htmlspecialchars($matricula_pq ?? ''); ?>" required></td>
                            </tr>
                            <tr>
                                <td><label for="matr_dp">Matrícula Depósito:</label></td>
                                <td><input type="text" name="matr_dp" value="<?php echo htmlspecialchars($matricula_dp ?? ''); ?>" required></td>
                            </tr>
                            <td colspan="2"><button type="submit" name="consultar">Consultar</button></td>
                        </table>
                    <?php endif; ?>
                    <?php if (isset($data) && count($data) > 0): ?>
                        <form method="POST" action="5A-procesar_matricula.php">
                            <button type="submit" name="new_query" value="1">Realizar nueva consulta</button>
                        </form>
                        <table border="1">
                            <tr>
                                <th style="text-align: center;">Matrícula</th>
                                <th style="text-align: center;">Tipo</th>
                                <th style="text-align: center;">Inmueble</th>
                                <th style="text-align: center;">Torre</th>
                                <th style="text-align: center;">Valor</th>
                            </tr>
                            <?php
                            $total_valor = 0;
                            foreach ($data as $index => $row):
                                $total_valor += $row['vlr_inm'];
                            ?>
                                <tr>
                                    <td><input type="text" name="matr_inm[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($row['matr_inm'] ?? ''); ?>" size="<?php echo strlen($row['matr_inm'] ?? '') + 0; ?>"></td>
                                    <td><input type="text" name="tipo_inm[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($row['tipo_inm'] ?? ''); ?>" size="<?php echo strlen($row['tipo_inm'] ?? '') + 2; ?>"></td>
                                    <td style="text-align: center;"><input type="text" name="num_inm[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($row['num_inm'] ?? ''); ?>" size="<?php echo strlen($row['num_inm'] ?? '') + 1; ?>" style="text-align: center;"></td>
                                    <td style="text-align: center;"><input type="text" name="torre_inm[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($row['torre_inm'] ?? ''); ?>" size="<?php echo max(strlen($row['torre_inm'] ?? ''), 5) + 1; ?>" style="text-align: center;"></td>
                                    <td style="text-align: center;"><input type="text" name="vlr_inm[<?php echo $index; ?>]" value="<?php echo '$' . number_format($row['vlr_inm'] ?? 0, 0, ',', '.'); ?>" size="15" style="text-align: center;"></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="4" style="text-align: right; font-weight: bold;">Valor Venta:</td>
                                <td style="text-align: center;font-weight: bold;"><?php echo '$' . number_format($total_valor, 0, ',', '.'); ?></td>
                            </tr>
                        </table>
                        <div class="compradores-container">
                            <table border="1">
                                <?php
                                // Dividimos la cantidad total de compradores en pares
                                $compradores = [
                                    $data[0]["nombre_comp1"],
                                    $data[0]["nombre_comp2"],
                                    $data[0]["nombre_comp3"],
                                    $data[0]["nombre_comp4"]
                                ];
                                // Recorremos los compradores de dos en dos
                                for ($i = 0; $i < 4; $i += 2):
                                    // Comprobamos si al menos uno de los dos compradores tiene datos
                                    if (!empty($compradores[$i]) || !empty($compradores[$i + 1])): ?>
                                        <tr>
                                            <!-- Comprador 1 -->
                                            <?php if (!empty($compradores[$i])): ?>
                                                <td style="text-align: center;">
                                                    <h4>Comprador <?php echo $i + 1; ?></h4>
                                                    <table border="1">
                                                        <tr>
                                                            <td><label for="nombre_comp<?php echo $i + 1; ?>">Nombre:</label></td>
                                                            <td><input type="text" name="nombre_comp<?php echo $i + 1; ?>" value="<?php echo htmlspecialchars($compradores[$i]); ?>" size="49" required></td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="cc_comp<?php echo $i + 1; ?>">C.C.:</label></td>
                                                            <td><input type="text" name="cc_comp<?php echo $i + 1; ?>" value="<?php echo htmlspecialchars(number_format($data[0]["cc_comp" . ($i + 1)] ?? '', 0, ',', '.')); ?>" required></td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="expcc_comp<?php echo $i + 1; ?>">Expedición C.C.:</label></td>
                                                            <td>
                                                                <select name="expcc_comp<?php echo $i + 1; ?>" required>
                                                                    <option value="" disabled selected>Seleccionar</option>
                                                                    <?php
                                                                    $result_municipios->data_seek(0);
                                                                    while ($municipio = $result_municipios->fetch_assoc()) { ?>
                                                                        <option value="<?php echo htmlspecialchars($municipio['nombre_mun']); ?>"
                                                                            <?php echo (isset($row["expcc_comp" . ($i + 1)]) && $row["expcc_comp" . ($i + 1)] == $municipio['nombre_mun']) ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($municipio['nombre_mun']); ?>
                                                                        </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="escivil_comp<?php echo $i + 1; ?>">Estado Civil:</label></td>
                                                            <td>
                                                                <select name="escivil_comp<?php echo $i + 1; ?>" required>
                                                                    <option value="" disabled selected>Seleccionar</option>
                                                                    <?php
                                                                    $result_estado_civil->data_seek(0);
                                                                    while ($estado_civil = $result_estado_civil->fetch_assoc()) { ?>
                                                                        <option value="<?php echo htmlspecialchars($estado_civil['nombre_escivil']); ?>"
                                                                            <?php echo (isset($row["escivil_comp" . ($i + 1)]) && $row["escivil_comp" . ($i + 1)] == $estado_civil['nombre_escivil']) ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($estado_civil['nombre_escivil']); ?>
                                                                        </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="domicilio_comp<?php echo $i + 1; ?>">Domicilio:</label></td>
                                                            <td>
                                                                <select name="domicilio_comp<?php echo $i + 1; ?>" required>
                                                                    <option value="" disabled selected>Seleccionar</option>
                                                                    <?php
                                                                    $result_municipios->data_seek(0);
                                                                    while ($municipio = $result_municipios->fetch_assoc()) { ?>
                                                                        <option value="<?php echo htmlspecialchars($municipio['nombre_mun']); ?>"
                                                                            <?php echo (isset($row["domicilio_comp" . ($i + 1)]) && $row["domicilio_comp" . ($i + 1)] == $municipio['nombre_mun']) ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($municipio['nombre_mun']); ?>
                                                                        </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            <?php endif; ?>
                                            <!-- Comprador 2 -->
                                            <?php if (!empty($compradores[$i + 1])): ?>
                                                <td style="text-align: center;">
                                                    <h4>Comprador <?php echo $i + 2; ?></h4>
                                                    <table border="1">
                                                        <tr>
                                                            <td><label for="nombre_comp<?php echo $i + 2; ?>">Nombre:</label></td>
                                                            <td><input type="text" name="nombre_comp<?php echo $i + 2; ?>" value="<?php echo htmlspecialchars($compradores[$i + 1]); ?>" size="49" required></td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="cc_comp<?php echo $i + 2; ?>">C.C.:</label></td>
                                                            <td><input type="text" name="cc_comp<?php echo $i + 2; ?>" value="<?php echo htmlspecialchars(number_format($data[0]["cc_comp" . ($i + 2)] ?? '', 0, ',', '.')); ?>" required></td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="expcc_comp<?php echo $i + 2; ?>">Expedición C.C.:</label></td>
                                                            <td>
                                                                <select name="expcc_comp<?php echo $i + 2; ?>" required>
                                                                    <option value="" disabled selected>Seleccionar</option>
                                                                    <?php
                                                                    $result_municipios->data_seek(0);
                                                                    while ($municipio = $result_municipios->fetch_assoc()) { ?>
                                                                        <option value="<?php echo htmlspecialchars($municipio['nombre_mun']); ?>"
                                                                            <?php echo (isset($row["expcc_comp" . ($i + 2)]) && $row["expcc_comp" . ($i + 2)] == $municipio['nombre_mun']) ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($municipio['nombre_mun']); ?>
                                                                        </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="escivil_comp<?php echo $i + 2; ?>">Estado Civil:</label></td>
                                                            <td>
                                                                <select name="escivil_comp<?php echo $i + 2; ?>" required>
                                                                    <option value="" disabled selected>Seleccionar</option>
                                                                    <?php
                                                                    $result_estado_civil->data_seek(0);
                                                                    while ($estado_civil = $result_estado_civil->fetch_assoc()) { ?>
                                                                        <option value="<?php echo htmlspecialchars($estado_civil['nombre_escivil']); ?>"
                                                                            <?php echo (isset($row["escivil_comp" . ($i + 2)]) && $row["escivil_comp" . ($i + 2)] == $estado_civil['nombre_escivil']) ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($estado_civil['nombre_escivil']); ?>
                                                                        </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><label for="domicilio_comp<?php echo $i + 2; ?>">Domicilio:</label></td>
                                                            <td>
                                                                <select name="domicilio_comp<?php echo $i + 2; ?>" required>
                                                                    <option value="" disabled selected>Seleccionar</option>
                                                                    <?php
                                                                    $result_municipios->data_seek(0);
                                                                    while ($municipio = $result_municipios->fetch_assoc()) { ?>
                                                                        <option value="<?php echo htmlspecialchars($municipio['nombre_mun']); ?>"
                                                                            <?php echo (isset($row["domicilio_comp" . ($i + 2)]) && $row["domicilio_comp" . ($i + 2)] == $municipio['nombre_mun']) ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($municipio['nombre_mun']); ?>
                                                                        </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </table>
                        </div>
                        <!-- Botón de actualizar -->
                        <form method="POST" action="5A-procesar_matricula.php" style="text-align: center; margin-top: 20px;">
                            <button type="submit" name="confirmar" value="1" class="boton">CONFIRMAR</button>
                        </form>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</body>

</html>