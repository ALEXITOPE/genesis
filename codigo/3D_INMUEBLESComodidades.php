<?php
// Incluir la conexión a la base de datos
include("ConexionBaseDatos.php");

// Iniciar sesión para manejar tokens
session_start();

// Generar un token si no existe uno en la sesión
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Variables para manejar mensajes y resultados
$error = null;
$registros_no_ingresados = [];
$apartamentos_no_procesados = [];
$apartamentos_duplicados = [];

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Validar el token
    if (isset($_POST['token']) && $_POST['token'] !== $_SESSION['token']) {
        $error = "El token de seguridad no es válido. Intenta enviar el formulario nuevamente.";
    } else {
        // Procesar los datos
        if (isset($_POST['informacion_completa'])) {
            $informacion_completa = trim($_POST['informacion_completa']);

            // Expresión regular para capturar números y texto asociado
            preg_match_all(
                '/(\d+(?:,\s*\d+)*)\s*,\s*(.*)/',
                $informacion_completa,
                $matches,
                PREG_SET_ORDER
            );

            // Obtener todos los apartamentos de una sola vez
            $apartamentos = [];
            foreach ($matches as $match) {
                $numeros = explode(',', $match[1]); // Separar los números
                $texto = trim($match[2]); // Capturar el texto asociado

                // Agregar los números y texto al arreglo
                foreach ($numeros as $numero) {
                    $numero = trim($numero);
                    if (!isset($apartamentos[$numero])) {
                        $apartamentos[$numero] = [
                            'texto' => $texto,
                            'veces' => 1
                        ];
                    } else {
                        $apartamentos[$numero]['veces']++; // Contar duplicados
                    }
                }
            }

            // Realizar una consulta para obtener todos los apartamentos existentes
            $numeros_apartamentos = array_keys($apartamentos);
            $numeros_apartamentos_str = implode("','", $numeros_apartamentos);

            $stmt_select = $conn->prepare("SELECT id_inm, num_inm FROM inmuebles WHERE tipo_inm = 'APARTAMENTO NUMERO'");
            $stmt_select->execute();
            $result = $stmt_select->get_result();

            // Crear un array de apartamentos encontrados en la base de datos
            $apartamentos_en_base = [];
            while ($row = $result->fetch_assoc()) {
                $apartamentos_en_base[$row['num_inm']] = $row['id_inm'];
            }

            // Comparar los apartamentos procesados con los de la base de datos
            foreach ($apartamentos_en_base as $numero => $id_inm) {
                if (!isset($apartamentos[$numero])) {
                    $apartamentos_no_procesados[] = $numero; // No está en la información procesada
                }
            }

            // Realizar la actualización solo de los apartamentos que fueron encontrados
            $stmt_update = $conn->prepare("UPDATE inmuebles SET comod_inm = ? WHERE num_inm = ? AND tipo_inm = 'APARTAMENTO NUMERO'");
            
            foreach ($apartamentos as $numero => $data) {
                if (isset($apartamentos_en_base[$numero])) {
                    // Actualizar solo si el apartamento existe en la base de datos
                    $stmt_update->bind_param("ss", $data['texto'], $numero);
                    $stmt_update->execute();
                    
                    if ($data['veces'] > 1) {
                        $apartamentos_duplicados[] = $numero; // Marcar como duplicado
                    }

                    // Verificar si se actualizó correctamente
                    if ($stmt_update->affected_rows === 0) {
                        $registros_no_ingresados[] = [
                            'numero' => $numero,
                            'razon' => 'No se pudo actualizar (ya actualizado o error).'
                        ];
                    }
                } else {
                    $registros_no_ingresados[] = [
                        'numero' => $numero,
                        'razon' => 'No se encontró en la base de datos.'
                    ];
                }
            }

            // Limpiar los campos del formulario después de procesar
            $_POST = [];
        }
    }
}

// Mostrar mensajes o errores
if ($error) {
    echo "<p style='color: red;'>$error</p>";
}

if (!empty($registros_no_ingresados)) {
    echo "<h3>Registros no ingresados a la base de datos:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Número de Apartamento</th><th>Razón</th></tr>";
    foreach ($registros_no_ingresados as $registro) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($registro['numero']) . "</td>";
        echo "<td>" . htmlspecialchars($registro['razon']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

if (!empty($apartamentos_no_procesados)) {
    echo "<h3>Apartamentos no procesados (están en la base de datos pero no en la información):</h3>";
    echo "<ul>";
    foreach ($apartamentos_no_procesados as $numero) {
        echo "<li>" . htmlspecialchars($numero) . "</li>";
    }
    echo "</ul>";
}

if (!empty($apartamentos_duplicados)) {
    echo "<h3>Apartamentos duplicados en la información procesada:</h3>";
    echo "<ul>";
    foreach ($apartamentos_duplicados as $numero) {
        echo "<li>" . htmlspecialchars($numero) . "</li>";
    }
    echo "</ul>";
}
?>






<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Comodidades</title>
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
                <a href="2FormularioBaseDatos.php" class="boton active">Base datos</a>
                <a href="5Generarescritura.php" class="boton">Generar escritura</a>
            </div>
        </div>

        <div class="right-column">
            <div class="middle-column">
                <h1>Gestión de INMUEBLES</h1>
                <div class="table-buttons">
                    <a href="3A_INMUEBLESMatriculas.php" class="boton ">Matrículas</a>
                    <a href="3B_INMUEBLESLinderos.php" class="boton">Linderos</a>
                    <a href="3C_INMUEBLESValores.php" class="boton">Valores</a>
                    <a href="3D_INMUEBLESComodidades.php" class="boton active">Comodidades</a>
                </div>

                <!-- Formulario para pegar la información -->
                <?php if (!empty($error)): ?>
                    <p style="color: red;"><?php echo $error; ?></p>
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                    <textarea id="informacion_completa" name="informacion_completa"rows="10" cols="50" required placeholder="Ingrese aquí la información a procesar"></textarea>
                    <button type="submit">Procesar Comodidades</button>
                    </form><br><br><br><br><br><br><br><br><br><br><br>
                    <a href="2FormularioBaseDatos.php" class="boton">Regresar</a>


                <!-- Mostrar resultados -->
                <?php if (!empty($resultados_no_procesados)): ?>
                    <h2>Registros No Procesados</h2>
                    <table border="1">
                        <thead>
                            <tr>
                                <th>Número</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados_no_procesados as $numero): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($numero); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>

</html>