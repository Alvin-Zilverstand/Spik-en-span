<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">QR Code Scanner</h1>
        <div id="qr-reader" class="mt-4"></div>
        <div id="qr-reader-results" class="mt-3 text-center"></div>
        <a href="logout.php" class="btn btn-danger mt-4">Logout</a>
    </div>

    <script>
        const qrReaderResults = document.getElementById('qr-reader-results');

        function onScanSuccess(decodedText, decodedResult) {
            // Display the scanned QR code content
            qrReaderResults.innerHTML = `<p class="text-success">Scanned Content: ${decodedText}</p>`;

            // Send the scanned ticket ID to the server for validation
            fetch('validate_ticket.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ticket_id: decodedText })
            })
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    qrReaderResults.innerHTML += `<p class="text-success">Ticket is valid!</p>`;
                } else {
                    qrReaderResults.innerHTML += `<p class="text-danger">Invalid or already scanned ticket.</p>`;
                }
            })
            .catch(error => {
                qrReaderResults.innerHTML += `<p class="text-danger">Error validating ticket.</p>`;
            });
        }

        function onScanError(errorMessage) {
            console.warn(`QR Code scan error: ${errorMessage}`);
        }

        // Initialize the QR Code scanner
        const html5QrCode = new Html5Qrcode("qr-reader");
        html5QrCode.start(
            { facingMode: "environment" }, // Use the back camera
            {
                fps: 10, // Frames per second
                qrbox: 250 // QR code scanning box size
            },
            onScanSuccess,
            onScanError
        ).catch(err => {
            qrReaderResults.innerHTML = `<p class="text-danger">Unable to start QR scanner: ${err}</p>`;
        });
    </script>
</body>
</html>