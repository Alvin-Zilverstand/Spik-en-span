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
require_once __DIR__ . '/../fpdf/fpdf.php'; // Ensure FPDF is included only once

use PHPMailer\PHPMailer\PHPMailer;

// Prepare SQL statement
$sql = "INSERT INTO tickets (ticket_id, name, email, category, day, quantity, qr_code_link) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$qrCodes = []; // Array to store QR code URLs for PDF attachment

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

    $qrCodes[] = ['ticket_id' => $ticket_id, 'qr_code_url' => $qrCodeUrl]; // Store QR code details for PDF
}

$stmt->close();
$conn->close();

// Generate individual PDFs for each QR code
$pdfPaths = []; // Array to store paths of generated PDFs
foreach ($qrCodes as $index => $qrCode) {
    $pdf = new FPDF();
    $pdf->AddPage();

    // Add a header with background color
    $pdf->SetFillColor(40, 167, 69); // Green background
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->Cell(0, 15, 'Spik & Span - Ticket Bevestiging', 0, 1, 'C', true);
    $pdf->Ln(10);

    // Add customer details section with a light gray background
    $pdf->SetFillColor(240, 240, 240); // Light gray background
    $pdf->SetTextColor(0, 0, 0); // Black text
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Naam: ' . $name, 0, 1, 'L', true);
    $pdf->Cell(0, 10, 'E-mail: ' . $email, 0, 1, 'L', true);
    $pdf->Ln(10);

    // Add ticket details
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0); // Black text
    $pdf->Cell(0, 10, 'Ticket ' . ($index + 1) . ':', 0, 1, 'L');
    $pdf->Cell(0, 10, 'Ticket ID: ' . $qrCode['ticket_id'], 0, 1, 'L');
    $pdf->Ln(5);

    // Add QR code image centered
    $qrCodeImage = file_get_contents($qrCode['qr_code_url']);
    $qrCodePath = __DIR__ . "/temp_qr_$index.png";
    file_put_contents($qrCodePath, $qrCodeImage);
    $pdf->Cell(0, 0, '', 0, 1, 'C'); // Move to the center
    $pdf->Image($qrCodePath, ($pdf->GetPageWidth() - 50) / 2, $pdf->GetY(), 50, 50); // Center the QR code
    $pdf->Ln(60);

    // Clean up temporary QR code image
    unlink($qrCodePath);

    // Add a footer with a green background
    $pdf->SetY(-30);
    $pdf->SetFillColor(40, 167, 69); // Green background
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, 'Bedankt voor uw bestelling bij Spik & Span!', 0, 1, 'C', true);
    $pdf->Cell(0, 10, 'Voor vragen kunt u contact opnemen via onze website.', 0, 1, 'C', true);

    // Save PDF to a temporary file
    $pdfPath = __DIR__ . "/ticket_$index.pdf";
    $pdf->Output('F', $pdfPath);
    $pdfPaths[] = $pdfPath; // Store the path of the generated PDF
}

// Send confirmation email with all PDF attachments
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
    $mail->Body = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;">
        <h1 style="color: #28a745; text-align: center;">Bedankt voor je bestelling!</h1>
        <p style="font-size: 16px; color: #333;">Beste ' . htmlspecialchars($name) . ',</p>
        <p style="font-size: 16px; color: #333;">We hebben je bestelling succesvol ontvangen. Je tickets zijn bijgevoegd in de PDF-bestanden.</p>
        <p style="font-size: 16px; color: #333;">Met vriendelijke groet,<br><strong>Spik & Span</strong></p>
    </div>';

    // Attach each PDF to the email
    foreach ($pdfPaths as $pdfPath) {
        $mail->addAttachment($pdfPath);
    }

    $mail->send();

    // Clean up temporary PDF files
    foreach ($pdfPaths as $pdfPath) {
        unlink($pdfPath);
    }

    // Redirect to the order completion page
    header("Location: ../order-complete.html");
    exit();
} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>