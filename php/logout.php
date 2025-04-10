<!-- filepath: c:\xampp\htdocs\Spik-en-span\logout.php -->
<?php
session_start();
session_destroy();
header("Location: employee-login.html");
exit();
?>