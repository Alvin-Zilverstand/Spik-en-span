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
$username = isset($_POST['username']) ? trim(htmlspecialchars($_POST['username'])) : '';
$password = isset($_POST['password']) ? trim(htmlspecialchars($_POST['password'])) : '';

// Validate credentials
$sql = "SELECT id, password_hash FROM employees WHERE username = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $password_hash);
    $stmt->fetch();
} else {
    header("Location: ../employee-login.html?error=server_error");
    exit();
}

try {
    if ($password_hash && password_verify($password, $password_hash)) {
        // Start session and store user ID
        session_start();
        $_SESSION['user_id'] = $user_id;
        header("Location: ../qr/qr.html"); // Redirect to QR scanner page
        exit();
    } else {
        // Redirect back to login page with an error message
        header("Location: ../employee-login.html?error=invalid_credentials");
        exit();
    }
} finally {
    $stmt->close();
    $conn->close();
}
?>