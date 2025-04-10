const btnScanQR = document.getElementById('btn-scan-qr');
const qrCanvas = document.getElementById('qr-canvas');
const qrResult = document.getElementById('qr-result');
const outputData = document.getElementById('outputData');
const video = document.getElementById('video');

btnScanQR.addEventListener('click', () => {
    btnScanQR.hidden = true; // Hide the QR code icon
    video.hidden = false; // Show the video element
    qrCanvas.hidden = false;
    qrResult.hidden = true;

    const context = qrCanvas.getContext('2d');
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then((stream) => {
            video.srcObject = stream;
            video.setAttribute('playsinline', true); // Required to work on iOS
            video.play();

            const scan = () => {
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    qrCanvas.height = video.videoHeight;
                    qrCanvas.width = video.videoWidth;
                    context.drawImage(video, 0, 0, qrCanvas.width, qrCanvas.height);

                    try {
                        const qrCodeData = qrcode.decode();
                        outputData.innerText = qrCodeData;
                        qrResult.hidden = false;
                        qrCanvas.hidden = true;
                        video.hidden = true; // Hide the video element after scanning
                        stream.getTracks().forEach(track => track.stop());
                    } catch (e) {
                        requestAnimationFrame(scan);
                    }
                } else {
                    requestAnimationFrame(scan);
                }
            };
            scan();
        })
        .catch((err) => {
            console.error('Error accessing camera:', err);
        });
});
