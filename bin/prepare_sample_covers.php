<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit(1); }
$books = require dirname(__DIR__) . '/docs/sample_books.php';
$folder = dirname(__DIR__) . '/private/covers';
if (!is_dir($folder)) { mkdir($folder, 0700, true); }
foreach ($books as [$id, $title]) {
    $path = $folder . '/gutenberg-' . $id . '.jpg';
    if (is_file($path)) { continue; }
    $url = "https://www.gutenberg.org/cache/epub/$id/pg$id.cover.medium.jpg";
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_USERAGENT => 'KaAklat local demo catalog preparation', CURLOPT_PROTOCOLS => CURLPROTO_HTTPS]);
    $bytes = is_file($folder . '/raw-' . $id . '.jpg') ? file_get_contents($folder . '/raw-' . $id . '.jpg') : curl_exec($curl);
    $status = is_file($folder . '/raw-' . $id . '.jpg') ? 200 : curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($status !== 200 || !is_string($bytes) || strlen($bytes) > 2097152) { throw new RuntimeException("Cover download failed for $title ($status)"); }
    $size = getimagesizefromstring($bytes);
    if (!$size || $size[0] > 4000 || $size[1] > 4000 || !in_array($size[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { throw new RuntimeException('Invalid image'); }
    $image = imagecreatefromstring($bytes);
    if (!$image || !imagejpeg($image, $path, 90)) { throw new RuntimeException('Cover decoding failed'); }
    echo "Prepared $title\n";
}
echo "All 20 covers prepared privately.\n";
