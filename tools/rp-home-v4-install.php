<?php
// Raisina Post homepage-v4 installer. Idempotent and limited to the homepage.
declare(strict_types=1);
$root = '/home/u757195205/domains/theraisinapost.com/public_html';
$base = 'https://raw.githubusercontent.com/theraisinapost/astro-blog/main/tools/';
$version = '20261118b';
$files = ['rp-home-v4.js' => 12000, 'rp-home-v4.css' => 7500];
function remoteAsset(string $url): string {
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_TIMEOUT => 25, CURLOPT_FAILONERROR => true]);
        $data = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (is_string($data) && $status === 200) return $data;
    }
    $data = @file_get_contents($url);
    if (is_string($data)) return $data;
    throw new RuntimeException('Asset request failed: '.basename($url));
}
if (!is_dir($root) || !is_file($root.'/index.html')) throw new RuntimeException('Homepage missing');
$assets = [];
foreach ($files as $name => $min) {
    $data = remoteAsset($base.$name.'?v='.$version);
    if (strlen($data) < $min) throw new RuntimeException('Invalid asset: '.$name);
    $assets[$name] = $data;
}
$index = $root.'/index.html';
$before = file_get_contents($index);
if (!is_string($before) || strpos($before, '</head>') === false) throw new RuntimeException('Unexpected homepage HTML');
foreach ($assets as $name => $data) {
    $temp = $root.'/'.$name.'.tmp';
    if (file_put_contents($temp, $data, LOCK_EX) !== strlen($data) || !rename($temp, $root.'/'.$name))
        throw new RuntimeException('Asset write failed: '.$name);
}
$back = $root.'/index.html.pre-rp-home-v4-20261118.bak';
if (!is_file($back) && !copy($index, $back)) throw new RuntimeException('Cannot back up homepage');
if (strpos($before, 'rp-home-v4.js') === false) {
    $tags = "\n<link rel=\"stylesheet\" href=\"/rp-home-v4.css?v=$version\" data-rp-home-v4>\n".
            "<script defer src=\"/rp-home-v4.js?v=$version\" data-rp-home-v4></script>\n";
    $after = str_replace('</head>', $tags.'</head>', $before);
    $temp = $root.'/index.html.homev4.tmp';
    if (file_put_contents($temp, $after, LOCK_EX) !== strlen($after) || !rename($temp, $index))
        throw new RuntimeException('Homepage integration failed');
    echo 'HOMEPAGE_UPDATED ';
} else {
    echo 'HOMEPAGE_ALREADY_LINKED ';
}
echo 'JS='.strlen($assets['rp-home-v4.js']).' CSS='.strlen($assets['rp-home-v4.css']).' BACKUP='.basename($back)."\n";
