<!-- filepath: c:\xampp\htdocs\Spik-en-span\process_ticket.php -->
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
$name = $_POST['name'];
$email = $_POST['email'];
$category = $_POST['category'];
$quantity = $_POST['quantity'];
$ticket_id = "TICKET-" . time();

// Insert ticket into database
$sql = "INSERT INTO tickets (ticket_id, name, email, category, quantity) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $ticket_id, $name, $email, $category, $quantity);

if ($stmt->execute()) {
    echo "Ticket successfully purchased! Your Ticket ID is: " . $ticket_id;
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>