<?php
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

// Verificar si se ha proporcionado un ID de toner para eliminar
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
  $id = $_GET["id"];

  // Eliminar el toner de la base de datos
  $sql = "DELETE FROM Toner WHERE ID = '$id'";

  if ($conn->query($sql) === TRUE) {
    echo "Toner eliminado exitosamente.";
  } else {
    echo "Error al eliminar el toner: " . $conn->error;
  }
}

$conn->close();
?>
