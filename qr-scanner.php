<!-- filepath: c:\xampp\htdocs\Spik-en-span\qr-scanner.php -->
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: employee-login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">QR Code Scanner</h1>
        <p class="text-center">This page is under construction.</p>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>