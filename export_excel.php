<?php
session_start();

//Loads all the PHP libraries installed via Composer, including PhpSpreadsheet.
require 'vendor/autoload.php';
//DB connection
include 'config.php';
//Imports PhpSpreadsheet classes to create and save Excel spreadsheets.
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
//for geting current logedin user id
$uid = $_SESSION['user_id'] ?? null;

if (!$uid) die("User not logged in.");
//Query for geting all qr codes by logedin user
$qrs = $conn->query("SELECT * FROM qrcodes WHERE created_by = $uid");
// execute query and checks that there shoulld be more then 0 entry or row
if ($qrs && $qrs->num_rows > 0) {
//Create a sheet and select 1st worksheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

// Set header 
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'QR Name');
    $sheet->setCellValue('C1', 'QR URL');
    $sheet->setCellValue('D1', 'Created At');
    $sheet->setCellValue('E1', 'QR Image');
//width of columns
    $sheet->getColumnDimension('B')->setWidth(25);
    $sheet->getColumnDimension('C')->setWidth(30);
    $sheet->getColumnDimension('D')->setWidth(25);
    $sheet->getColumnDimension('E')->setWidth(25);
//data will be starts filling from row no 2
    $rowNum = 2;
//loop for go through each row
    foreach ($qrs as $row) {
//filling data
        $sheet->setCellValue("A$rowNum", $row['Id']);
        $sheet->setCellValue("B$rowNum", $row['name']);
        $sheet->setCellValue("C$rowNum", $row['url']);
        $sheet->setCellValue("D$rowNum", $row['created_at']);
//file path for qr img
        $imgPath = 'qrcodes/' . $row['Image'];
//if file is present in folder
        if (file_exists($imgPath)) {
//hight og row
            $sheet->getRowDimension($rowNum)->setRowHeight(70);
//Create a new drawing (image object) in PhpSpreadsheet - setPath() loads the image file, setCoordinates() sets which cell to put it in (column E, current row).
//setHeight() scales the image height to 60px, setWorksheet() places it on the current Excel sheet.
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath($imgPath);
            $drawing->setCoordinates("E$rowNum");
            $drawing->setHeight(60);
            $drawing->setWorksheet($sheet);
        }
        $rowNum++;
    }
    //Sending file to browser 
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //downloading that file
    header('Content-Disposition: attachment; filename="my_qrcodes.xlsx"');
    // Create a writer object for .xlsx format, and send the file content to browser output (php://output), which triggers the download
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} else {
    die("No QR data found.");
}
