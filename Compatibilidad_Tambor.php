<?php
session_start();
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
// Verificar si el usuario tiene una sesión válida
if (!isset($_SESSION["usuario"]) || strcasecmp($_SESSION["usuario"], 'admin') !== 0) {
    if (strcasecmp($_SESSION["usuario"], 'admin') !== 0) {
        header("Location: inventarioo.php"); // Redirigir a inventarioo.php si no es admin
    } else {
        header("Location: Compatibilidad_Tambor.php"); // Redirigir a inventario.php si no es admin
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

// Obtener los modelos de tambor con su color
$tamborQuery = "SELECT Modelo, ID FROM Tambores";
$tamborResult = $conn->query($tamborQuery);

// Procesar el formulario al enviarlo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Obtener los valores seleccionados del formulario
  $impresoraSeleccionada = $_POST["impresora"];
  $tamborSeleccionado = $_POST["tambor"];

  // Verificar si los valores seleccionados existen en las tablas referenciadas
  $impresoraID = null;
  $tamborID = null;

  // Verificar si la impresora seleccionada existe
  while ($row = $impresoraResult->fetch_assoc()) {
    if ($row["Impresora_Modelo"] == $impresoraSeleccionada) {
      $impresoraID = $row["ID"];
      break;
    }
  }

  // Verificar si el tambor seleccionado existe
  while ($row = $tamborResult->fetch_assoc()) {
    if ($row["Modelo"] == $tamborSeleccionado) {
      $tamborID = $row["ID"];
      break;
    }
  }

  if ($impresoraID && $tamborID) {
    // Insertar la compatibilidad en la tabla Impresora_Tambor
    $insertQuery = "INSERT INTO Impresora_Tambor (Impresora_id, Tambor_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ii", $impresoraID, $tamborID);
    $stmt->execute();
    $stmt->close();
  } else {
    // Mostrar un mensaje de error si los valores seleccionados no existen en las tablas referenciadas
    echo "Error: La impresora o el tambor seleccionado no existen.";
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
    <label for="tambor">Modelo de Tambor:</label>
    <select name="tambor" id="tambor">
    <option value=''>Seleccionar Tambor...</option>
      <?php
      // Mostrar los modelos y colores de tambor en el selector
      if ($tamborResult->num_rows > 0) {
        mysqli_data_seek($tamborResult, 0); // Reiniciar el puntero del resultado
        while ($row = $tamborResult->fetch_assoc()) {
          echo "<option value='" . $row["Modelo"] . "'>" . $row["Modelo"] .  "</option>";
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
