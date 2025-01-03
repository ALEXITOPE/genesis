<?php
// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir archivo de conexión a la base de datos y utilidades
require_once 'ConexionBaseDatos.php';
require_once 'Convertidores.php'; // Función `obtenerDatosInmuebleYCompradores` está aquí

// Inicializar variables
$error_message = '';
$matricula_ap = $matricula_pq = $matricula_dp = '';
$inmuebles = [];
$compradores = [];

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar conexión a la base de datos
    if (!isset($conn) || !$conn) {
        $error_message = 'Error en la conexión a la base de datos.';
    } else {
        // Verificar si es búsqueda o actualización
        if (isset($_POST['buscar'])) {
            // Obtener datos del formulario
            $matricula_ap = $_POST['matr_ap'] ?? null;
            $matricula_pq = $_POST['matr_pq'] ?? null;
            $matricula_dp = $_POST['matr_dp'] ?? null;

            // Validar campos requeridos
            if (!$matricula_ap || !$matricula_pq || !$matricula_dp) {
                $error_message = 'Todos los campos son obligatorios.';
            } else {
                // Sanitizar entradas
                $matricula_ap = $conn->real_escape_string($matricula_ap);
                $matricula_pq = $conn->real_escape_string($matricula_pq);
                $matricula_dp = $conn->real_escape_string($matricula_dp);

                // Obtener datos de los inmuebles y compradores desde Convertidores.php
                $datos = obtenerDatosInmuebleYCompradores($conn, $matricula_ap, $matricula_pq, $matricula_dp);

                if (!empty($datos)) {
                    $inmuebles = $datos['inmuebles'] ?? [];
                    $compradores = $datos['compradores'] ?? [];
                } else {
                    $error_message = 'No se encontraron datos para las matrículas proporcionadas.';
                }
            }
        } elseif (isset($_POST['guardar'])) {
            // Guardar datos editados en la base de datos
            foreach ($_POST['inmuebles'] as $index => $inmueble) {
                $id_inmueble = $conn->real_escape_string($inmueble['id']);
                $tipo = $conn->real_escape_string($inmueble['tipo']);
                $numero = $conn->real_escape_string($inmueble['numero']);
                $torre = $conn->real_escape_string($inmueble['torre']);
                $valor = $conn->real_escape_string($inmueble['valor']);
                $matricula = $conn->real_escape_string($inmueble['matricula']);
                $sql = "UPDATE inmuebles SET tipo_inm='$tipo', num_inm='$numero', torre_inm='$torre', vlr_inm='$valor', matr_inm='$matricula' WHERE id_inm='$id_inmueble'";

                // Actualizar inmueble
                $conn->query($sql);
            }

            foreach ($_POST['compradores'] as $index => $comprador) {
                $id_comprador = $conn->real_escape_string($comprador['id']);
                $nombre = $conn->real_escape_string($comprador['nombre']);
                $cedula = $conn->real_escape_string($comprador['cedula']);
                $expedicion = isset($comprador['expedicion1']) ? $conn->real_escape_string($comprador['expedicion1']) : ''; // Verificar si existe la expedición
                $domicilio = $conn->real_escape_string($comprador['domicilio']);
                $estado_civil = $conn->real_escape_string($comprador['estado_civil']);

                // Actualizar comprador
                $sql = "UPDATE compradores SET nombre_comp='$nombre', cc_comp='$cedula', expcc_comp='$expedicion', dom_comp='$domicilio', escivil_comp='$estado_civil' WHERE id_comp='$id_comprador'";

                $conn->query($sql);
            }

            $error_message = 'Datos actualizados correctamente.';
        }
    }
}

// Obtener los datos para las listas desplegables
$sql_municipios = "SELECT nombre_mun FROM municipios";
$result_municipios = $conn->query($sql_municipios);

$sql_estado_civil = "SELECT nombre_escivil FROM estados_civiles";
$result_estado_civil = $conn->query($sql_estado_civil);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Matrículas</title>
</head>

