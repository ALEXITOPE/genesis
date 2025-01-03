<?php
// Incluir los archivos necesarios
require 'vendor/autoload.php';
require_once 'ConexionBaseDatos.php'; // Conexión a la base de datos
require_once 'Convertidoresprueba.php'; // Función para obtener datos
session_start();

// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si la conexión está disponible
if ($conn->connect_error) {
    die('Error en la conexión a la base de datos: ' . $conn->connect_error);
}

// Verificar si el formulario ha sido enviado y obtener las matrículas
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener las matrículas desde el formulario
    $matricula_ap = $_POST['matricula_ap'];
    $matricula_pq = $_POST['matricula_pq'];
    $matricula_dp = $_POST['matricula_dp'];

    // Obtener los datos de los compradores
    $compradores = obtenerDatosInmuebleYCompradores($conn, $matricula_ap, $matricula_pq, $matricula_dp);

    // Inicializar las variables para los campos de los compradores
    $compradores_fields = [
        'nombre_comp' => [],
        'cc_comp' => [],
        'expcc_comp' => [],
        'dom_comp' => [],
        'escivil_comp' => [],
    ];

    // Llenar los campos con los datos de la base de datos, si existen
    foreach ($compradores as $comprador) {
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($comprador["nombre_comp$i"])) {
                $compradores_fields['nombre_comp'][$i] = $comprador["nombre_comp$i"];
                $compradores_fields['cc_comp'][$i] = $comprador["cc_comp$i"];
                $compradores_fields['expcc_comp'][$i] = $comprador["expcc_comp$i"];
                $compradores_fields['dom_comp'][$i] = $comprador["dom_comp$i"];
                $compradores_fields['escivil_comp'][$i] = $comprador["escivil_comp$i"];
            }
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
        <!-- Columna izquierda -->
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

        <!-- Columna derecha -->
        <div class="right-column">
            <div class="middle-column">
                <h1>GESTIÓN DE PLANTILLAS</h1>
                <form action="5Generarescritura.php" method="POST" class="styled-form">
                    <!-- Formulario -->
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

                    <div class="inmuebles">
                        <label for="matr_ap">MATR.AP:</label>
                        <input type="text" id="matr_ap" name="matr_ap" value="<?= htmlspecialchars($matricula_ap ?? '') ?>" required><br>
                        <label for="matr_pq">MATR.PQ:</label>
                        <input type="text" id="matr_pq" name="matr_pq" value="<?= htmlspecialchars($matricula_pq ?? '') ?>" required><br>
                        <label for="matr_dp">MATR.DP:</label>
                        <input type="text" id="matr_dp" name="matr_dp" value="<?= htmlspecialchars($matricula_dp ?? '') ?>" required>
                    </div>

                    <!-- Campos de compradores (Dinamicos según cantidad) -->
                    <h3>Detalles del Comprador</h3>
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <?php if (isset($compradores_fields['nombre_comp'][$i])): ?>
                            <div class="styled-formuestra">
                                <label for="nombre_comp<?= $i ?>">Nombre:</label>
                                <input type="text" id="nombre_comp<?= $i ?>" name="nombre_comp<?= $i ?>" value="<?= htmlspecialchars($compradores_fields['nombre_comp'][$i]) ?>" required>
                                <label for="cc_comp<?= $i ?>">Cédula:</label>
                                <input type="text" id="cc_comp<?= $i ?>" name="cc_comp<?= $i ?>" value="<?= htmlspecialchars($compradores_fields['cc_comp'][$i]) ?>" required>
                                <label for="expcc_comp<?= $i ?>">Expedición Cédula:</label>
                                <input type="text" id="expcc_comp<?= $i ?>" name="expcc_comp<?= $i ?>" value="<?= htmlspecialchars($compradores_fields['expcc_comp'][$i]) ?>" required>
                                <label for="dom_comp<?= $i ?>">Domicilio:</label>
                                <input type="text" id="dom_comp<?= $i ?>" name="dom_comp<?= $i ?>" value="<?= htmlspecialchars($compradores_fields['dom_comp'][$i]) ?>" required>
                                <label for="escivil_comp<?= $i ?>">Estado Civil:</label>
                                <input type="text" id="escivil_comp<?= $i ?>" name="escivil_comp<?= $i ?>" value="<?= htmlspecialchars($compradores_fields['escivil_comp'][$i]) ?>" required>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <button type="submit" name="ejecutar">GENERAR DOCUMENTO</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
