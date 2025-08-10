<?php
$servername = "sql111.infinityfree.com"; // Your MySQL Host
$username = "if0_39673432";      // Your InfinityFree DB username
$password = "Neeraj5415";       // Your InfinityFree DB password
$dbname = "if0_39673432"; // Your DB name

$conn = new mysqli("localhost", "root", "Neer@j5415", "qr_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
