<?php
session_start();
// if ($_SESSION['last_page'] !== 'movimientos.php') {
//   header('Location: movimientos.php');
//   exit();
// }
if (!isset($_SESSION['usuario'])) {
  header('Location: login.php');
  exit();
}
$servername = "127.0.0.1"; // Cambia "localhost" si tu base de datos está en un servidor remoto
$username = "root";
$password = "";
$dbname = "inventario";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

// Verificar la conexión
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}


$sql = "SELECT * FROM Toner";
$result = $conn->query($sql);

$options2 = '';
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $options2 .= '<option value="' . $row["ID"] . '">' . $row["Modelo"] . '</option>';
  }
}

$sql = "SELECT * FROM Impresora";
$result = $conn->query($sql);

$optionsImpresoras = '';
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $optionsImpresoras .= '<option value="' . $row["ID"] . '">' . $row["Impresora_Modelo"] . '</option>';
  }
}

if (isset($_POST["impresora"])) {
  $impresoraId = $_POST["impresora"];

  $sql = "SELECT Toner.ID, Toner.Modelo, Toner.Color FROM Toner INNER JOIN Impresora_Toner ON Toner.ID = Impresora_Toner.Toner_id WHERE Impresora_Toner.Impresora_id = '$impresoraId'";
  $result = $conn->query($sql);

  $options = '';
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $options .= '<option value="' . $row["ID"] . '">' . $row["Modelo"] . " / " . $row["Color"] . '</option>';
    }
  }

  echo $options;
}
?>

<?php
// ...

if (isset($_POST["impresora"])) {
  $impresoraId = $_POST["impresora"];
  $modeloTonerId = $_POST["modelo"];
  $cantidad = $_POST["cantidad"];

  // Verificar si la cantidad solicitada es mayor a la cantidad disponible
  $sql = "SELECT cantidad FROM Toner WHERE ID = '$modeloTonerId'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $cantidadDisponible = $row["cantidad"];

    if ($cantidad <= $cantidadDisponible) {
      // Iniciar una transacción
      $conn->begin_transaction();

      try {
        // Restar la cantidad de toner en la tabla "Toner"
        $sql = "UPDATE Toner SET cantidad = cantidad - $cantidad WHERE ID = '$modeloTonerId'";
        $conn->query($sql);

        // Obtener el ID del último registro en la tabla "Registro"
        $sql = "SELECT MAX(ID) AS LastID FROM Registro";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          $row = $result->fetch_assoc();
          $registroId = $row["LastID"];

          // Insertar el toner en la tabla "Registro_Toner"
          $sql = "INSERT INTO Registro_Toner (Registro_id, Toner_id, cantidad)
                  VALUES ('$registroId', '$modeloTonerId', '$cantidad')";
          $conn->query($sql);

          // Confirmar la transacción
          $conn->commit();

          echo "El toner ha sido agregado al registro exitosamente.";
        } else {
          echo "No se encontró ningún registro existente.";
        }
      } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollback();

        echo "Error al realizar la transacción: " . $e->getMessage();
      }
    } else {
      echo "La cantidad solicitada supera la existencia actual del modelo.";
    }
  } else {
    echo "No se encontró el modelo de toner especificado.";
  }
}


?>
<style>
  .red-button {
    background-color: #FF0000;
    color: #FFFFFF;
    /* Otros estilos opcionales */
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
  }
</style>

<style>
  .green-button {
    background-color: #00FF00;
    color: #FFFFFF;
    /* Otros estilos opcionales */
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
  }
</style>

<style>
  .blue-button {
    background-color: #0000FF;
    color: #FFFFFF;
    /* Otros estilos opcionales */
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
  }
</style>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Salida de Toner</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#selector-impresora').change(function() {
        var impresoraId = $(this).val();

        $.ajax({
          type: 'POST',
          url: '<?php echo $_SERVER['PHP_SELF']; ?>',
          data: { impresora: impresoraId },
          success: function(data) {
            $('#selector-toner').html(data);
            $('.toner-selector').prop('disabled', false); // Habilitar el selector de toner
          }
        });
      });

      $('#generar-reporte').click(function(e) {
        e.preventDefault();
        $.ajax({
          type: 'POST',
          url: 'generar_documento.php',
          success: function(response) {
            // Aquí puedes hacer algo con la respuesta del archivo PHP
            alert('El reporte ha sido generado correctamente.');
          }
        });
      });
    });
  </script>
</head>
<body background="Pantalla.jpg">
<div align="center"> 
  <i><h1>Salida</h1></i>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <select id="selector-impresora" name="impresora">
      <option value="">Seleccionar Impresora...</option>
      <?php echo $optionsImpresoras; ?>
    </select>
    <select id="selector-toner" name="modelo" class="toner-selector" disabled>
      <option value="">Seleccionar Modelo...</option>
    </select>
    <br><br>
    <label for="Cantidad">Cantidad:</label>
    <input type="text" name="cantidad" value="">
    <br><br>
    <input type="submit" value="Agregar" class="green-button">
    <input type="button" id="generar-reporte" value="Generar Reporte" class="blue-button">
    <br><br><br><br>
    <input type="button" value="Terminar" class="red-button" onclick="window.location.href = 'inventario.php';">
  </form>
</div>
</body>
</html>


<?php
$sql = "SELECT * FROM Toner ";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <style>
    body {
      background-color: #f0f0f0; /* Color de fondo */
      font-family: Arial, sans-serif;
    }

    table {
      width: 30%;
      margin: 20px auto;
      border-collapse: collapse;
      background-color: #ffffff; /* Color de fondo de la tabla */
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    th, td {
      padding: 10px;
      text-align: left;
    }

    th {
      background-color: #3498db; /* Color de fondo del encabezado de la tabla */
      color: #ffffff; /* Color del texto del encabezado */
    }

    tr:nth-child(even) {
      background-color: #f2f2f2; /* Color de fondo de las filas pares */
    }
  </style>
</head>
<body>
<div align="center"> 
  <?php
  if (mysqli_num_rows($result) > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Marca</th><th>Modelo</th><th>Color</th><th>Cantidad</th></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
      echo "<tr>";
      echo "<td>" . $row["ID"] . "</td>";
      echo "<td>" . $row["Marca"] . "</td>";
      echo "<td>" . $row["Modelo"] . "</td>";
      echo "<td>" . $row["Color"] . "</td>";
      echo "<td>" . $row["cantidad"] . "</td>";
      echo "</tr>";
    }

    echo "</table>";
  } else {
    echo "No se encontraron resultados.";
  }
  ?>
</div>
</body>
</html>