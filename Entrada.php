<?php
session_start();
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
// Verificar si el usuario tiene una sesión válida
if (!isset($_SESSION["usuario"]) || strcasecmp($_SESSION["usuario"], 'admin') !== 0) {
    if (strcasecmp($_SESSION["usuario"], 'admin') !== 0) {
        header("Location: inventarioo.php"); // Redirigir a inventarioo.php si no es admin
    } else {
        header("Location: Entrada.php"); // Redirigir a inventario.php si no es admin
    }
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

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Obtener los valores del formulario
  $modeloId = $_POST['modelo'];
  $cantidad = $_POST['cantidad'];

  // Verificar si se ha seleccionado un modelo
  if (!empty($modeloId) && is_numeric($cantidad)) {
    // Actualizar la cantidad de toners en la tabla
    $sql = "UPDATE Toner SET Cantidad = Cantidad + $cantidad WHERE ID = $modeloId";

    if ($conn->query($sql) === TRUE) {
      echo "Cantidad de toner actualizada correctamente.";
    } else {
      echo "Error al actualizar la cantidad de toner: " . $conn->error;
    }
  } else {
    echo "Por favor, selecciona un modelo y proporciona una cantidad válida.";
  }
}

$sql = "SELECT ID, Modelo, Color FROM Toner";
$result = $conn->query($sql);
$options = "";
while ($row = $result->fetch_assoc()) {
  $modeloId = $row['ID'];
  $modelo = $row['Modelo'];
  $color = $row['Color'];
  $options .= "<option value='$modeloId'>$modelo - $color</option>";
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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Entrada de Toner</title>
</head>
<body background="Pantalla.jpg">
<div align="center"> 
  <i><h1>Entrada</h1></i>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <select id="selector-toner" name="modelo">
      <option value=''>Seleccionar Modelo...</option>
      <?php echo $options; ?>
    </select>
    <br><br>
    <label for="Cantidad">Cantidad:</label>
    <input type="text" name="cantidad" value="">
    <br><br>
    <input type="submit" value="Agregar" class="green-button">
    <br><br><br><br>
    <input type="button" value="Terminar" class="red-button" onclick="window.location.href = 'inventario.php';">
  </form>

  <?php
    $conn->close();
  ?>
</div>
