<?php
session_start();
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
// Verificar si el usuario tiene una sesión válida
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Menú de Inicio</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column; /* Añadimos esta línea para centrar verticalmente */
            height: 100vh;
            background-color: #f2f2f2;
        }

        .menu {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between; /* Colocar los botones alrededor */
        }

        .button {
            min-width: 390px;
            height: 250px;
            margin: 10px;
            color: #fff;
            padding: 10px;
            font-weight: bold;
            font-size: 24px;
            font-family: "Arial"; /* Cambiamos la fuente a Comic Sans */
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            outline: none;
            border-radius: 5px;
            border: 5px solid #000;
            background: #2c0b8e;
        }

        .button:hover {
            background: #fff;
            color: #2c0b8e;
        }

        .button img {
            width: 140px;
            height: 150px;
            margin-bottom: 10px;
        }

        .button-red {
            min-width: 130px;
            height: 40px;
            color: #fff;
            padding: 5px 10px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            display: inline-block;
            outline: none;
            border-radius: 5px;
            border: 2px solid #d90429;
            background: #d90429;
        }
        .button-red:hover {
            background: #fff;
            color: #d90429
        }
    </style>

</head>
<body background="Pantalla.jpg">
    
    <div>
        <h1 style="font-family: 'Arial';">Sistema de Gestión de Inventario</h1>
    </div>
    <div class="menu">
        <button class="button" onclick="window.location.href = 'Movimientos.php';">
            <img src="images/imagen1.png" alt="Icono 1">
            SALIDA
        </button>
        
        <button class="button" onclick="window.location.href = 'Historial.php';">
            <img src="images/imagen3.png" alt="Icono 3">
            HISTORIAL
        </button>
    </div>

        <form action="cerrar_sesion.php" method="POST">
            <br><br><br><button class="button-red">
                Cerrar Sesión
            </button>
        </form>
</body>
</html>
