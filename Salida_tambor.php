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


$sql = "SELECT * FROM Tambores";
$result = $conn->query($sql);

$options2 = '';
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $options2 .= '<option value="' . $row["ID"] . '">' . $row["Modelo"] . '</option>';
  }
}

$sql = "SELECT Impresora.ID, Impresora.Impresora_Modelo 
        FROM Impresora 
        INNER JOIN Impresora_Tambor ON Impresora.ID = Impresora_Tambor.Impresora_id";
$result = $conn->query($sql);

$optionsImpresoras = '';
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $optionsImpresoras .= '<option value="' . $row["ID"] . '">' . $row["Impresora_Modelo"] . '</option>';
  }
}


if (isset($_POST["impresora"])) {
  $impresoraId = $_POST["impresora"];

  $sql = "SELECT Tambores.ID, Tambores.Modelo FROM Tambores INNER JOIN Impresora_Tambor ON Tambores.ID = Impresora_Tambor.Tambor_id WHERE Impresora_Tambor.Impresora_id = '$impresoraId'";
  $result = $conn->query($sql);

  $options = '';
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $options .= '<option value="' . $row["ID"] . '">' . $row["Modelo"] . '</option>';
    }
  }

  echo $options;
}
?>

<?php
// ...

if (isset($_POST["impresora"])) {
  $impresoraId = $_POST["impresora"];
  $modeloTamborId = $_POST["modelo"];
  $cantidad = $_POST["cantidad"];

  // Iniciar una transacción
  $conn->begin_transaction();

  try {
    // Restar la cantidad de toner en la tabla "Toner"
    $sql = "UPDATE Tambores SET cantidad = cantidad - $cantidad WHERE ID = '$modeloTamborId'";
    $conn->query($sql);

    // Obtener el ID del último registro en la tabla "Registro"
    $sql = "SELECT MAX(ID) AS LastID FROM Registro";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $registroId = $row["LastID"];

      // Insertar el toner en la tabla "Registro_Toner"
      $sql = "INSERT INTO Registro_Tambor (Registro_id, Tambor_id, cantidad)
              VALUES ('$registroId', '$modeloTamborId', '$cantidad')";
      $conn->query($sql);

      // Confirmar la transacción
      $conn->commit();

      echo "El Tambor ha sido agregado al registro exitosamente.";
    } else {
      echo "No se encontró ningún registro existente.";
    }
  } catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conn->rollback();

    echo "Error al realizar la transacción: " . $e->getMessage();
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
  <title>Salida de Tambor</title>
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
    <style>
    table {
      border-collapse: collapse;
      width: 50%;
      opacity: 0.8;
    }
    
    th, td {
      border: 1px solid black;
      padding: 6px;
      text-align: left;
    }
    
    th {
      background-color: #dddddd;
    }
  </style>
</head>
<body background="Pantalla.jpg">
<div align="center"> 
  <i><h1>Salida Tambor</h1></i>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <select id="selector-impresora" name="impresora">
      <option value="">Seleccionar Impresora...</option>
      <?php echo $optionsImpresoras; ?>
    </select>
    <select id="selector-toner" name="modelo">
      <option value=''>Seleccionar Modelo...</option>
      <?php echo $options2; ?>
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

  <table>
    <tr>
      <th>ID</th>
      <th>Marca</th>
      <th>Modelo</th>
      <th>Cantidad</th>
    </tr>
    <?php
    $sql = "SELECT * FROM Tambores";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["ID"] . "</td>";
        echo "<td>" . $row["Marca"] . "</td>";
        echo "<td>" . $row["Modelo"] . "</td>";
        echo "<td>" . $row["Cantidad"] . "</td>";
        echo "</tr>";
      }
    } else {
      echo "<tr><td colspan='4'>No se encontraron tambores.</td></tr>";
    }
    ?>
  </table>
</div>
</body>
</html>

  <?php
    $conn->close();
  ?>

