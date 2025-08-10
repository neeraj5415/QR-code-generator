<?php
session_start();
// it wiil remove all the stored variables in session 
session_unset();
// it will destroy or delete the session
session_destroy();
//navigeting to login page
header("Location: login.php");
exit();
?>
