document.getElementById('ticketForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const category = document.getElementById('ticketCategory').value;
    const quantity = document.getElementById('ticketQuantity').value;

    // Generate a unique ticket ID (for simplicity, using timestamp)
    const ticketId = `TICKET-${Date.now()}`;

    // Generate QR code content
    const qrContent = `Name: ${name}, Email: ${email}, Category: ${category}, Quantity: ${quantity}, Ticket ID: ${ticketId}`;

    // Display QR code (using a library like QRCode.js)
    const qrCodeContainer = document.getElementById('qrCodeContainer');
    qrCodeContainer.innerHTML = ''; // Clear previous QR code
    const qrCode = new QRCode(qrCodeContainer, {
        text: qrContent,
        width: 200,
        height: 200,
    });

    alert('Ticket(s) successfully generated!');
});
