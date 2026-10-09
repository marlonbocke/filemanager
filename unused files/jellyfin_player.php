<?php
// jellyfin_player.php
// Dieses Skript demonstriert, wie man ein Jellyfin-Video mit hls.js im Browser abspielt.

// Konfiguration
$jellyfin_url = "http://" . $_SERVER['SERVER_NAME'] . ":8096"; // URL zu deinem Jellyfin Server
$api_key = $_GET['api_key'] ?? ''; // API Key übergibst du per URL (?api_key=...)
$video_id = $_GET['id'] ?? '';     // Video ID von Jellyfin (?id=...)

if (!$api_key || !$video_id) {
    die("Bitte gib einen API-Key und eine Video-ID an. <br>Beispiel: ?id=DEINE_ID&api_key=DEIN_KEY");
}

// Stream URL generieren (HLS Stream)
// Jellyfin bietet verschiedene Stream-Typen. HLS ist sehr kompatibel.
$stream_url = "$jellyfin_url/Videos/$video_id/master.m3u8?api_key=$api_key&MediaSourceId=$video_id&VideoCodec=h264&AudioCodec=aac";

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jellyfin Player Demo</title>
    <!-- hls.js Bibliothek einbinden (für Support in Chrome, Firefox, etc.) -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <style>
        body { font-family: sans-serif; background: #1a1a1a; color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        video { width: 80%; max-width: 1000px; box-shadow: 0 0 20px rgba(0,0,0,0.5); }
        .info { margin-top: 20px; text-align: center; color: #aaa; }
    </style>
</head>
<body>

    <h2>Jellyfin Stream Player</h2>

    <video id="video" controls></video>

    <div class="info">
        <p>Stream URL: <?php echo htmlspecialchars($stream_url); ?></p>
        <p>Hinweis: Falls das Video nicht lädt, prüfe die CORS-Einstellungen in Jellyfin oder Browser-Konsole.</p>
    </div>

    <script>
        var video = document.getElementById('video');
        var videoSrc = '<?php echo $stream_url; ?>';

        if (Hls.isSupported()) {
            var hls = new Hls();
            hls.loadSource(videoSrc);
            hls.attachMedia(video);
            hls.on(Hls.Events.MANIFEST_PARSED, function() {
                video.play();
            });
        }
        else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            // Native HLS Unterstützung (Safari)
            video.src = videoSrc;
            video.addEventListener('loadedmetadata', function() {
                video.play();
            });
        }
    </script>
</body>
</html>
