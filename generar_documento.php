<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
use Dompdf\Dompdf;
use Dompdf\Options;

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

$queryCondicion = "SELECT Condiciones FROM Registro WHERE ID = (SELECT MAX(ID) FROM Registro)";
$resultCondicion = $conn->query($queryCondicion);

if ($resultCondicion->num_rows > 0) {
    $rowCondicion = $resultCondicion->fetch_assoc();
    $condicion = $rowCondicion['Condiciones'];

    // Determinar el nombre del documento de origen y realizar la generación del documento según la condición
    switch ($condicion) {
        case 'Se Entrega Toner Nuevo':
            // Código para el caso 'Se Entregan Toner Nuevo'
            // Obtener el último ID de la tabla Registro
            $query = "SELECT MAX(ID) AS LastID FROM Registro";
            $result = $conn->query($query);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $idRegistro = $row['LastID'];

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
                } else {
                    echo "No se encontró el registro con ID $idRegistro en la tabla Toner.";
                }
            } else {
                echo "No se encontró ningún registro en la tabla Registro.";
            }
            break;

            ////////////////////////////////////////////

        case 'Se Entrega Tambor Nuevo':
            // Código para el caso 'Se Entregan Tambor Nuevo'
            // Obtener el último ID de la tabla Registro
            $query = "SELECT MAX(ID) AS LastID FROM Registro";
            $result = $conn->query($query);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $idRegistro = $row['LastID'];

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
            
        // case 'zzzz':
        //     // Código para el caso 'zzzz'
        //     break;                                               PARA MAS CASOS HIPOTÉTICOS
        default:
            die("Condición no válida.");
    }
    // Crear el PDF usando FPDF
    require('fpdf/fpdf.php'); // Asegúrate de ajustar la ruta al archivo fpdf.php

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(40, 10, 'Hello World!'); // Añade tu contenido PDF aquí

    // Guardar el archivo PDF generado
    $documentoFinalPDF = 'C:\Users\Usuario\Documents\Reportes Inventario\Reporte ' . $folio . '.pdf';
    $pdf->Output($documentoFinalPDF, 'F'); // 'F' para guardar el archivo
} else {
    echo "No se encontró la condición para el registro con ID $idRegistro en la tabla Registro.";
}


// Cerrar la conexión a la base de datos
$conn->close();