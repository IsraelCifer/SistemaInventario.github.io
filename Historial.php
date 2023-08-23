<?php
session_start();
$_SESSION['last_page'] = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['usuario'])) {
  header('Location: login.php');
  exit();
}


require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Obtener el ID del formulario
    $idRegistro = $_POST['Reporte'];

// Obtener los datos de la base de datos
$query = "SELECT r.ID AS Folio, r.Fecha, r.Impresora_Inv AS `No. Inventario`, r.Condiciones, r.Observaciones, r.Persona_Autoriza AS Autoriza, r.Persona_Entrega AS Entrega, r.Persona_Recibe AS Recibe, d.Puesto AS Destino,
    t.Modelo, t.Color, rt.Cantidad,
    SUM(rt.Cantidad) AS Total
    FROM Registro r
    JOIN Direcciones d ON r.Destino = d.ID
    LEFT JOIN Registro_Toner rt ON r.ID = rt.Registro_id
    LEFT JOIN Toner t ON rt.Toner_id = t.ID
    LEFT JOIN Registro_Tambor rb ON r.ID = rb.Registro_id
    LEFT JOIN Tambores b ON rb.Tambor_id = b.ID
    WHERE r.ID = '$idRegistro'
    GROUP BY r.ID, r.Fecha, r.Impresora_Inv, r.Condiciones, r.Observaciones, r.Persona_Autoriza, r.Persona_Entrega, r.Persona_Recibe, d.Puesto, t.Modelo, t.Color, rt.Cantidad, b.Modelo";

$result = $conn->query($query);

