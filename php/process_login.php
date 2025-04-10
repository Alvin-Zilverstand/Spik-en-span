<?php
$servername = "localhost";
$username = "database12";
$password = "181t$1lJg";
$dbname = "spik_en_span";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = isset($_POST['username']) ? trim(htmlspecialchars($_POST['username'])) : '';
$password = isset($_POST['password']) ? trim(htmlspecialchars($_POST['password'])) : '';

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
        session_start();
        $_SESSION['user_id'] = $user_id;
        header("Location: ");
        exit();
    } else {
        header("Location: ../employee-login.php?error=invalid_credentials");
        exit();
    }
} finally {
    $stmt->close();
    $conn->close();
}
?>