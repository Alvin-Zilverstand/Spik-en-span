// Check if the ticket form exists before adding an event listener
const ticketForm = document.getElementById('ticketForm');
if (ticketForm) {
    ticketForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const category = document.getElementById('ticketCategory').value;
        const quantity = document.getElementById('ticketQuantity').value;

        const ticketId = `TICKET-${Date.now()}`;

        const qrContent = `Name: ${name}, Email: ${email}, Category: ${category}, Quantity: ${quantity}, Ticket ID: ${ticketId}`;

        const qrCodeContainer = document.getElementById('qrCodeContainer');
        qrCodeContainer.innerHTML = '';
        const qrCode = new QRCode(qrCodeContainer, {
            text: qrContent,
            width: 200,
            height: 200,
        });

        alert('Ticket(s) successfully generated!');
    });
}

// Function to toggle video playback
function toggleVideo() {
    const video = document.getElementById('myVideo');
    if (video) {
        if (video.paused) {
            video.play();
        } else {
            video.pause();
        }
    }
}

document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    if (username === 'admin' && password === 'password') {
        alert('Succesvol ingelogd!');
        window.location.href = './qr/qr.html';
    } else {
        alert('Ongeldige inloggegevens.');
    }
});