// Verificar si se encontró el registro
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $folio = $row['Folio'];
    $fecha = $row['Fecha'];
    $noInventario = $row['No. Inventario'];
    $condiciones = $row['Condiciones'];
    $observaciones = $row['Observaciones'];
    $autoriza = $row['Autoriza'];
    $entrega = $row['Entrega'];
    $recibe = $row['Recibe'];
    $destino = $row['Destino'];
    $total = $row['Total'];

    // Determinar el nombre del documento de origen basado en la condición y la cantidad de toners/tambores
    $numFilas = $result->num_rows;
    switch ($condiciones) {
        case 'Se Entrega Toner Nuevo':
          // Código para el caso 'Se Entregan Toner Nuevo'
            // Obtener el último ID de la tabla Registro
            
            $result = $conn->query($query);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                

                // Obtener los datos de la base de datos
                $query = "SELECT r.ID AS Folio, r.Fecha, r.Impresora_Inv AS `No. Inventario`, r.Condiciones, r.Observaciones, r.Persona_Autoriza AS Autoriza, r.Persona_Entrega AS Entrega, r.Persona_Recibe AS Recibe, d.Puesto AS Destino,
                t.Modelo, t.Color, rt.cantidad AS Cantidad,
                SUM(rt.cantidad) AS Total
                FROM Registro r
                JOIN Registro_Toner rt ON r.ID = rt.Registro_id
                JOIN Toner t ON rt.Toner_id = t.ID
                JOIN Direcciones d ON r.Destino = d.ID
                WHERE r.Id = '$idRegistro'
                GROUP BY r.ID, r.Fecha, r.Impresora_Inv, r.Condiciones, r.Observaciones, r.Persona_Autoriza, r.Persona_Entrega, r.Persona_Recibe, d.Puesto, t.Modelo, t.Color, rt.cantidad";
                $result = $conn->query($query);

                // Verificar si se encontró el registro
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();

                    $folio = $row['Folio'];
                    $fecha = $row['Fecha'];
                    $noInventario = $row['No. Inventario'];
                    $condiciones = $row['Condiciones'];
                    $observaciones = $row['Observaciones'];
                    $autoriza = $row['Autoriza'];
                    $entrega = $row['Entrega'];
                    $recibe = $row['Recibe'];
                    $destino = $row['Destino'];
                    $total = $row['Total'];

                    // Determinar el nombre del documento de origen basado en el número de toners
                    $numFilas = $result->num_rows;
                    switch ($numFilas) {
                        case 1:
                            $nombreDocumentoOrigen = 'toner1.docx';
                            break;
                        case 2:
                            $nombreDocumentoOrigen = 'toner2.docx';
                            break;
                        case 3:
                            $nombreDocumentoOrigen = 'toner3.docx';
                            break;
                        case 4:
                            $nombreDocumentoOrigen = 'toner4.docx';
                            break;
                        default:
                            die("Número de toners no válido.");
                    }

                    // Cargar el documento de Word existente
                    $template = new TemplateProcessor('C:\Users\Usuario\Desktop\\' . $nombreDocumentoOrigen);

                    // Reemplazar los marcadores de posición con los datos de la base de datos
                    $template->setValue('{Folio}', $folio);
                    $template->setValue('{Fecha}', $fecha);
                    $template->setValue('{No. Inventario}', $noInventario);
                    $template->setValue('{Condiciones}', $condiciones);
                    $template->setValue('{Observaciones}', $observaciones);
                    $template->setValue('{Autoriza}', $autoriza);
                    $template->setValue('{Entrega}', $entrega);
                    $template->setValue('{Recibe}', $recibe);
                    $template->setValue('{Destino}', $destino);
                    $template->setValue('{Total}', $total);

                    // Obtener el número de filas de la tabla
                    $numFilas = $result->num_rows;

                    // Clonar la primera fila de la tabla
                    $template->cloneBlock('TABLE_ROW', $numFilas);

                    // Iterar sobre los resultados de la consulta y agregar datos a cada fila de la tabla
                    $i = 1;
                    $result->data_seek(0); // Volver al inicio del resultado
                    while ($row = $result->fetch_assoc()) {
                        $modelo = $row['Modelo'];
                        $color = $row['Color'];
                        $cantidad = $row['Cantidad'];

                        // Reemplazar los marcadores de posición en la fila de la tabla
                        $template->setValue('{Modelo#' . $i . '}', $modelo . ' (' . $color . ')');
                        $template->setValue('{Cantidad#' . $i . '}', $cantidad);

                        $i++;
                    }

                    // Eliminar la primera fila de la tabla (fila de ejemplo)
                    $template->cloneBlock('TABLE_ROW', 0, true);

                    // Guardar el documento final
                    $documentoFinal = 'C:\Users\Usuario\Documents\Reportes Inventario\Reporte ' . $folio . ' Toner.docx';
                    $template->saveAs($documentoFinal);
                } 
                else{
                    echo "No se encontró el registro con ID $idRegistro en la tabla Toner.";
                }
            } else {
                echo "No se encontró ningún registro en la tabla Registro.";
            }
            break;


        case 'Se Entrega Tambor Nuevo':
          // Código para el caso 'Se Entregan Toner Nuevo'
            // Obtener el último ID de la tabla Registro
            $result = $conn->query($query);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
              

                // Obtener los datos de la base de datos
                $query = "SELECT r.ID AS Folio, r.Fecha, r.Impresora_Inv AS `No. Inventario`, r.Condiciones, r.Observaciones, r.Persona_Autoriza AS Autoriza, r.Persona_Entrega AS Entrega, r.Persona_Recibe AS Recibe, d.Puesto AS Destino,
                t.Modelo, rt.Cantidad,
                SUM(rt.Cantidad) AS Total
                FROM Registro r
                JOIN Registro_Tambor rt ON r.ID = rt.Registro_id
                JOIN Tambores t ON rt.Tambor_id = t.ID
                JOIN Direcciones d ON r.Destino = d.ID
                WHERE r.ID = '$idRegistro'
                GROUP BY r.ID, r.Fecha, r.Impresora_Inv, r.Condiciones, r.Observaciones, r.Persona_Autoriza, r.Persona_Entrega, r.Persona_Recibe, d.Puesto, t.Modelo, rt.Cantidad";
                $result = $conn->query($query);


                // Verificar si se encontró el registro
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();

                    $folio = $row['Folio'];
                    $fecha = $row['Fecha'];
                    $noInventario = $row['No. Inventario'];
                    $condiciones = $row['Condiciones'];
                    $observaciones = $row['Observaciones'];
                    $autoriza = $row['Autoriza'];
                    $entrega = $row['Entrega'];
                    $recibe = $row['Recibe'];
                    $destino = $row['Destino'];
                    $total = $row['Total'];

                    // Determinar el nombre del documento de origen basado en el número de toners
                    $numFilas = $result->num_rows;
                    switch ($numFilas) {
                        case 1:
                            $nombreDocumentoOrigen = 'toner1.docx';
                            break;
                        case 2:
                            $nombreDocumentoOrigen = 'toner2.docx';
                            break;
                        case 3:
                            $nombreDocumentoOrigen = 'toner3.docx';
                            break;
                        case 4:
                            $nombreDocumentoOrigen = 'toner4.docx';
                            break;
                        default:
                            die("Número de toners no válido.");
                    }

                    // Cargar el documento de Word existente
                    $template = new TemplateProcessor('C:\Users\Usuario\Desktop\\' . $nombreDocumentoOrigen);

                    // Reemplazar los marcadores de posición con los datos de la base de datos
                    $template->setValue('{Folio}', $folio);
                    $template->setValue('{Fecha}', $fecha);
                    $template->setValue('{No. Inventario}', $noInventario);
                    $template->setValue('{Condiciones}', $condiciones);
                    $template->setValue('{Observaciones}', $observaciones);
                    $template->setValue('{Autoriza}', $autoriza);
                    $template->setValue('{Entrega}', $entrega);
                    $template->setValue('{Recibe}', $recibe);
                    $template->setValue('{Destino}', $destino);
                    $template->setValue('{Total}', $total);

                    // Obtener el número de filas de la tabla
                    $numFilas = $result->num_rows;

                    // Clonar la primera fila de la tabla
                    $template->cloneBlock('TABLE_ROW', $numFilas);

                    // Iterar sobre los resultados de la consulta y agregar datos a cada fila de la tabla
                    $i = 1;
                    $result->data_seek(0); // Volver al inicio del resultado
                    while ($row = $result->fetch_assoc()) {
                        $modelo = $row['Modelo'];
                        $cantidad = $row['Cantidad'];

                        // Reemplazar los marcadores de posición en la fila de la tabla
                        $template->setValue('{Modelo#' . $i . '}', $modelo );
                        $template->setValue('{Cantidad#' . $i . '}', $cantidad);

                        $i++;
                    }

                    // Eliminar la primera fila de la tabla (fila de ejemplo)
                    $template->cloneBlock('TABLE_ROW', 0, true);

                    // Guardar el documento final
                    $documentoFinal = 'C:\Users\Usuario\Documents\Reportes Inventario\Reporte ' . $folio . ' Tambor.docx';
                    $template->saveAs($documentoFinal);
                } else {
                    echo "No se encontró el registro con ID $idRegistro en la tabla Tambor.";
                }
            } else {
                echo "No se encontró ningún registro en la tabla Registro.";
            }
            break;
        default:
            die("Condición no válida.");
    }
} else 
    echo "No se encontró el registro con ID $idRegistro en la tabla Registro.";


}
?>