<body>
    <h1>Formulario de Matrículas</h1>

    <!-- Mostrar mensaje de error o éxito -->
    <?php if ($error_message): ?>
        <p style="color: red;"><?= htmlspecialchars($error_message ?? '') ?></p>
    <?php endif; ?>

    <!-- Formulario para buscar matrículas -->
    <form action="" method="POST">
        <label for="matr_ap">Matrícula APTO:</label>
        <input type="text" name="matr_ap" id="matr_ap" value="<?= htmlspecialchars($matricula_ap ?? '') ?>" required>
        <br>

        <label for="matr_pq">Matrícula PQ:</label>
        <input type="text" name="matr_pq" id="matr_pq" value="<?= htmlspecialchars($matricula_pq ?? '') ?>" required>
        <br>

        <label for="matr_dp">Matrícula DP:</label>
        <input type="text" name="matr_dp" id="matr_dp" value="<?= htmlspecialchars($matricula_dp ?? '') ?>" required>
        <br>

        <input type="submit" name="buscar" value="Buscar">
    </form>

    <!-- Formulario dinámico para editar inmuebles -->
    <?php if (!empty($inmuebles)): ?>
        <h2>Editar Inmuebles</h2>
        <form action="" method="POST">
            <?php foreach ($inmuebles as $index => $inmueble): ?>
                <fieldset>
                    <legend>Inmueble <?= $index + 1 ?></legend>
                    <input type="hidden" name="inmuebles[<?= $index ?>][id]" value="<?= htmlspecialchars($inmueble['id_inmueble'] ?? '') ?>">
                    <label>Tipo:</label>
                    <input type="text" name="inmuebles[<?= $index ?>][tipo]" value="<?= htmlspecialchars($inmueble['tipo_inm'] ?? '') ?>">
                    <br>

                    <label>Número:</label>
                    <input type="text" name="inmuebles[<?= $index ?>][numero]" value="<?= htmlspecialchars($inmueble['num_inm'] ?? '') ?>">
                    <br>
                    
                    <label>Torre:</label>
                    <input type="text" name="inmuebles[<?= $index ?>][torre]" value="<?= htmlspecialchars($inmueble['torre_inm'] ?? '') ?>">
                    <br>
                    
                    <label>Valor:</label>
                    <input type="text" name="inmuebles[<?= $index ?>][valor]" value="<?= htmlspecialchars($inmueble['vlr_inm'] ?? '') ?>">
                    <br>
                    
                    <label>Matrícula:</label>
                    <input type="text" name="inmuebles[<?= $index ?>][matricula]" value="<?= htmlspecialchars($inmueble['matr_inm'] ?? '') ?>">
                </fieldset>
            <?php endforeach; ?>
            
            <!-- Formulario dinámico para editar compradores -->
            <h2>Editar Compradores</h2>
            <?php foreach ($compradores as $index => $comprador): ?>
                <fieldset>
                    <legend>Comprador <?= $index + 1 ?></legend>
                    <input type="hidden" name="compradores[<?= $index ?>][id]" value="<?= htmlspecialchars($comprador['id_comp'] ?? '') ?>">
                    <label>Nombre:</label>
                    <input type="text" name="compradores[<?= $index ?>][nombre]" value="<?= htmlspecialchars($comprador['nombre_comp'] ?? '') ?>">
                    <br>
                    <label>Cédula:</label>
                    <input type="text" name="compradores[<?= $index ?>][cedula]" value="<?= htmlspecialchars($comprador['cc_comp'] ?? '') ?>">
                    <br>
                    <label>Expedición Cédula:</label>
                    <select name="compradores[<?= $index ?>][expedicion1]">
                        <?php
                        // Realizar la consulta de municipios dentro del ciclo de cada comprador
                        $result_municipios = $conn->query($sql_municipios);

                        if ($result_municipios && $result_municipios->num_rows > 0) {
                            while ($municipio = $result_municipios->fetch_assoc()) {
                                // Verificar si el valor ya está seleccionado
                                $selected = ($municipio['nombre_mun'] == $comprador['expcc_comp']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($municipio['nombre_mun']) . "' $selected>" . htmlspecialchars($municipio['nombre_mun']) . "</option>";
                            }
                        } else {
                            echo "<option value=''>No se encontraron municipios</option>";
                        }
                        ?>
                    </select>
                    <br>

                    <label>Domicilio:</label>
                    <select name="compradores[<?= $index ?>][domicilio]">
                        <?php
                        // Realizar la consulta de municipios dentro del ciclo de cada comprador
                        $result_municipios = $conn->query($sql_municipios); // Aquí lo reutilizamos
                        if ($result_municipios && $result_municipios->num_rows > 0) {
                            while ($municipio = $result_municipios->fetch_assoc()) {
                                // Verificar si el valor ya está seleccionado
                                $selected = ($municipio['nombre_mun'] == $comprador['dom_comp']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($municipio['nombre_mun']) . "' $selected>" . htmlspecialchars($municipio['nombre_mun']) . "</option>";
                            }
                        } else {
                            echo "<option value=''>No se encontraron municipios</option>";
                        }
                        ?>
                    </select>
                    <br>
                    
                    <label>Estado Civil:</label>
                    <select name="compradores[<?= $index ?>][estado_civil]">
                        <?php
                        if ($result_estado_civil && $result_estado_civil->num_rows > 0) {
                            while ($estado = $result_estado_civil->fetch_assoc()) {
                                $selected = ($estado['nombre_escivil'] == $comprador['escivil_comp']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($estado['nombre_escivil']) . "' $selected>" . htmlspecialchars($estado['nombre_escivil']) . "</option>";
                            }
                        } else {
                            echo "<option value=''>No se encontraron estados civiles</option>";
                        }
                        ?>
                    </select>
                </fieldset>
            <?php endforeach; ?>
            <input type="submit" name="guardar" value="Guardar Cambios">
        </form>
    <?php endif; ?>
</body>

</html>
