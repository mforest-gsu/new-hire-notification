<?php

declare(strict_types=1);

$logPath = '/data/www/new-hire-notifications/tracker.log';
$pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
$tracker = $_GET['t'] ?? null;

$fp = (is_string($tracker) && preg_match($pattern, $tracker) === 1)
    ? fopen($logPath, "a")
    : false;

if (is_resource($fp)) {
    if (flock($fp, LOCK_EX)) {
        fputcsv($fp, [
            $tracker,
            (new \DateTime("now", new \DateTimeZone("America/New_York")))->format('Y-m-d H:i:s')
        ]);
        fflush($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
}

header("Content-Type: image/png");
header("Cache-Control: max-age=0, no-cache, no-store, private");
echo file_get_contents('pixel.png');
