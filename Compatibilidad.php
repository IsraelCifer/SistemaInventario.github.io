<?php
session_start();
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
// Verificar si el usuario tiene una sesión válida
if (!isset($_SESSION["usuario"]) || strcasecmp($_SESSION["usuario"], 'admin') !== 0) {
    if (strcasecmp($_SESSION["usuario"], 'admin') !== 0) {
        header("Location: inventarioo.php"); // Redirigir a inventarioo.php si no es admin
    } else {
        header("Location: Compatibilidad.php"); // Redirigir a inventario.php si no es admin
    }
    exit();
}
// Compatibilidad.php
$servername = "127.0.0.1"; // Cambia "localhost" si tu base de datos está en un servidor remoto
$username = "root";
$password = "";
$dbname = "inventario";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los modelos de impresoras
$impresoraQuery = "SELECT Impresora_Modelo, ID FROM Impresora";
$impresoraResult = $conn->query($impresoraQuery);

// Obtener los modelos de toner con su color
$tonerQuery = "SELECT Modelo, Color, ID FROM Toner";
$tonerResult = $conn->query($tonerQuery);

// Procesar el formulario al enviarlo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Obtener los valores seleccionados del formulario
  $impresoraSeleccionada = $_POST["impresora"];
  $tonerSeleccionado = $_POST["toner"];

  // Verificar si los valores seleccionados existen en las tablas referenciadas
  $impresoraID = null;
  $tonerID = null;

  // Verificar si la impresora seleccionada existe
  while ($row = $impresoraResult->fetch_assoc()) {
    if ($row["Impresora_Modelo"] == $impresoraSeleccionada) {
      $impresoraID = $row["ID"];
      break;
    }
  }

  // Verificar si el toner seleccionado existe
  while ($row = $tonerResult->fetch_assoc()) {
    if ($row["Modelo"] == $tonerSeleccionado) {
      $tonerID = $row["ID"];
      break;
    }
  }

  if ($impresoraID && $tonerID) {
    // Insertar la compatibilidad en la tabla Impresora_Toner
    $insertQuery = "INSERT INTO Impresora_Toner (Impresora_id, Toner_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ii", $impresoraID, $tonerID);
    $stmt->execute();
    $stmt->close();
  } else {
    // Mostrar un mensaje de error si los valores seleccionados no existen en las tablas referenciadas
    echo "Error: La impresora o el toner seleccionado no existen.";
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Compatibilidad</title>
</head>
<body background="Pantalla.jpg">
  <i><h1>Compatibilidad</h1></i>

  <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <label for="impresora">Modelo de Impresora:</label>
    <select name="impresora" id="impresora">
    <option value=''>Seleccionar Impresora...</option>
      <?php
      // Mostrar los modelos de impresoras en el selector
      if ($impresoraResult->num_rows > 0) {
        mysqli_data_seek($impresoraResult, 0); // Reiniciar el puntero del resultado
        while ($row = $impresoraResult->fetch_assoc()) {
          echo "<option value='" . $row["Impresora_Modelo"] . "'>" . $row["Impresora_Modelo"] . "</option>";
        }
      }
      ?>
    </select>
    <label for="toner">Modelo de Toner:</label>
    <select name="toner" id="toner">
    <option value=''>Seleccionar Toner...</option>
      <?php
      // Mostrar los modelos y colores de toner en el selector
      if ($tonerResult->num_rows > 0) {
        mysqli_data_seek($tonerResult, 0); // Reiniciar el puntero del resultado
        while ($row = $tonerResult->fetch_assoc()) {
          echo "<option value='" . $row["Modelo"] . "'>" . $row["Modelo"] . " - " . $row["Color"] . "</option>";
        }
      }
      ?>
    </select>
    <br><br>
    <input type="submit" value="Agregar compatibilidad">
  </form>

  <br><br><br><button onclick="window.location.href = 'Inventario.php';">Terminar</button>  

</body>
</html>
<?php
// Cerrar la conexión a la base de datos
$conn->close();
?>
