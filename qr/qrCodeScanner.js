const btnScanQR = document.getElementById('btn-scan-qr');
const qrCanvas = document.getElementById('qr-canvas');
const qrResult = document.getElementById('qr-result');
const outputData = document.getElementById('outputData');
const video = document.getElementById('video');
const scanAgainButton = document.createElement('button');

scanAgainButton.textContent = 'Scan Nog Een Ticket';
scanAgainButton.className = 'btn btn-primary mt-3';
scanAgainButton.style.display = 'none';
scanAgainButton.addEventListener('click', () => {
    location.reload();
});
document.body.appendChild(scanAgainButton);

btnScanQR.addEventListener('click', () => {
    btnScanQR.hidden = true;
    video.style.display = 'block';
    qrCanvas.hidden = true;
    qrResult.hidden = true;

    const context = qrCanvas.getContext('2d');
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then((stream) => {
            video.srcObject = stream;
            video.setAttribute('playsinline', true);
            video.play();

            const scan = () => {
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    qrCanvas.height = video.videoHeight;
                    qrCanvas.width = video.videoWidth;
                    context.drawImage(video, 0, 0, qrCanvas.width, qrCanvas.height);

                    try {
                        const qrCodeData = qrcode.decode();
                        fetch(`../php/get_ticket_details.php?ticket_id=${encodeURIComponent(qrCodeData)}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    document.body.style.backgroundColor = 'green';
                                    outputData.innerHTML = `
                                        <p><strong>Ticket ID:</strong> ${data.ticket_id}</p>
                                        <p><strong>Dag:</strong> ${data.day === 'friday' ? 'Vrijdag' : 'Zaterdag'}</p>
                                        <p><strong>Categorie:</strong> ${data.category === 'volwassen' ? 'Volwassene' : 'Kind'}</p>
                                        <p style="color: green;">Ticket succesvol gescand en opgeslagen!</p>
                                    `;
                                } else {
                                    document.body.style.backgroundColor = 'red';
                                    outputData.innerHTML = `<p style="color: red;">${data.message}</p>`;
                                }
                                setTimeout(() => {
                                    document.body.style.backgroundColor = '';
                                }, 2000);
                                qrResult.hidden = false;
                                qrCanvas.hidden = true;
                                video.hidden = true;
                                scanAgainButton.style.display = 'block';
                                stream.getTracks().forEach(track => track.stop());
                            })
                            .catch(err => {
                                console.error('Error fetching ticket details:', err);
                                document.body.style.backgroundColor = 'red';
                                outputData.innerHTML = `<p style="color: red;">Fout bij het ophalen van ticketgegevens.</p>`;
                                setTimeout(() => {
                                    document.body.style.backgroundColor = '';
                                }, 2000);
                                qrResult.hidden = false;
                                qrCanvas.hidden = true;
                                video.hidden = true;
                                scanAgainButton.style.display = 'block';
                                stream.getTracks().forEach(track => track.stop());
                            });
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
