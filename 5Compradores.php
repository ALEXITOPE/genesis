<?php
// Conexión a la base de datos
include("ConexionBaseDatos.php");

$section = isset($_GET['section']) ? $_GET['section'] : '';

// Variables de control para mostrar formularios
$mostrarComprador1 = false;
$mostrarComprador2 = false;
$mostrarComprador3 = false;
$mostrarComprador4 = false;

// Determinar qué formulario mostrar
switch ($section) {
    case 'Comprador1':
        $mostrarComprador1 = true;
        break;
    case 'Comprador2':
        $mostrarComprador2 = true;
        break;
    case 'Comprador3':
        $mostrarComprador3 = true;
        break;
    case 'Comprador4':
        $mostrarComprador4 = true;
        break;
}

// Procesar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determinar qué formulario fue enviado y procesarlo
    if (isset($_POST['submit_comprador1'])) {
        procesarFormulario($_POST['data_comprador'], 'COMPRADOR_1');
    } elseif (isset($_POST['submit_comprador2'])) {
        procesarFormulario($_POST['data_comprador'], 'COMPRADOR_2');
    } elseif (isset($_POST['submit_comprador3'])) {
        procesarFormulario($_POST['data_comprador'], 'COMPRADOR_3');
    } elseif (isset($_POST['submit_comprador4'])) {
        procesarFormulario($_POST['data_comprador'], 'COMPRADOR_4');
    }
}

// Función para procesar los datos e insertarlos en la base de datos
function procesarFormulario($data, $tabla)
{
    global $conn;

    // Dividir las líneas del textarea
    $filas = explode("\n", trim($data));
    foreach ($filas as $fila) {
        // Dividir columnas por tabulación o espacios múltiples
        $columnas = preg_split("/\t+|\s{2,}/", trim($fila));

        // Obtener datos con valores predeterminados si están vacíos
        $nombre = !empty($columnas[0]) ? $columnas[0] : null;
        $cc = !empty($columnas[1]) ? $columnas[1] : null;
        $expcc = !empty($columnas[2]) ? $columnas[2] : null;
        $dom = !empty($columnas[3]) ? $columnas[3] : null;
        $escivil = !empty($columnas[4]) ? $columnas[4] : null;

        // Ajustar nombres de columnas según la tabla
        $sql = "";
        switch ($tabla) {
            case 'COMPRADOR_1':
                $sql = "INSERT INTO $tabla (nombre_comp1, cc_comp1, expcc_comp1, dom_comp1, escivil_comp1) VALUES (?, ?, ?, ?, ?)";
                break;
            case 'COMPRADOR_2':
                $sql = "INSERT INTO $tabla (nombre_comp2, cc_comp2, expcc_comp2, dom_comp2, escivil_comp2) VALUES (?, ?, ?, ?, ?)";
                break;
            case 'COMPRADOR_3':
                $sql = "INSERT INTO $tabla (nombre_comp3, cc_comp3, expcc_comp3, dom_comp3, escivil_comp3) VALUES (?, ?, ?, ?, ?)";
                break;
            case 'COMPRADOR_4':
                $sql = "INSERT INTO $tabla (nombre_comp4, cc_comp4, expcc_comp4, dom_comp4, escivil_comp4) VALUES (?, ?, ?, ?, ?)";
                break;
        }

        // Ejecutar la consulta preparada
        if ($sql) {
            // Usar los parámetros de forma condicional para permitir nulos en los campos vacíos
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $nombre, $cc, $expcc, $dom, $escivil);
            $stmt->execute();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BASE DE DATOS</title>
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
                <a href="2FormularioBaseDatos.php" class="boton active">Base de Datos</a>
                <a href="3FormularioPlantilla.php" class="boton">Crear nueva plantilla</a>
                <a href="10Generarescritura.php" class="boton">Generar nueva escritura</a>
            </div>
        </div>

        <!-- Columna derecha -->
        <div class="right-column">
            <div class="middle-column">
                <h1>Gestión de COMPRADORES</h1>

                <!-- Botones para mostrar formularios -->
                <div class="table-buttons">
                    <a href="?section=Comprador1" class="boton <?php echo $section === 'Comprador1' ? 'active' : ''; ?>">Comprador 1</a>
                    <a href="?section=Comprador2" class="boton <?php echo $section === 'Comprador2' ? 'active' : ''; ?>">Comprador 2</a>
                    <a href="?section=Comprador3" class="boton <?php echo $section === 'Comprador3' ? 'active' : ''; ?>">Comprador 3</a>
                    <a href="?section=Comprador4" class="boton <?php echo $section === 'Comprador4' ? 'active' : ''; ?>">Comprador 4</a>
                </div>

                <!-- Formulario Comprador 1 -->
                <?php if ($mostrarComprador1): ?>
                    <form method="POST">
                        <textarea name="data_comprador" rows="10" placeholder="Ingrese aquí la información a procesar"></textarea>
                        <button type="submit" name="submit_comprador1">Registrar Comprador 1</button>
                        <a href="2FormularioBaseDatos.php" class="boton">Regresar</a>
                    </form>
                <?php endif; ?>

                <!-- Formulario Comprador 2 -->
                <?php if ($mostrarComprador2): ?>
                    <form method="POST">
                        <textarea name="data_comprador" rows="10" placeholder="Pega aquí los datos"></textarea>
                        <button type="submit" name="submit_comprador2">Registrar Comprador 2</button>
                        <a href="2FormularioBaseDatos.php" class="boton">Regresar</a>
                    </form>
                <?php endif; ?>

                <!-- Formulario Comprador 3 -->
                <?php if ($mostrarComprador3): ?>
                    <form method="POST">
                        <textarea name="data_comprador" rows="10" placeholder="Pega aquí los datos"></textarea>
                        <button type="submit" name="submit_comprador3">Registrar Comprador 3</button>
                        <a href="2FormularioBaseDatos.php" class="boton">Regresar</a>
                    </form>
                <?php endif; ?>

                <!-- Formulario Comprador 4 -->
                <?php if ($mostrarComprador4): ?>
                    <form method="POST">
                        <textarea name="data_comprador" rows="10" placeholder="Pega aquí los datos"></textarea>
                        <button type="submit" name="submit_comprador4">Registrar Comprador 4</button>
                        <a href="2FormularioBaseDatos.php" class="boton">Regresar</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>