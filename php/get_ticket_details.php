<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "database12"; // Updated username
$password = "181t$1lJg"; // Updated password
$dbname = "spik_en_span";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Databaseverbinding mislukt.']);
    exit();
}

// Retrieve ticket_id from the query string
$ticket_id = isset($_GET['ticket_id']) ? trim($_GET['ticket_id']) : '';

if (empty($ticket_id)) {
    echo json_encode(['success' => false, 'message' => 'Geen ticket ID opgegeven.']);
    exit();
}

// Query the database for ticket details
$sql = "SELECT ticket_id, category, day FROM tickets WHERE ticket_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $ticket = $result->fetch_assoc();
    echo json_encode(['success' => true, 'ticket_id' => $ticket['ticket_id'], 'category' => $ticket['category'], 'day' => $ticket['day']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ticket niet gevonden.']);
}

$stmt->close();
$conn->close();
?>
