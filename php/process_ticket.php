<!-- filepath: c:\xampp\htdocs\Spik-en-span\process_ticket.php -->
<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "database12"; // Updated username
$password = "181t$1lJg"; // Updated password
$dbname = "spik_en_span";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve form data
$name = $_POST['name'];
$email = $_POST['email'];
$category = $_POST['category']; // e.g., 'Volwassenen Vrijdag' or 'Kinderen Zaterdag'
$quantity = (int)$_POST['quantity'];

// Extract day and category from the input
list($categoryType, $day) = explode(' ', $category); // Split into 'Volwassenen'/'Kinderen' and 'Vrijdag'/'Zaterdag'
$day = strtolower($day) === 'vrijdag' ? 'friday' : 'saturday';
$categoryType = strtolower($categoryType) === 'volwassenen' ? 'volwassen' : 'kind';

require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';
require __DIR__ . '/../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Prepare SQL statement
$sql = "INSERT INTO tickets (ticket_id, name, email, category, day, quantity, qr_code_link) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$qrCodes = []; // Array to store QR code URLs for email attachment

// Insert one ticket per row and generate QR codes
for ($i = 0; $i < $quantity; $i++) {
    $ticket_id = bin2hex(random_bytes(16)); // Generate a 32-character unique ticket ID
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" . urlencode($ticket_id); // Generate QR code link
    $one_ticket_quantity = 1; // Each row represents one ticket
    $stmt->bind_param("sssssis", $ticket_id, $name, $email, $categoryType, $day, $one_ticket_quantity, $qrCodeUrl);
    if (!$stmt->execute()) {
        echo "Error: " . $stmt->error;
        $stmt->close();
        $conn->close();
        exit();
    }

    $qrCodes[] = $qrCodeUrl; // Store QR code URL for email
}

$stmt->close();
$conn->close();

// Send confirmation email with QR codes
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'ticketsopdracht@gmail.com'; // Your Gmail address
    $mail->Password = 'rqxm fbju xbmu qmbr'; // Your App Password from Google
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS encryption
    $mail->Port = 587; // TLS port

    $mail->setFrom('ticketsopdracht@gmail.com', 'Spik & Span'); // Sender email and name
    $mail->addAddress($email, $name); // Recipient email and name

    $mail->isHTML(true);
    $mail->Subject = 'Bevestiging van je bestelling';

    // Build the email body with inline styles
    $mail->Body = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;">
        <h1 style="color: #28a745; text-align: center;">Bedankt voor je bestelling!</h1>
        <p style="font-size: 16px; color: #333;">Beste ' . htmlspecialchars($name) . ',</p>
        <p style="font-size: 16px; color: #333;">We hebben je bestelling succesvol ontvangen. Hieronder vind je de details van je bestelling:</p>
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>Naam:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($name) . '</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>E-mail:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($email) . '</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>Categorie:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($category) . '</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>Aantal Tickets:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">' . $quantity . '</td>
            </tr>
        </table>
        <p style="font-size: 16px; color: #333;">Hieronder vind je de QR-codes voor je tickets:</p>
        <div style="text-align: center;">';

    // Add QR codes to the email body
    foreach ($qrCodes as $index => $qrCodeUrl) {
        $mail->Body .= '
            <div style="margin-bottom: 20px;">
                <p style="font-size: 16px; color: #333;"><strong>Ticket ' . ($index + 1) . ':</strong></p>
                <img src="' . $qrCodeUrl . '" alt="QR Code" style="width: 150px; height: 150px; border: 1px solid #ddd; border-radius: 10px;">
            </div>';
    }

    $mail->Body .= '
        </div>
        <p style="font-size: 16px; color: #333;">We wensen je veel plezier tijdens het evenement!</p>
        <p style="font-size: 16px; color: #333;">Met vriendelijke groet,<br><strong>Spik & Span</strong></p>
    </div>';

    $mail->send();

    // Redirect to the order completion page
    header("Location: ../order-complete.html");
    exit();
} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>