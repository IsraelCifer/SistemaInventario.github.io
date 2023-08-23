<?php
session_start();
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Obtener los valores del formulario
  $Fecha = $_POST["fecha"];
  $Folio = $_POST["Folio"];
  $Impresora_Inv = $_POST["Inventario"];
  $Condiciones = $_POST["Condiciones"];
  $Observaciones = $_POST["Observaciones"];
  $Persona_Autoriza = $_POST["Autoriza"];
  $Persona_Entrega = $_POST["Entrega"];
  $Persona_Recibe = $_POST["Recibe"];
  $Destino = $_POST["Destino"];

  // Verificar si las partes específicas han sido editadas
  $observacionesPredeterminadas = "Se Entrega Cartucho Original en Caja Cerrada Para la Impresora ";
  $inventarioPredeterminado = "5151000096-";
  
  if ($Observaciones === $observacionesPredeterminadas || $Impresora_Inv === $inventarioPredeterminado) {
    echo "Por favor, edita las observaciones y el número de inventario antes de continuar.";
  } else {
    // Insertar los datos en la tabla Registro
    $sql = "INSERT INTO Registro (Folio, Fecha, Impresora_Inv, Condiciones, Observaciones, Persona_Autoriza, Persona_Entrega, Persona_Recibe, Destino)
    VALUES ('$Folio', '$Fecha', '$Impresora_Inv', '$Condiciones', '$Observaciones', '$Persona_Autoriza', '$Persona_Entrega', '$Persona_Recibe', '$Destino')";

    if ($conn->query($sql) === TRUE) {
      echo "Los datos se han insertado correctamente en la tabla Registro.";
      switch ($Condiciones) {
        case 'Se Entrega Toner Nuevo':
          header("Location: Salida.php"); // Redirigir a salida.php
          break;
        case 'Se Entrega Tambor Nuevo':
          header("Location: Salida_Tambor.php"); // Redirige a salida tambor
          break;
      }
      exit(); // Asegurarse de que el script se detenga después de la redirección
    } else {
      echo "Error al insertar los datos: " . $conn->error;
    }
  }
}

  
$sql = "SELECT * FROM Direcciones ORDER BY Puesto ASC";
$result = $conn->query($sql);

$options3 = '';
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $options3 .= '<option value="' . $row["ID"] . '">' . $row["Puesto"] . '</option>';
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

$query = "SELECT Folio FROM Registro ORDER BY Folio DESC LIMIT 1";
$result = $conn->query($query);
if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $ultimoFolio = intval($row['Folio']) + 1; // Sumar uno al último folio
} else {
  $ultimoFolio = 1; // Si no hay registros, iniciar desde 1
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Entradas y Salidas</title>
</head>
<body background="Pantalla.jpg">
<div align="center"> 
  <i><h1>Entradas y Salidas</h1></i>

  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <br>
    <label for="fecha">Fecha:</label>
    <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
    <label for="Folio">Folio de Operacion:</label>
    <input type="text" name="Folio" value="<?php echo $ultimoFolio; ?>" required>
    <br><label for="Inventario">Numero de Inventario(Impresora):</label>
    <input type="text" name="Inventario" value="5151000096-" required><br><br>
    <label for="Origen">Area de Origen:</label>
    <select name="Origen" required>
      <option value='128'>Subdireccion de Informatica</option>
      <?php echo $options3; ?>
    </select>
    <br>
    <label for="Destino">Area de Destino:</label>
    <select name="Destino" required>
      <option value=''>Seleccionar Destino...</option>
      <?php echo $options3; ?>
    </select>
    <br>
    <br>
    <label for="Condiciones">Condiciones:</label><br>
    <select name="Condiciones" required>
    <option value=''selected>Seleccione Una Opción...</option>
      <option value='Se Entrega Toner Nuevo'>Se Entrega Toner Nuevo</option>
      <option value='Se Entrega Tambor Nuevo'>Se Entrega Tambor Nuevo</option>
    </select>
    <br><br>
    <label for="Observaciones">Observaciones:</label>
    <br>
    <textarea name="Observaciones" rows="4" cols="50" required>Se Entrega Cartucho Original en Caja Cerrada Para la Impresora </textarea>
    <br><br>
    <label for="Autoriza">Autoriza:</label>
    <select name="Autoriza" required>
    <option value=''selected>Seleccione Una Opción...</option>
      <option value='LIC. SERGIO JAVIER RIVEROLL ARELLANO'>LIC. SERGIO JAVIER RIVEROLL ARELLANO</option>
      <option value='ING. ISAAC HUERTA CASTILLO'>ING. ISAAC HUERTA CASTILLO</option>
      <option value='ING. JOSE ALBERTO JUAREZ RIOS'>ING. JOSE ALBERTO JUAREZ RIOS</option>
    </select>
    <br><br>
    <label for="Entrega">Entrega:</label>
    <select name="Entrega" required>
    <option value=''selected>Seleccione Una Opción...</option>
      <option value='LIC. SERGIO JAVIER RIVEROLL ARELLANO'>LIC. SERGIO JAVIER RIVEROLL ARELLANO</option>
      <option value='ING. ISAAC HUERTA CASTILLO'>ING. ISAAC HUERTA CASTILLO</option>
      <option value='ING. JOSE ALBERTO JUAREZ RIOS'>ING. JOSE ALBERTO JUAREZ RIOS</option>
    </select>
    <br><br>
    <label for="Recibe">Recibe:</label>
    <input type="text" name="Recibe" value="" size="40" required><br>
    <br><br><br>
    <input type="button" value="Cancelar" onclick="window.location.href = 'inventario.php';">
    <input type="submit" value="Continuar">
    <br><br><br>
  </form>

  <?php
    $conn->close();
  ?>
</div>
