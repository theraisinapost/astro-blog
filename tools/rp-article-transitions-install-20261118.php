<?php
declare(strict_types=1);
/* CLI-only. Patch eligible canonical article HTML, never editorial body. */
if (PHP_SAPI !== 'cli') {http_response_code(404);exit(1);}

function rp_transition_article_html(string $html): string {
    if (strlen($html)<600 || !str_contains($html,'</head>') ||
        !preg_match('~<main\b[^>]*class="[^"]*\bra-article\b~i',$html)) return $html;
    foreach ([
        ['link','href','rp-article-photo-transition.css',
            '<link rel="stylesheet" href="/rp-article-photo-transition.css?v=20261118r">'],
        ['script','src','rp-article-photo-transition.js',
            '<script src="/rp-article-photo-transition.js?v=20261118r"></script>']
    ] as [$tag,$attribute,$name,$link]) {
        $pattern='~(<'.$tag.'\b[^>]*'.$attribute.'="/'.preg_quote($name,'~').
            ')(?:\?[^"]*)?("[^>]*>)~i';
        $updated=preg_replace($pattern,'$1?v=20261118r$2',$html);
        if (!is_string($updated)) throw new RuntimeException('Bad asset rewrite');
        $html=$updated;
        if (!str_contains($html,$attribute.'="/'.$name)) {
            $html=str_replace('</head>',$link."\n</head>",$html);
        }
    }
    return $html;
}

if (in_array('--self-test',$argv,true)) {
    $fixture='<!doctype html><html><head></head><body><main class="ra-article"><article>'.
        str_repeat('<p>Source text</p>',50).'</article></main></body></html>';
    $once=rp_transition_article_html($fixture);
    $twice=rp_transition_article_html($once);
    $plain='<html><head></head><body><main class="home">Home</main></body></html>';
    if ($once!==$twice ||
        substr_count($once,'href="/rp-article-photo-transition.css')!==1 ||
        substr_count($once,'src="/rp-article-photo-transition.js')!==1 ||
        rp_transition_article_html($plain)!==$plain) {
        fwrite(STDERR,"FAIL: article installer self-test\n");exit(1);
    }
    echo "PASS: article installer is scoped and idempotent\n";exit(0);
}

$private='/home/u757195205';
$root=$private.'/domains/theraisinapost.com/public_html';
$lock=fopen($private.'/rp-article-transitions-install.lock','c');
if (!$lock || !flock($lock,LOCK_EX|LOCK_NB)) exit(0);
try {
    if (!is_file($root.'/rp-article-photo-transition.css') ||
        !is_file($root.'/rp-article-photo-transition.js')) {
        throw new RuntimeException('Awaiting shared assets');
    }
    $scanned=0;$patched=0;$errors=0;
    foreach (['business','governance','in-depth','opinion'] as $desk) {
        foreach (glob($root.'/'.$desk.'/*/index.html') ?: [] as $path) {
            if (!is_file($path) || is_link($path)) continue;
            $size=filesize($path);
            if (!is_int($size) || $size<12000 || $size>2500000) continue;
            $scanned++;
            $original=file_get_contents($path);
            if (!is_string($original)) {$errors++;continue;}
            $updated=rp_transition_article_html($original);
            if ($original===$updated) continue;
            $tmp=$path.'.rp-transition.tmp';
            if (file_put_contents($tmp,$updated,LOCK_EX)!==strlen($updated) ||
                !rename($tmp,$path)) {
                @unlink($tmp);$errors++;continue;
            }
            $patched++;
        }
    }
    echo "ARTICLE_TRANSITIONS scanned=$scanned patched=$patched errors=$errors\n";
    if ($errors) exit(1);
} catch(Throwable $e) {
    fwrite(STDERR,'ARTICLE_TRANSITION_ERROR: '.$e->getMessage()."\n");exit(1);
}