<style>
  .red-button {
    background-color: #FF0000;
    color: #FFFFFF;
    /* Otros estilos opcionales */
    padding: 5px 14px;
    border: none;
    border-radius: 5px;
  }

  .green-button {
    background-color: #339933;
    color: #FFFFFF;
    /* Otros estilos opcionales */
    padding: 5px 14px;
    border: none;
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
  }

  .blue-button {
    background-color: #3366FF;
    color: #FFFFFF;
    /* Otros estilos opcionales */
    padding: 5px 14px;
    border: none;
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
  }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Historial de Movimientos</title>
</head>
<body background="Pantalla.jpg">
<div align="center"> 
  <h1>Historial de Movimientos</h1>
 
  <form method="post">
    <label for="consulta">Seleccione una opción:</label>
    <select name="consulta" id="consulta" onchange="document.getElementById('myForm').submit()">
      <option value="consulta1" selected>Búsqueda por Folio</option>
      <option value="consulta4">Búsqueda Toner por Folio</option>
      <option value="consulta5">Búsqueda Tambor por Folio</option>
      <option value="consulta2">Búsqueda por dirección</option>
      <option value="consulta6">Búsqueda Mensual</option>
      <option value="consulta3">Búsqueda por impresora</option>
    </select>
    <label for="Busqueda">Busqueda:</label>
    <input type="text" name="Busqueda" value="">
    <input type="submit" value="Mostrar"><br><br>
    <label for="Reporte">Generar Reporte (Coloque el ID):</label>
    <input type="text" name="Reporte" value="">
    <input type="submit" value="Generar" class="blue-button"><br>
    <br><input type="button" value="Generar Informe de Registros" class="green-button" onclick="window.location.href = 'exportar_tabla.php';"><br>
    <br><input type="button" value="Regresar" class="red-button" onclick="window.location.href = 'inventario.php';">
  </form>

  <?php 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $selectedOption = $_POST["consulta"];
  $Busqueda = $_POST["Busqueda"];
  switch ($selectedOption) {
    case "consulta1":
      if (empty($Busqueda)) {
        // Consulta sin filtro de ID
        $query = "SELECT r.ID, r.Folio, r.Fecha, r.Impresora_Inv AS `No. Inventario`, r.Condiciones, r.Observaciones, r.Persona_Autoriza AS Autoriza, r.Persona_Entrega AS Entrega, r.Persona_Recibe AS Recibe, d.Puesto AS Destino,
        GROUP_CONCAT(CONCAT(t.Marca, t.Modelo, '(', t.Color, ') ') SEPARATOR '<br>') AS `Toner Seleccionados`,
        GROUP_CONCAT(rt.cantidad SEPARATOR '<br>') AS Cantidad, 
        SUM(rt.cantidad) AS Total
        FROM Registro r
        JOIN Registro_Toner rt ON r.ID = rt.Registro_id
        JOIN Toner t ON rt.Toner_id = t.ID
        JOIN Direcciones d ON r.Destino = d.ID
        WHERE r.Condiciones = 'Se Entrega Toner Nuevo' 
        GROUP BY r.ID,r.Folio, r.Fecha, r.Impresora_Inv, r.Condiciones, r.Observaciones, r.Persona_Autoriza, r.Persona_Entrega, r.Persona_Recibe, d.Puesto";        
        // Añade la consulta para tambor usando UNION
        $query .= " UNION 
                   SELECT r.ID, r.Folio, r.Fecha, r.Impresora_Inv AS `No. Inventario`, r.Condiciones, r.Observaciones, r.Persona_Autoriza AS Autoriza, r.Persona_Entrega AS Entrega, r.Persona_Recibe AS Recibe, d.Puesto AS Destino,
                   GROUP_CONCAT(CONCAT(t.Marca, t.Modelo) SEPARATOR '<br>') AS `Tambor Seleccionados`,
                   GROUP_CONCAT(rt.Cantidad SEPARATOR '<br>') AS Cantidad,
                   SUM(rt.Cantidad) AS Total
                   FROM Registro r
                   JOIN Registro_Tambor rt ON r.ID = rt.Registro_id
                   JOIN Tambores t ON rt.Tambor_id = t.ID
                   JOIN Direcciones d ON r.Destino = d.ID
                   WHERE r.Condiciones = 'Se Entrega Tambor Nuevo'
                   GROUP BY r.ID, r.Folio, r.Fecha, r.Impresora_Inv, r.Condiciones, r.Observaciones, r.Persona_Autoriza, r.Persona_Entrega, r.Persona_Recibe, d.Puesto";        
        // Ordenar por el campo "ID" de forma descendente y limitar a los últimos 100 registros
        $query .= " ORDER BY ID DESC LIMIT 1500";
      
      } else {
        // Consulta con filtro de ID
        $query = "SELECT r.ID, r.Folio, r.Fecha, r.Impresora_Inv AS `No. Inventario`, r.Condiciones, r.Observaciones, r.Persona_Autoriza AS Autoriza, r.Persona_Entrega AS Entrega, r.Persona_Recibe AS Recibe, d.Puesto AS Destino,
        GROUP_CONCAT(CONCAT(t.Marca, t.Modelo, '(', t.Color, ') ') SEPARATOR '<br>') AS `Toner Seleccionados`,
        GROUP_CONCAT(rt.cantidad SEPARATOR '<br>') AS Cantidad, 
        SUM(rt.cantidad) AS Total
        FROM Registro r
        JOIN Registro_Toner rt ON r.ID = rt.Registro_id
        JOIN Toner t ON rt.Toner_id = t.ID
        JOIN Direcciones d ON r.Destino = d.ID
        WHERE r.Id = '$Busqueda'
        GROUP BY r.ID, r.Folio, r.Fecha, r.Impresora_Inv, r.Condiciones, r.Observaciones, r.Persona_Autoriza, r.Persona_Entrega, r.Persona_Recibe, d.Puesto";        
        $query .= " UNION 
        SELECT r.ID, r.Folio, r.Fecha, r.Impresora_Inv AS `No. Inventario`, r.Condiciones, r.Observaciones, r.Persona_Autoriza AS Autoriza, r.Persona_Entrega AS Entrega, r.Persona_Recibe AS Recibe, d.Puesto AS Destino,
        GROUP_CONCAT(CONCAT(t.Marca, t.Modelo) SEPARATOR '<br>') AS `Tambor Seleccionados`,
        GROUP_CONCAT(rt.Cantidad SEPARATOR '<br>') AS Cantidad,
        SUM(rt.Cantidad) AS Total
        FROM Registro r
        JOIN Registro_Tambor rt ON r.ID = rt.Registro_id
        JOIN Tambores t ON rt.Tambor_id = t.ID
        JOIN Direcciones d ON r.Destino = d.ID
        WHERE r.Id = '$Busqueda'
        GROUP BY r.ID, r.Folio, r.Fecha, r.Impresora_Inv, r.Condiciones, r.Observaciones, r.Persona_Autoriza, r.Persona_Entrega, r.Persona_Recibe, d.Puesto";        
        // Ordenar por el campo "ID" de forma descendente y limitar a los últimos 100 registros
        $query .= " ORDER BY ID DESC LIMIT 1500";
      } 
      break;      

      case "consulta2":
        if (empty($Busqueda)) {
          $query = "SELECT d.Puesto, SUM(rt.cantidad) AS `Total de Toners Entregados`, GROUP_CONCAT(DISTINCT r.Folio ORDER BY r.Folio ASC) AS `Folios`
          FROM Direcciones d
          LEFT JOIN Registro r ON d.ID = r.Destino
          LEFT JOIN Registro_Toner rt ON r.ID = rt.Registro_id
          WHERE rt.cantidad IS NOT NULL
          GROUP BY d.Puesto";
        } else {
          $query = "SELECT d.Puesto, SUM(rt.cantidad) AS `Total de Toners Entregados`, GROUP_CONCAT(DISTINCT r.Folio ORDER BY r.Folio ASC) AS `Folios`
          FROM Direcciones d
          LEFT JOIN Registro r ON d.ID = r.Destino
          LEFT JOIN Registro_Toner rt ON r.ID = rt.Registro_id
          WHERE rt.cantidad IS NOT NULL AND d.Puesto = '$Busqueda'
          GROUP BY d.Puesto";
        }
        break;

        case "consulta3":
          if (empty($Busqueda)) {
            $query = "SELECT r.Impresora_Inv AS `Impresora`, SUM(rt.cantidad) AS `Toner Entregados`, d.Puesto AS `Ultima Dirección`
            FROM Registro r
            JOIN Registro_Toner rt ON r.ID = rt.Registro_id
            LEFT JOIN Direcciones d ON r.Destino = d.ID
            GROUP BY r.Impresora_Inv, `Ultima Dirección`
            ORDER BY `Ultima Dirección` DESC"; // Ordenar en orden descendente por la última dirección
          } else {
            $query = "SELECT r.Impresora_Inv AS `Impresora`, SUM(rt.cantidad) AS `Toner Entregados`, d.Puesto AS `Ultima Dirección`
            FROM Registro r
            JOIN Registro_Toner rt ON r.ID = rt.Registro_id
            LEFT JOIN Direcciones d ON r.Destino = d.ID
            WHERE r.Impresora_Inv = '$Busqueda'
            GROUP BY r.Impresora_Inv, `Ultima Dirección`
            ORDER BY `Ultima Dirección` DESC"; // Ordenar en orden descendente por la última dirección
          }
          break;
        
        
        

        case "consulta4":
          if (empty($Busqueda)) {
            $query = "SELECT r.ID AS Folio, r.Fecha, r.Impresora_Inv AS `No. Inventario`, r.Condiciones, r.Observaciones, r.Persona_Autoriza AS Autoriza, r.Persona_Entrega AS Entrega, r.Persona_Recibe AS Recibe, d.Puesto AS Destino,
            GROUP_CONCAT(CONCAT(t.Marca, t.Modelo, '(', t.Color, ') ') SEPARATOR '<br>') AS `Toner Seleccionados`,
            GROUP_CONCAT(rt.cantidad SEPARATOR '<br>') AS Cantidad, 
            SUM(rt.cantidad) AS Total
            FROM Registro r
            JOIN Registro_Toner rt ON r.ID = rt.Registro_id
            JOIN Toner t ON rt.Toner_id = t.ID
            JOIN Direcciones d ON r.Destino = d.ID
            WHERE r.Condiciones = 'Se Entrega Toner Nuevo'
            GROUP BY r.ID, r.Fecha, r.Impresora_Inv, r.Condiciones, r.Observaciones, r.Persona_Autoriza, r.Persona_Entrega, r.Persona_Recibe, d.Puesto
            ORDER BY r.ID DESC";
    
          } else {
            $query = "SELECT r.ID AS Folio, r.Fecha, r.Impresora_Inv AS `No. Inventario`, r.Condiciones, r.Observaciones, r.Persona_Autoriza AS Autoriza, r.Persona_Entrega AS Entrega, r.Persona_Recibe AS Recibe, d.Puesto AS Destino,
            GROUP_CONCAT(CONCAT(t.Marca, t.Modelo, '(', t.Color, ') ') SEPARATOR '<br>') AS `Toner Seleccionados`,
            GROUP_CONCAT(rt.cantidad SEPARATOR '<br>') AS Cantidad, 
            SUM(rt.cantidad) AS Total
            FROM Registro r
            JOIN Registro_Toner rt ON r.ID = rt.Registro_id
            JOIN Toner t ON rt.Toner_id = t.ID
            JOIN Direcciones d ON r.Destino = d.ID
            WHERE r.Id = '$Busqueda'
            GROUP BY r.ID, r.Fecha, r.Impresora_Inv, r.Condiciones, r.Observaciones, r.Persona_Autoriza, r.Persona_Entrega, r.Persona_Recibe, d.Puesto
            ORDER BY r.ID DESC";
          }
          break;
    
          case "consulta5":
            if (empty($Busqueda)) {
              $query = "SELECT r.ID AS Folio, r.Fecha, r.Impresora_Inv AS `No. Inventario`, r.Condiciones, r.Observaciones, r.Persona_Autoriza AS Autoriza, r.Persona_Entrega AS Entrega, r.Persona_Recibe AS Recibe, d.Puesto AS Destino,
              GROUP_CONCAT(CONCAT(t.Marca, t.Modelo) SEPARATOR '<br>') AS `Tambor Seleccionados`,
              GROUP_CONCAT(rt.Cantidad SEPARATOR '<br>') AS Cantidad,
              SUM(rt.Cantidad) AS Total
              FROM Registro r
              JOIN Registro_Tambor rt ON r.ID = rt.Registro_id
              JOIN Tambores t ON rt.Tambor_id = t.ID
              JOIN Direcciones d ON r.Destino = d.ID
              WHERE r.Condiciones = 'Se Entrega Tambor Nuevo'
              GROUP BY r.ID, r.Fecha, r.Impresora_Inv, r.Condiciones, r.Observaciones, r.Persona_Autoriza, r.Persona_Entrega, r.Persona_Recibe, d.Puesto
              ORDER BY r.ID DESC";
      
            } else {
              $query = "SELECT r.ID AS Folio, r.Fecha, r.Impresora_Inv AS `No. Inventario`, r.Condiciones, r.Observaciones, r.Persona_Autoriza AS Autoriza, r.Persona_Entrega AS Entrega, r.Persona_Recibe AS Recibe, d.Puesto AS Destino,
              GROUP_CONCAT(CONCAT(t.Marca, t.Modelo) SEPARATOR '<br>') AS `Tambores Seleccionados`,
              GROUP_CONCAT(rt.Cantidad SEPARATOR '<br>') AS Cantidad,
              SUM(rt.Cantidad) AS Total
              FROM Registro r
              JOIN Registro_Tambor rt ON r.ID = rt.Registro_id
              JOIN Tambores t ON rt.Tambor_id = t.ID
              JOIN Direcciones d ON r.Destino = d.ID
              WHERE r.Id = '$Busqueda'
              GROUP BY r.ID, r.Fecha, r.Impresora_Inv, r.Condiciones, r.Observaciones, r.Persona_Autoriza, r.Persona_Entrega, r.Persona_Recibe, d.Puesto
              ORDER BY r.ID DESC";
            }
            break;

            case "consulta6":
              if (empty($Busqueda)) {
                // Consulta sin filtro de mes
                $query = "SELECT
                    YEAR(Fecha) AS Anio,
                    CASE MONTH(Fecha)
                        WHEN 1 THEN 'Enero'
                        WHEN 2 THEN 'Febrero'
                        WHEN 3 THEN 'Marzo'
                        WHEN 4 THEN 'Abril'
                        WHEN 5 THEN 'Mayo'
                        WHEN 6 THEN 'Junio'
                        WHEN 7 THEN 'Julio'
                        WHEN 8 THEN 'Agosto'
                        WHEN 9 THEN 'Septiembre'
                        WHEN 10 THEN 'Octubre'
                        WHEN 11 THEN 'Noviembre'
                        WHEN 12 THEN 'Diciembre'
                    END AS Mes,
                    Direcciones.Puesto AS Direccion,
                    SUM(Registro_Toner.cantidad) AS TotalToners,
                    GROUP_CONCAT(CONCAT(Toner.Marca, ' ', Toner.Modelo, ' (', Toner.Color, ')') SEPARATOR ', ') AS ModelosYColores,
                    GROUP_CONCAT(Registro_Toner.cantidad SEPARATOR ', ') AS Cantidades
                  FROM
                    Registro
                  JOIN
                    Direcciones ON Registro.Destino = Direcciones.ID
                  JOIN
                    Registro_Toner ON Registro.ID = Registro_Toner.Registro_id
                  JOIN
                    Toner ON Registro_Toner.Toner_id = Toner.ID
                  GROUP BY
                    Anio,
                    Mes,
                    Direccion
                  ORDER BY
                    Anio,
                    MONTH(Fecha),
                    TotalToners DESC 
                  LIMIT 1000";
              } else {

                // Consulta con filtro de mes
                $meses = array(
                  'enero' => 1,
                  'febrero' => 2,
                  'marzo' => 3,
                  'abril' => 4,
                  'mayo' => 5,
                  'junio' => 6,
                  'julio' => 7,
                  'agosto' => 8,
                  'septiembre' => 9,
                  'octubre' => 10,
                  'noviembre' => 11,
                  'diciembre' => 12
                );
              
                // Convertir el valor de $Busqueda a minúsculas
                $mesBuscado = strtolower($Busqueda);
              
                // Verificar si el mes existe en el array $meses
                if (array_key_exists($mesBuscado, $meses)) {
                  // Obtener el valor numérico del mes
                  $mesNumerico = $meses[$mesBuscado];
                  $query = "SELECT
                  YEAR(Fecha) AS Anio,
                  CASE MONTH(Fecha)
                      WHEN 1 THEN 'Enero'
                      WHEN 2 THEN 'Febrero'
                      WHEN 3 THEN 'Marzo'
                      WHEN 4 THEN 'Abril'
                      WHEN 5 THEN 'Mayo'
                      WHEN 6 THEN 'Junio'
                      WHEN 7 THEN 'Julio'
                      WHEN 8 THEN 'Agosto'
                      WHEN 9 THEN 'Septiembre'
                      WHEN 10 THEN 'Octubre'
                      WHEN 11 THEN 'Noviembre'
                      WHEN 12 THEN 'Diciembre'
                  END AS Mes,
                  Direcciones.Puesto AS Direccion,
                  SUM(Registro_Toner.cantidad) AS TotalToners,
                  GROUP_CONCAT(CONCAT(Toner.Marca, ' ', Toner.Modelo, ' (', Toner.Color, ')') SEPARATOR ', ') AS ModelosYColores,
                  GROUP_CONCAT(Registro_Toner.cantidad SEPARATOR ', ') AS Cantidades
                FROM
                  Registro
                JOIN
                  Direcciones ON Registro.Destino = Direcciones.ID
                JOIN
                  Registro_Toner ON Registro.ID = Registro_Toner.Registro_id
                JOIN
                  Toner ON Registro_Toner.Toner_id = Toner.ID
                  WHERE MONTH(Fecha) = '$mesNumerico'
                GROUP BY
                  Anio,
                  Mes,
                  Direccion
                ORDER BY
                  Anio,
                  MONTH(Fecha),
                  TotalToners DESC 
                LIMIT 1000";
                      }
                    }
              break;
          
      default:
        $query = "";
        break;
      }

    if (!empty($query)) {
      $result = $conn->query($query);

      if ($result->num_rows > 0) {
        echo '<table>';
        switch ($selectedOption) {
          case "consulta1":
            echo '<tr><th>ID</th><th>Folio</th><th>Fecha</th><th>No. Inventario</th><th>Condiciones</th><th>Observaciones</th><th>Autoriza</th><th>Entrega</th><th>Recibe</th><th>Destino</th><th>Consumibles Seleccionados</th><th>Cantidad</th><th>Total</th></tr>';

            while ($row = $result->fetch_assoc()) {
              echo '<tr>';
              echo '<td>'.$row['ID'].'</td>';
              echo '<td>'.$row['Folio'].'</td>';
              echo '<td>'.$row['Fecha'].'</td>';
              echo '<td>'.$row['No. Inventario'].'</td>';
              echo '<td>'.$row['Condiciones'].'</td>';
              echo '<td>'.$row['Observaciones'].'</td>';
              echo '<td>'.$row['Autoriza'].'</td>';
              echo '<td>'.$row['Entrega'].'</td>';
              echo '<td>'.$row['Recibe'].'</td>';
              echo '<td>'.$row['Destino'].'</td>';
              echo '<td style="text-align: center;">'.$row['Toner Seleccionados'].'</td>';
              echo '<td style="text-align: center;">'.$row['Cantidad'].'</td>';
              echo '<td style="text-align: center;">'.$row['Total'].'</td>';
              echo '</tr>';
            }
            break;

            case "consulta2":
              echo '<tr><th>Puesto</th><th style="text-align: center;">Total de Toners Entregados</th><th style="text-align: center;">Folios</th></tr>';
            
              while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>'.$row['Puesto'].'</td>';
                echo '<td style="text-align: center;">'.$row['Total de Toners Entregados'].'</td>';
                echo '<td>'.$row['Folios'].'</td>';
                echo '</tr>';
              }
              break;

            case "consulta3":
              echo '<tr><th>Impresora</th><th>Toner Entregados</th><th>Ultima Dirección</th></tr>';
            
              while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['Impresora'] . '</td>';
                echo '<td style="text-align: center;">' . $row['Toner Entregados'] . '</td>';
                echo '<td>' . $row['Ultima Dirección'] . '</td>';
                echo '</tr>';
              }
              break;
          
          case "consulta4":
            echo '<tr><th>Folio</th><th>Fecha</th><th>No. Inventario</th><th>Condiciones</th><th>Observaciones</th><th>Autoriza</th><th>Entrega</th><th>Recibe</th><th>Destino</th><th>Toner Seleccionados</th><th>Cantidad</th><th>Total</th></tr>';

            while ($row = $result->fetch_assoc()) {
              echo '<tr>';
              echo '<td>'.$row['Folio'].'</td>';
              echo '<td>'.$row['Fecha'].'</td>';
              echo '<td>'.$row['No. Inventario'].'</td>';
              echo '<td>'.$row['Condiciones'].'</td>';
              echo '<td>'.$row['Observaciones'].'</td>';
              echo '<td>'.$row['Autoriza'].'</td>';
              echo '<td>'.$row['Entrega'].'</td>';
              echo '<td>'.$row['Recibe'].'</td>';
              echo '<td>'.$row['Destino'].'</td>';
              echo '<td style="text-align: center;">'.$row['Toner Seleccionados'].'</td>';
              echo '<td style="text-align: center;">'.$row['Cantidad'].'</td>';
              echo '<td style="text-align: center;">'.$row['Total'].'</td>';
              echo '</tr>';
            }
            break;

          case "consulta5":
            echo '<tr><th>Folio</th><th>Fecha</th><th>No. Inventario</th><th>Condiciones</th><th>Observaciones</th><th>Autoriza</th><th>Entrega</th><th>Recibe</th><th>Destino</th><th>Tambores Seleccionados</th><th>Cantidad</th><th>Total</th></tr>';

            while ($row = $result->fetch_assoc()) {
              echo '<tr>';
              echo '<td>'.$row['Folio'].'</td>';
              echo '<td>'.$row['Fecha'].'</td>';
              echo '<td>'.$row['No. Inventario'].'</td>';
              echo '<td>'.$row['Condiciones'].'</td>';
              echo '<td>'.$row['Observaciones'].'</td>';
              echo '<td>'.$row['Autoriza'].'</td>';
              echo '<td>'.$row['Entrega'].'</td>';
              echo '<td>'.$row['Recibe'].'</td>';
              echo '<td>'.$row['Destino'].'</td>';
              echo '<td style="text-align: center;">'.$row['Tambor Seleccionados'].'</td>';
              echo '<td style="text-align: center;">'.$row['Cantidad'].'</td>';
              echo '<td style="text-align: center;">'.$row['Total'].'</td>';
              echo '</tr>';
            }
            break;

            case "consulta6":
              echo '<table>';
              echo '<tr><th>Año</th><th>Mes</th><th>Dirección</th><th>Total Toners</th><th>Modelo</th><th>Cantidades</th></tr>';
            
              while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>'.$row['Anio'].'</td>';
                echo '<td>'.$row['Mes'].'</td>';
                echo '<td>'.$row['Direccion'].'</td>';
                echo '<td style="text-align: center;">'.$row['TotalToners'].'</td>';
                echo '<td>'.$row['ModelosYColores'].'</td>';
                echo '<td>'.$row['Cantidades'].'</td>';
                echo '</tr>';
              }
              break;
        }

        echo '</table>';
      } else {
        echo 'No se encontraron resultados.';
      }
    }
  }
  ?>
</div>
</body>
</html>
