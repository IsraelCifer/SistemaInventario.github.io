<?php
session_start();
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
// Verificar si el usuario tiene una sesión válida
if (!isset($_SESSION["usuario"]) || strcasecmp($_SESSION["usuario"], 'admin') !== 0) {
    if (strcasecmp($_SESSION["usuario"], 'admin') !== 0) {
        header("Location: inventarioo.php"); // Redirigir a inventarioo.php si no es admin
    } else {
        header("Location: Nuevo_Modelo_Tambor.php"); // Redirigir a inventario.php si no es admin
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

// Consultar las impresoras de la base de datos
$sql = "SELECT ID, Impresora_Modelo FROM Impresora";
$result = $conn->query($sql);

// Procesar el formulario si se envió
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $marca = $_POST["marca"];
  $modelo = $_POST["modelo"];
  $cantidad = $_POST["cantidad"];
  $impresora = $_POST["Impresora"];

  // Insertar los datos en la tabla Tambores
$sql_insert = "INSERT INTO Tambores (Marca, Modelo, Cantidad) VALUES ('$marca', '$modelo', $cantidad)";
if ($conn->query($sql_insert) === TRUE) {
  echo "El nuevo modelo de tambor se ha guardado correctamente.";
} else {
  echo "Error al guardar el nuevo modelo de tambor: " . $conn->error;
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Modelo de Tambor</title>
</head>
<body background="Pantalla.jpg">
  <i><h1>Nuevo Modelo de Tambor</h1></i>

  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <label for="marca">Marca:</label>
    <br><input type="text" name="marca" required>
    <br>
    <label for="modelo">Modelo:</label>
    <br><input type="text" name="modelo" required>
    <br>
    <label for="cantidad">Cantidad:</label>
    <br><input type="text" name="cantidad" required>
    <br>
    <label for="Impresora">Impresora:</label>
    <br>
    <br>
    <br><input type="submit" value="Guardar">
    <button onclick="window.location.href = 'Inventario.php';">Terminar</button>
    <br><br><button onclick="window.location.href = 'Inventario.php';">Cancelar</button> 
  </form>
</body>
</html>

<?php
// Cerrar el resultado de la consulta
$result->close();

// Cerrar la conexión a la base de datos
$conn->close();
?>
