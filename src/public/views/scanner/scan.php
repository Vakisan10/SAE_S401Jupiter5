<?php require_once __DIR__ . '/../../views/layout/header.php' ?? '' ?>

<div class="scanner-container">
    <h2>Scanner un colis</h2>
    <div class="camera-wrapper">
        <video id="camera" playsinline autoplay></video>
        <canvas id="canvas" hidden></canvas>
    </div>
    <div id="result"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script src="/assets/js/scanner.js"></script>
