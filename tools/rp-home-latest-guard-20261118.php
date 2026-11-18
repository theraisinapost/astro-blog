<?php
declare(strict_types=1);
/* The Raisina Post — CLI-only post-deployment homepage feature guard.
   Hostinger managed builds can replace public_html/index.html. This guard
   restores only the previously approved Games styles and newest-first loader.
   It does not change the lead story, Power Brief, or published editorial data. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(1); }

function rp_latest_transform(string $html): string {
    if (strlen($html) < 500 || !str_contains($html, 'id="home-24h-grid"') ||
        !str_contains($html, '</head>')) return $html;
    $updated = preg_replace(
        '~(<script\b[^>]*src="/home-latest-auto\.js)(?:\?[^"]*)?("[^>]*></script>)~i',
        '$1?v=20261118d$2', $html
    );
    if (!is_string($updated)) throw new RuntimeException('Cannot normalise script URL');
    if (!str_contains($updated, 'src="/home-latest-auto.js')) {
        $updated=str_replace('</head>', '<script defer src="/home-latest-auto.js?v=20261118d"></script>' . "\n</head>", $updated);
    }
    if (str_contains($updated, 'id="rp-home-daily-games"')) {
        $updated=preg_replace(
            '~(<link\b[^>]*href="/rp-home-daily-games\.css)(?:\?[^"]*)?("[^>]*>)~i',
            '$1?v=20261118d$2', $updated
        );
        if (!is_string($updated)) throw new RuntimeException('Cannot normalise Games style URL');
        if (!str_contains($updated,'href="/rp-home-daily-games.css')) {
            $updated=str_replace('</head>', '<link rel="stylesheet" href="/rp-home-daily-games.css?v=20261118d">' . "\n</head>", $updated);
        }
    }
    return $updated;
}
if (in_array('--self-test', $argv, true)) {
    $html='<html><head></head><body><aside><div id="home-24h-grid"></div><section id="rp-home-daily-games"></section></aside></body></html>';
    $html=str_replace('</body>',str_repeat('x',600).'</body>',$html);
    $once=rp_latest_transform($html);
    $twice=rp_latest_transform($once);
    if ($once!==$twice || substr_count($twice,'src="/home-latest-auto.js')!==1 ||
        substr_count($twice,'href="/rp-home-daily-games.css')!==1 ||
        rp_latest_transform('<html><head></head><body>not homepage</body></html>')!==
            '<html><head></head><body>not homepage</body></html>') {
        fwrite(STDERR, "FAIL: idempotent homepage guard\n"); exit(1);
    }
    echo "PASS: guard is idempotent and homepage-scoped\n";
    exit(0);
}
$private='/home/u757195205';
$root=$private.'/domains/theraisinapost.com/public_html';
$lock=fopen($private.'/rp-home-latest-guard.lock','c');
if (!$lock || !flock($lock,LOCK_EX|LOCK_NB)) exit(0);
try {
    $scriptSource=$private.'/home-latest-auto-master.js';
    $styleSource=$private.'/rp-home-daily-games-master.css';
    $index=$root.'/index.html';
    if (!is_file($index) || filesize($index)<25000) throw new RuntimeException('Unexpected missing/short homepage');
    $filePairs=[[$scriptSource,$root.'/home-latest-auto.js',6000],[$styleSource,$root.'/rp-home-daily-games.css',2500]];
    foreach ($filePairs as [$source,$target,$minSize]) {
        if (!is_file($source) || filesize($source)<$minSize) throw new RuntimeException('Missing approved source '.basename($source));
        if (is_file($target) && sha1_file($target)===sha1_file($source)) continue;
        $bytes=file_get_contents($source);
        if (!is_string($bytes)) throw new RuntimeException('Could not read '.basename($source));
        $tmp=$target.'.rp-guard.tmp';
        if (file_put_contents($tmp,$bytes,LOCK_EX)!==strlen($bytes) || !rename($tmp,$target))
            throw new RuntimeException('Could not restore '.basename($target));
    }
    $before=file_get_contents($index);
    if (!is_string($before)) throw new RuntimeException('Could not read homepage');
    $after=rp_latest_transform($before);
    if ($before!==$after) {
        $backup=$private.'/raisina-home-guard-prechange-20261118.html';
        if (!is_file($backup) && !copy($index,$backup)) throw new RuntimeException('Cannot back up homepage');
        $tmp=$index.'.rp-home-guard.tmp';
        if (file_put_contents($tmp,$after,LOCK_EX)!==strlen($after) || !rename($tmp,$index))
            throw new RuntimeException('Cannot update homepage');
        echo "HOMEPAGE_GUARDED script=on games=".(str_contains($after,'id="rp-home-daily-games"')?'on':'missing')."\n";
    } else {
        echo "OK: homepage feature loaders already current\n";
    }
} catch(Throwable $error) {
    fwrite(STDERR,"GUARD_ERROR: ".$error->getMessage()."\n");exit(1);
}