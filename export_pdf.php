<?php
session_start();
//Includes all Composer packages. Here, it's used to load Dompdf (PDF generation library).
require 'vendor/autoload.php';
//DB connection
include 'config.php';
//Imports the Dompdf and Options classes so you can use them without full namespace
use Dompdf\Dompdf;
use Dompdf\Options;
//get user_id if not then it will be null
$uid = $_SESSION['user_id'] ?? null;
//no user then stop script code
if (!$uid) die("User not logged in.");
//Sql query for geting qr codes for logedin user
$qrs = $conn->query("SELECT * FROM qrcodes WHERE created_by = $uid");
//It will check sql query and then check there should be more then 0 result atlest one
if ($qrs && $qrs->num_rows > 0) {
//HTML code for pdf file
    $html = '<h2 style="text-align:center;">My QR Code Report</h2>';
    $html .= '<table border="1" cellpadding="8" cellspacing="0" width="100%">';
    $html .= '<tr style="background-color:#f0f0f0;">
        <th>ID</th>
        <th>QR Name</th>
        <th>URL</th>
        <th>Created</th>
        <th>QR Image</th>
    </tr>';
//loop for printing all the details present in DB qrcodes
    foreach ($qrs as $row) {
//It will get img name from db or leves column null
        $imageFile = $row['image'] ?? '';
//creating a path to img in qrcodes folder 
        $imgPath = 'qrcodes/' . $imageFile;
//This code is for absolute path
        $imgPath = __DIR__ . '/qrcodes/' . $row['Image'];
//If image exists, read it, convert to base64, and embed it as inline <img> in HTML
            if (file_exists($imgPath)) {
                $imgData = base64_encode(file_get_contents($imgPath));
                $imgTag = '<img src="data:image/png;base64,' . $imgData . '" width="80">';
            } else {
                $imgTag = 'N/A';
            }
//uploads data in pdf table from DB
        $html .= '<tr>
            <td>' . $row['Id'] . '</td>
            <td>' . $row['name'] . '</td>
            <td>' . $row['url'] . '</td>
            <td>' . $row['created_at'] . '</td>
            <td>' . $imgTag . '</td>
        </tr>';
    }
//for ending table tag in html 
    $html .= '</table>';
//Creates a new Dompdf options object 
    $options = new Options();
//Enables loading of external or inline images important for QR code image
    $options->set('isRemoteEnabled', true);
//Initializes Dompdf Loads the HTML you created Sets page size to A4 in landscape mode and Renders the PDF.
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
//sends pdf to the browser with file name attachment is true for drict download
    $dompdf->stream("my_qrcodes.pdf", ["Attachment" => true]);
    exit;
} else {
    die("No QR data found.");
}

