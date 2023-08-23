<!DOCTYPE html>
<html>
<head>
  <title>Iniciar sesión</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body{
        background-image: url("Pantalla.jpg");
        background-size: cover;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center mt-5">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h2 class="text-center">Iniciar sesión</h2>
          </div>
          <div class="card-body">
            <form method="POST" action="login.php">
              <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" class="form-control">
              </div>
              <div class="form-group">
                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" class="form-control">
              </div>
              <div class="text-center">
                <button type="submit" class="btn btn-primary">Iniciar sesión</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

<?php
// Conexión a la base de datos
$servername = "127.0.0.1"; // Cambia "localhost" si tu base de datos está en un servidor remoto
$username = "root";
$password = "";
$dbname = "inventario";
 $conn = new mysqli($servername, $username, $password, $dbname);
 // Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Obtener los datos del formulario
  $usuario = $_POST["usuario"];
  $contrasena = $_POST["contrasena"];
   // Consulta SQL para verificar si el usuario y la contraseña son válidos
   $sql = "SELECT id, privilegio FROM usuarios WHERE usuario = '$usuario' AND contrasena = '$contrasena'";
  $result = $conn->query($sql);
  if ($result->num_rows > 0) {
    // Obtener los valores del usuario de la base de datos
    $row = $result->fetch_assoc();
    $privilegio = $row['privilegio'];

    // Iniciar sesión
    session_start();
    $_SESSION["usuario"] = $usuario;
    $_SESSION['privilegio'] = $privilegio;

    // Redirigir al usuario a la página inventario.php
    header("Location: inventario.php");
    exit();
} else {
    // Mostrar un mensaje de error
    echo "Usuario o contraseña incorrectos.";
}
}
 $conn->close();
?>