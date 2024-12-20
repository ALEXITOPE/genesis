<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Plantillas</title>
    <link rel="stylesheet" type="text/css" href="AllStyles.css">
    <style>
        .row-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .row-container .field-group {
            flex: 1;
            margin-right: 10px;
        }

        .form-row {
            margin-bottom: 10px;
        }

        .error-message {
            color: red;
        }
    </style>
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
                <a href="2FormularioBaseDatos.php" class="boton">Base de Datos</a>
                <a href="3FormularioPlantilla.php" class="boton">Crear nueva plantilla</a>
                <a href="10Generarescritura.php" class="boton">Generar nueva escritura</a>
            </div>
        </div>

        <div class="right-column">
            <div class="middle-column" style="height: 50% autoscroll">
                <h1>GESTIÓN DE PLANTILLAS</h1>
                <form action="10Generarescritura.php" method="POST" class="styled-form">

                    <div class="fpago">
                        <label for="tipo_escritura">FORMA PAGO:</label>
                        <select name="tipo_escritura" id="tipo_escritura" required onchange="mostrarBanco()">
                            <option value="Contado">CONTADO</option>
                            <option value="Hipoteca">HIPOTECA</option>
                            <option value="Leasing">LEASING</option>
                        </select><br>

                        <div class="opcion-banco" id="opcion-banco">
                            <select name="banco" id="nombre_bco" required>
                                <option value="" disabled selected>BANCO:</option>
                            </select>
                        </div>
                    </div>

                    <div class="inmuebles">
                        <div class="matricula">
                            <label for="matr_ap">MATR.AP:</label>
                            <input type="text" id="matr_ap" name="matr_ap" required>
                        </div>
                        <div class="matricula">
                            <label for="matr_pq">MATR.PQ:</label>
                            <input type="text" id="matr_pq" name="matr_pq" required>
                        </div>
                        <div class="matricula">
                            <label for="matr_dp">MATR.DP:</label>
                            <input type="text" id="matr_dp" name="matr_dp" required>
                        </div>
                    </div>

                    <label for="num_compradores">N°. COMPRADORES:</label>
                    <input type="number" id="num_compradores" name="num_compradores" min="1" value="1" max="4" oninput="validateAndToggleBuyersInput()" onkeypress="return isNumberKey(event)">

                    <!-- Contenedor para campos adicionales en filas -->
                    <div id="dynamic-fields"></div>

                    <button type="submit" name="ejecutar" class="boton">GENERAR DOCUMENTO</button>
                </form>

                <!-- Mostrar mensaje de error si existe -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            generateDynamicFields(1);
        });

        function generateDynamicFields(numBuyers) {
            const container = document.getElementById('dynamic-fields');
            container.innerHTML = '';

            for (let i = 1; i <= numBuyers; i++) {
                const rowContainer = document.createElement('div');
                rowContainer.classList.add('row-container');

                const expccField = `
                    <div class="field-group">
                        <label for="lugar_expcc${i}">LUGAR EXP.CC ${i}:</label>
                        <select name="lugar_expcc${i}" id="lugar_expcc${i}" required>
                            <option value="" disabled selected>Expedida en:</option>
                        </select>
                    </div>
                `;

                const domicilioField = `
                    <div class="field-group">
                        <label for="lugar_domicilio${i}">LUGAR DOMICILIO ${i}:</label>
                        <select name="lugar_domicilio${i}" id="lugar_domicilio${i}" required>
                            <option value="" disabled selected>Seleccione opción</option>
                        </select>
                    </div>
                `;

                const compradorField = `
                    <div class="field-group">
                        <label for="cedula_comprador${i}">CC COMPRADOR ${i}:</label>
                        <input type="text" id="cedula_comprador${i}" name="cedula_comprador${i}" ${i == 1 ? 'required' : ''}>
                    </div>
                `;

                rowContainer.innerHTML = expccField + domicilioField + compradorField;
                container.appendChild(rowContainer);
            }
        }

        function validateAndToggleBuyersInput() {
            const numInput = document.getElementById("num_compradores");
            let numBuyers = parseInt(numInput.value) || 1;

            if (!Number.isInteger(numBuyers) || numBuyers < 1 ) {
                alert("Por favor, ingrese un número entero.");
                numInput.value = 1;
                numBuyers = 1;
            }

            generateDynamicFields(numBuyers);
        }

        function isNumberKey(evt) {
            const charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                evt.preventDefault();
                return false;
            }
            return true;
        }

        function mostrarBanco() {
            const tipoEscritura = document.getElementById("tipo_escritura").value;
            const bancoDiv = document.getElementById("opcion-banco");
            bancoDiv.style.display = (tipoEscritura === "Hipoteca" || tipoEscritura === "Leasing") ? "block" : "none";
        }
    </script>

</body>
</html>