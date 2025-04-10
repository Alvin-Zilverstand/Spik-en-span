<!-- filepath: c:\xampp\htdocs\Spik-en-span\process_login.php -->
<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "spik_en_span";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve form data
$username = $_POST['username'];
$password = $_POST['password'];

// Validate credentials
$sql = "SELECT id, password_hash FROM employees WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id, $password_hash);
$stmt->fetch();

if ($password_hash && password_verify($password, $password_hash)) {
    // Start session and store user ID
    session_start();
    $_SESSION['user_id'] = $user_id;
    echo "Login successful!";
    header("Location: ../qr/qr.html"); // Redirect to QR scanner page
} else {
    echo "Invalid login credentials.";
}

$stmt->close();
$conn->close();
?>