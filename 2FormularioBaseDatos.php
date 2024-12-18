<?php 
// Conexión a la base de datos
include("ConexionBaseDatos.php");


// Iniciar sesión para manejar tokens y evitar duplicados al refrescar
session_start();

// Aumentar los límites de tiempo y memoria para manejar grandes volúmenes
ini_set('max_execution_time', '600'); // Tiempo en segundos (ajustable)
ini_set('memory_limit', '1024M'); // Memoria (ajustable)

// Variables para mostrar los formularios
$mostrarFideicomisos = false;
$mostrarFideicomitentes = false;
$mostrarFiduciarias = false;
$mostrarProyectos = false;

// Verificar qué formulario mostrar
if (isset($_GET['section'])) {
    switch ($_GET['section']) {
        case 'fideicomitentes':
            $mostrarFideicomitentes = true;
            break;
        case 'fiduciarias':
            $mostrarFiduciarias = true;
            break;
        case 'fideicomisos':
            $mostrarFideicomisos = true;
            break;
        case 'proyectos':
            $mostrarProyectos = true;
            break;
    }
}

// Manejo de los formularios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Verificar token CSRF
    if (isset($_POST['token']) && $_POST['token'] !== $_SESSION['token']) {
        die("Token inválido.");
    }

    // Insertar fideicomisos
    if (isset($_POST['fideicomisos'])) {
        $nit_fdcomiso = $conn->real_escape_string($_POST["nit_fdcomiso"]);
        $nombre_fdcomiso = $conn->real_escape_string($_POST["nombre_fdcomiso"]);
        $sql = "INSERT INTO fideicomisos (nit_fdcomiso, nombre_fdcomiso)
                VALUES ('$nit_fdcomiso', '$nombre_fdcomiso')";
        if ($conn->query($sql) !== TRUE) {
            echo "Error: " . $conn->error;
        } else {
            echo "<p>Fideicomiso registrado correctamente.</p>";
        }
    }

    // Insertar fideicomitentes
    if (isset($_POST['fideicomitentes'])) {
        $nit_fdcomitente = $conn->real_escape_string($_POST["nit_fdcomitente"]);
        $nombre_fdcomitente = $conn->real_escape_string($_POST["nombre_fdcomitente"]);
        $sql = "INSERT INTO fideicomitentes (nit_fdcomitente, nombre_fdcomitente)
                VALUES ('$nit_fdcomitente', '$nombre_fdcomitente')";
        if ($conn->query($sql) !== TRUE) {
            echo "Error: " . $conn->error;
        } else {
            echo "<p>Fideicomitente registrado correctamente.</p>";
        }
    }

    // Insertar fiduciarias
    if (isset($_POST['fiduciarias'])) {
        $nit_fdciaria = $conn->real_escape_string($_POST["nit_fdciaria"]);
        $nombre_fdciaria = $conn->real_escape_string($_POST["nombre_fdciaria"]);
        $sql = "INSERT INTO fiduciarias (nit_fdciaria, nombre_fdciaria)
                VALUES ('$nit_fdciaria', '$nombre_fdciaria')";
        if ($conn->query($sql) !== TRUE) {
            echo "Error: " . $conn->error;
        } else {
            echo "<p>Fiduciaria registrada correctamente.</p>";
        }
    }

    // Insertar proyectos
    if (isset($_POST['proyectos'])) {
        $nit_proy = $conn->real_escape_string($_POST["nit_proy"]);
        $nombre_proy = $conn->real_escape_string($_POST["nombre_proy"]);
        $sql = "INSERT INTO proyectos (nit_proy, nombre_proy)
            VALUES ('$nit_proy', '$nombre_proy')";
        if ($conn->query($sql) !== TRUE) {
            echo "Error: " . $conn->error;
        } else {
            echo "<p>Proyecto registrado correctamente.</p>";
        }
    }
}

// Generar un nuevo token único para el formulario y guardarlo en sesión
$_SESSION['token'] = bin2hex(random_bytes(32));
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

        <div class="right-column">
            <div class="middle-column">
                <h1>BASE DE DATOS</h1>

                <div class="table-buttons">
                    <a href="?section=fideicomisos" class="boton <?php if ($mostrarFideicomisos) echo 'active'; ?>">Fideicomisos</a>
                    <a href="?section=fideicomitentes" class="boton <?php if ($mostrarFideicomitentes) echo 'active'; ?>">Fideicomitentes</a>
                    <a href="?section=fiduciarias" class="boton <?php if ($mostrarFiduciarias) echo 'active'; ?>">Fiduciarias</a>
                    <a href="?section=proyectos" class="boton <?php if ($mostrarProyectos) echo 'active'; ?>">Proyectos</a>
                    <a href="6Matriculas.php" class="boton <?php if ($mostrarMatriculas) echo 'active'; ?>">Inmuebles</a> <!-- Enlace al nuevo archivo -->
                    <a href="5Compradores.php" class="boton <?php if ($mostrarCompradores) echo 'active'; ?>">Compradores</a>
                </div>

                <!-- Formularios de fideicomisos -->
                <?php if ($mostrarFideicomisos): ?>
                    <br>
                    <br>
                    <form method="POST" action="">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                        <label for="nit_fdcomiso">NIT Fideicomiso:</label>
                        <input type="text" id="nit_fdcomiso" name="nit_fdcomiso" required>
                        <label for="nombre_fdcomiso">Nombre Fideicomiso:</label>
                        <input type="text" id="nombre_fdcomiso" name="nombre_fdcomiso" required>
                        <button type="submit" name="fideicomisos">Registrar Fideicomiso</button>
                    </form>
                <?php endif; ?>


                <!-- Formularios de fideicomitentes -->
                <?php if ($mostrarFideicomitentes): ?>
                    <br>
                    <br>
                    <form method="POST" action="">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                        <label for="nit_fdcomitente">NIT Fideicomitente:</label>
                        <input type="text" id="nit_fdcomitente" name="nit_fdcomitente" required>
                        <label for="nombre_fdcomitente">Nombre Fideicomitente:</label>
                        <input type="text" id="nombre_fdcomitente" name="nombre_fdcomitente" required>
                        <button type="submit" name="fideicomitentes">Registrar Fideicomitente</button>
                    </form>
                <?php endif; ?>

                <!-- Formularios de fiduciarias -->
                <?php if ($mostrarFiduciarias): ?>
                    <br>
                    <br>
                    <form method="POST" action="">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                        <label for="nit_fdciaria">NIT Fiduciaria:</label>
                        <input type="text" id="nit_fdciaria" name="nit_fdciaria" required>
                        <label for="nombre_fdciaria">Nombre Fiduciaria:</label>
                        <input type="text" id="nombre_fdciaria" name="nombre_fdciaria" required>
                        <button type="submit" name="fiduciarias">Registrar Fiduciaria</button>
                    </form>
                <?php endif; ?>

                <!-- Formularios de proyectos -->
                <?php if ($mostrarProyectos): ?>
                    <br>
                    <br>
                    <form method="POST" action="">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                        <label for="nit_proy">NIT del Proyecto:</label>
                        <input type="text" id="nit_proy" name="nit_proy" required>
                        <label for="nombre_proy">Nombre del Proyecto:</label>
                        <input type="text" id="nombre_proy" name="nombre_proy" required>
                        <button type="submit" name="proyectos">Procesar Proyecto</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>