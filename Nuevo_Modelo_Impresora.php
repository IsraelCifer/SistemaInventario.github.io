<?php
session_start();
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
// Verificar si el usuario tiene una sesión válida
if (!isset($_SESSION["usuario"]) || strcasecmp($_SESSION["usuario"], 'admin') !== 0) {
    if (strcasecmp($_SESSION["usuario"], 'admin') !== 0) {
        header("Location: inventarioo.php"); // Redirigir a inventarioo.php si no es admin
    } else {
        header("Location: Nuevo_Modelo_Impresora.php"); // Redirigir a inventario.php si no es admin
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
  $modelo = $_POST["modelo"];

  // Guardar los valores en la base de datos o realizar cualquier otra operación
  $sql = "INSERT INTO impresora (Impresora_Modelo) VALUES ('$modelo')";


  // Realizar la consulta a la base de datos
  if ($conn->query($sql) === TRUE) {
    // Redireccionar a la página principal después de guardar los datos
    header("Location: inventario.php");
    exit();
  } else {
    echo "Error al guardar los datos: " . $conn->error;
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Modelo de Impresora</title>
</head>
<body background="Pantalla.jpg">
  <i><h1>Nuevo Modelo de Impresora</h1></i>

  <form action="nuevo_modelo_Impresora.php" method="POST">
    <br>
    <label for="modelo">Modelo:</label>
    <br><input type="text" name="modelo" required>
    <br>
    <br><input type="submit" value="Guardar">
    <br><br><button onclick="window.location.href = 'Inventario.php';">Cancelar</button>  
  </form>
</body>
</html>

<?php
// Obtener todos los modelos de impresora de la base de datos
$sql = "SELECT Impresora_Modelo FROM Impresora";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  echo "<h2>Modelos de Impresora:</h2>";
  echo "<ul>";

  while ($row = $result->fetch_assoc()) {
    echo "<li>" . $row["Impresora_Modelo"] . "</li>";
  }  
  echo "</ul>";
} else {
  echo "No se encontraron modelos de impresora.";
}
// Cerrar la conexión a la base de datos
$conn->close();
?>