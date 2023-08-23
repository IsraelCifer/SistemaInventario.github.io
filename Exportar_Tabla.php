<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

// Realiza la consulta a la base de datos para obtener el resultado en $result
$query = "SELECT r.ID, r.Folio, r.Fecha, r.Impresora_Inv AS `No. Inventario`, r.Condiciones, r.Observaciones, r.Persona_Autoriza AS Autoriza, r.Persona_Entrega AS Entrega, r.Persona_Recibe AS Recibe, d.Puesto AS Destino,
        GROUP_CONCAT(CONCAT(t.Marca, t.Modelo, '(', t.Color, ') ')) AS `Toner Seleccionados`,
        GROUP_CONCAT(rt.cantidad) AS Cantidad, 
        SUM(rt.cantidad) AS Total
        FROM Registro r
        JOIN Registro_Toner rt ON r.ID = rt.Registro_id
        JOIN Toner t ON rt.Toner_id = t.ID
        JOIN Direcciones d ON r.Destino = d.ID
        WHERE r.Condiciones = 'Se Entrega Toner Nuevo' 
        GROUP BY r.ID,r.Folio, r.Fecha, r.Impresora_Inv, r.Condiciones, r.Observaciones, r.Persona_Autoriza, r.Persona_Entrega, r.Persona_Recibe, d.Puesto        
        UNION      SELECT r.ID, r.Folio, r.Fecha, r.Impresora_Inv AS `No. Inventario`, r.Condiciones, r.Observaciones, r.Persona_Autoriza AS Autoriza, r.Persona_Entrega AS Entrega, r.Persona_Recibe AS Recibe, d.Puesto AS Destino,
                   GROUP_CONCAT(CONCAT(t.Marca, t.Modelo)) AS `Tambor Seleccionados`,
                   GROUP_CONCAT(rt.Cantidad) AS Cantidad,
                   SUM(rt.Cantidad) AS Total
                   FROM Registro r
                   JOIN Registro_Tambor rt ON r.ID = rt.Registro_id
                   JOIN Tambores t ON rt.Tambor_id = t.ID
                   JOIN Direcciones d ON r.Destino = d.ID
                   WHERE r.Condiciones = 'Se Entrega Tambor Nuevo'
                   GROUP BY r.ID, r.Folio, r.Fecha, r.Impresora_Inv, r.Condiciones, r.Observaciones, r.Persona_Autoriza, r.Persona_Entrega, r.Persona_Recibe, d.Puesto
                   ORDER BY ID DESC LIMIT 1500";

// Ejecutar la consulta y obtener el resultado
$result = $conn->query($query);

// Crear una instancia de PhpSpreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Agregar los encabezados de columna al archivo Excel
$columnNames = ['ID', 'Folio', 'Fecha', 'No. Inventario', 'Condiciones', 'Observaciones', 'Autoriza', 'Entrega', 'Recibe', 'Destino', 'Toner Seleccionados', 'Cantidad', 'Total'];
$colIndex = 'A';
foreach ($columnNames as $columnName) {
    $sheet->setCellValue($colIndex . '1', $columnName);
    $colIndex++;
}

// Llenar el archivo Excel con los datos de la consulta
$row = 2; // Comenzar desde la segunda fila para los datos
while ($data = mysqli_fetch_assoc($result)) {
    $colIndex = 'A';
    foreach ($data as $value) {
        // Reemplazar <br> con saltos de línea (\n)
        $value = str_replace('<br>', " \n", $value);
        $sheet->setCellValue($colIndex . $row, $value);
        $colIndex++;
    }
    $row++;
}

// Establecer el ancho automático de las columnas para que se ajusten al contenido
$lastCol = $sheet->getHighestColumn();
for ($col = 'A'; $col <= $lastCol; $col++) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Crear un objeto de escritura para guardar el archivo Excel
$writer = new Xlsx($spreadsheet);

// Establecer las cabeceras para la descarga del archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Informe de Registros.xlsx"');
header('Cache-Control: max-age=0');

// Guardar el archivo en la salida (descarga)
$writer->save('php://output');
?>
