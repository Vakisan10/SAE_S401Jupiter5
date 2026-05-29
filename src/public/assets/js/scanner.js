const video  = document.getElementById('camera');
const canvas = document.getElementById('canvas');
const ctx    = canvas.getContext('2d');
const result = document.getElementById('result');
let scanning = true;

navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
    .then(stream => {
        video.srcObject = stream;
        video.play();
        requestAnimationFrame(scan);
    })
    .catch(() => {
        result.innerHTML = '<p class="error">Accès caméra refusé.</p>';
    });

function scan() {
    if (!scanning) return;
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height);
        if (code) {
            scanning = false;
            envoyerTracking(code.data);
            return;
        }
    }
    requestAnimationFrame(scan);
}

function envoyerTracking(tracking) {
    result.innerHTML = '<p>Recherche en cours...</p>';
    fetch('/scanner/process', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ qr_content: tracking })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            afficherColis(data.colis);
        } else {
            result.innerHTML = `<p class="error">${data.message}</p>`;
            setTimeout(() => { scanning = true; requestAnimationFrame(scan); }, 3000);
        }
    });
}

function afficherColis(colis) {
    result.innerHTML = `
        <div class="colis-card">
            <h3>Colis trouvé</h3>
            <p><strong>Numéro suivi :</strong> ${colis.numero_suivi}</p>
            <p><strong>Statut :</strong> ${colis.statut_id}</p>
            <p><strong>Date réception :</strong> ${colis.date_reception ?? '-'}</p>
            <p><strong>Commentaire :</strong> ${colis.commentaire ?? '-'}</p>
            <button onclick="resetScanner()">Scanner un autre</button>
        </div>
    `;
}

function resetScanner() {
    result.innerHTML = '';
    scanning = true;
    requestAnimationFrame(scan);
}
