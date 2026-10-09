<?php
// Jellyfin integration for video streaming

$JELLYFIN_API_KEY = '3b92c16a01454847b36bd98e9477463e';
$JELLYFIN_URL = 'https://bockelmanncloud.ddns.net/jellyfin';

function getJellyfinItemId($filename) {
    global $JELLYFIN_API_KEY;
    
    $name = basename($filename);
    $nameWithoutExt = pathinfo($name, PATHINFO_FILENAME);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:8096/Items?api_key=$JELLYFIN_API_KEY&IncludeItemTypes=Video&Recursive=true");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['Items'])) return null;
    
    foreach ($data['Items'] as $item) {
        if (isset($item['Name'])) {
            $itemNameWithoutExt = pathinfo($item['Name'], PATHINFO_FILENAME);
            if ($itemNameWithoutExt === $nameWithoutExt) {
                return $item['Id'];
            }
        }
    }
    return null;
}

function getJellyfinStreamUrl($filename) {
    global $JELLYFIN_API_KEY, $JELLYFIN_URL;
    
    $itemId = getJellyfinItemId($filename);
    if (!$itemId) return null;
    
    // Direct HLS streaming URL through Apache proxy
    return "$JELLYFIN_URL/Videos/$itemId/master.m3u8?api_key=$JELLYFIN_API_KEY&MediaSourceId=$itemId&VideoCodec=h264&AudioCodec=aac";
}

function isJellyfinAvailable() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:8096/System/Info");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode === 200 || $httpCode === 401;
}
?>