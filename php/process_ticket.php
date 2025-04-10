<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "database12";
$password = "181t$1lJg";
$dbname = "spik_en_span";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['name'];
$email = $_POST['email'];
$category = $_POST['category'];
$quantity = (int)$_POST['quantity'];

list($categoryType, $day) = explode(' ', $category);
$day = strtolower($day) === 'vrijdag' ? 'friday' : 'saturday';
$categoryType = strtolower($categoryType) === 'volwassenen' ? 'volwassen' : 'kind';

require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';
require __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../fpdf/fpdf.php';

use PHPMailer\PHPMailer\PHPMailer;

$sql = "INSERT INTO tickets (ticket_id, name, email, category, day, quantity, qr_code_link) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$qrCodes = [];

for ($i = 0; $i < $quantity; $i++) {
    $ticket_id = bin2hex(random_bytes(16));
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" . urlencode($ticket_id);
    $one_ticket_quantity = 1;
    $stmt->bind_param("sssssis", $ticket_id, $name, $email, $categoryType, $day, $one_ticket_quantity, $qrCodeUrl);
    if (!$stmt->execute()) {
        echo "Error: " . $stmt->error;
        $stmt->close();
        $conn->close();
        exit();
    }

    $qrCodes[] = ['ticket_id' => $ticket_id, 'qr_code_url' => $qrCodeUrl];
}

$stmt->close();
$conn->close();

$pdfPaths = [];
foreach ($qrCodes as $index => $qrCode) {
    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFillColor(40, 167, 69);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->Cell(0, 20, 'Spik & Span - Ticket Bevestiging', 0, 1, 'C', true);
    $pdf->Ln(10);

    $pdf->SetFillColor(230, 230, 250);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, 'Naam: ' . $name, 0, 1, 'L', true);
    $pdf->Cell(0, 10, 'E-mail: ' . $email, 0, 1, 'L', true);
    $pdf->Ln(10);

    $pdf->SetFillColor(240, 255, 240);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, 'Ticket ' . ($index + 1) . ':', 0, 1, 'L', true);
    $pdf->Cell(0, 10, 'Ticket ID: ' . $qrCode['ticket_id'], 0, 1, 'L', true);
    $pdf->Ln(5);

    $qrCodeImage = file_get_contents($qrCode['qr_code_url']);
    $qrCodePath = __DIR__ . "/temp_qr_$index.png";
    file_put_contents($qrCodePath, $qrCodeImage);
    $pdf->Cell(0, 0, '', 0, 1, 'C');
    $pdf->Image($qrCodePath, ($pdf->GetPageWidth() - 80) / 2, $pdf->GetY(), 80, 80);
    $pdf->Ln(90);

    unlink($qrCodePath);

    $pdf->SetY(-50);
    $pdf->SetFillColor(40, 167, 69);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell(0, 10, 'Bedankt voor uw bestelling bij Spik & Span!', 0, 1, 'C', true);
    $pdf->Cell(0, 10, 'Voor vragen kunt u contact opnemen via onze website.', 0, 1, 'C', true);

    $pdfPath = __DIR__ . "/ticket_$index.pdf";
    $pdf->Output('F', $pdfPath);
    $pdfPaths[] = $pdfPath;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ticketsopdracht@gmail.com';
    $mail->Password = 'rqxm fbju xbmu qmbr';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('ticketsopdracht@gmail.com', 'Spik & Span');
    $mail->addAddress($email, $name);

    $mail->isHTML(true);
    $mail->Subject = 'Bevestiging van je bestelling';
    $mail->Body = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;">
        <h1 style="color: #28a745; text-align: center;">Bedankt voor je bestelling!</h1>
        <p style="font-size: 16px; color: #333;">Beste ' . htmlspecialchars($name) . ',</p>
        <p style="font-size: 16px; color: #333;">We hebben je bestelling succesvol ontvangen. Je tickets zijn bijgevoegd in de PDF-bestanden.</p>
        <p style="font-size: 16px; color: #333;">Met vriendelijke groet,<br><strong>Spik & Span</strong></p>
    </div>';

    foreach ($pdfPaths as $pdfPath) {
        $mail->addAttachment($pdfPath);
    }

    $mail->send();

    foreach ($pdfPaths as $pdfPath) {
        unlink($pdfPath);
    }

    header("Location: ../order-complete.html");
    exit();
} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>