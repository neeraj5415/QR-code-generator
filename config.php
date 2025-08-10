<?php
$conn = new mysqli("localhost", "root", "Neer@j5415", "qr_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
