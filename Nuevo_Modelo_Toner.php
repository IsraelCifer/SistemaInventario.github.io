<?php
session_start();
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
// Verificar si el usuario tiene una sesión válida
if (!isset($_SESSION["usuario"]) || strcasecmp($_SESSION["usuario"], 'admin') !== 0) {
    if (strcasecmp($_SESSION["usuario"], 'admin') !== 0) {
        header("Location: inventarioo.php"); // Redirigir a inventarioo.php si no es admin
    } else {
        header("Location: Nuevo_Modelo_Toner.php"); // Redirigir a inventario.php si no es admin
    }
    exit();
}
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

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Obtener los valores del formulario
  $marca = $_POST["marca"];
  $modelo = $_POST["modelo"];
  $color = $_POST["color"];
  $cantidad = $_POST["cantidad"];

  // Guardar los valores en la base de datos o realizar cualquier otra operación
  $sql = "INSERT INTO Toner (Marca, Modelo, Color, Cantidad) VALUES ('$marca', '$modelo', '$color', $cantidad)";

  // Realizar la consulta a la base de datos
  if ($conn->query($sql) === TRUE) {
    // Redireccionar a la página principal después de guardar los datos
    header("Location: inventario.php");
    exit();
  } else {
    echo "Error al guardar los datos: " . $conn->error;
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Obtener los valores del formulario
  $marca = $_POST["marca"];

  header("Location: inventario.php");
  exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Modelo de Toner</title>
</head>
<body background="Pantalla.jpg">
  <i><h1>Nuevo Modelo de Toner</h1></i>

  <form action="nuevo_modelo_toner.php" method="POST">
    <label for="marca">Marca:</label>
    <br><input type="text" name="marca" required>
    <br>
    <label for="modelo">Modelo:</label>
    <br><input type="text" name="modelo" required>
    <br>
    <label for="color">Color:</label>
<br>
<select name="color" required>
  <option value="" selected>Seleccionar Color...</option>
  <option value="Negro">Negro</option>
  <option value="Azul">Azul</option>
  <option value="Amarillo">Amarillo</option>
  <option value="Magenta">Magenta</option>
</select>

    <br>
    <label for="cantidad">Cantidad:</label>
    <br><input type="text" name="cantidad" required>
    <br>
    <br><input type="submit" value="Guardar">
    <br><br><button onclick="window.location.href = 'Inventario.php';">Cancelar</button> 
  </form>
</body>
</html>

<?php
// Cerrar la conexión a la base de datos
$conn->close();
?>