<?php
/** The Raisina Post: one-section homepage Games of the Day installer. */
declare(strict_types=1);
/* Restrict the homepage selection to two originals, without changing /games/. */
function rp_two_games_only(string $html): string {
    $pattern='~\s*<a class="rp-home-daily-game" href="/games/(?:daily-quiz|mini-crossword)/"[^>]*>.*?</a>~s';
    $after=preg_replace($pattern,'',$html);
    if(!is_string($after))throw new RuntimeException('Game-card pruning failed');
    if(strpos($after,'id="rp-home-daily-games"')!==false) {
        if(!preg_match('~<section[^>]*id="rp-home-daily-games"[^>]*>(.*?)</section>~s',$after,$m))
            throw new RuntimeException('Games panel structure is missing');
        preg_match_all('~<a class="rp-home-daily-game(?: rp-home-daily-game--featured)?" href="/games/[^"]+/"~',$m[1],$links);
        if(count($links[0])!==2 ||
           substr_count($m[1],'href="/game-icons.svg#sudoku"')!==1 ||
           substr_count($m[1],'href="/game-icons.svg#word-riddle"')!==1 ||
           !str_contains($m[1],'href="/games/sudoku/"') ||
           !str_contains($m[1],'href="/games/word-riddle/"'))
            throw new RuntimeException('Expected exactly two original-logo Games cards');
    }
    return $after;
}
if(in_array('--self-test',$argv,true)){
    $four='<section id="rp-home-daily-games"><div class="rp-home-daily-games-list">'
      .'<a class="rp-home-daily-game rp-home-daily-game--featured" href="/games/sudoku/"><svg><use href="/game-icons.svg#sudoku"></use></svg></a>'
      .'<a class="rp-home-daily-game" href="/games/word-riddle/"><svg><use href="/game-icons.svg#word-riddle"></use></svg></a>'
      .'<a class="rp-home-daily-game" href="/games/daily-quiz/"><svg><use href="/game-icons.svg#daily-quiz"></use></svg></a>'
      .'<a class="rp-home-daily-game" href="/games/mini-crossword/"><svg><use href="/game-icons.svg#mini-crossword"></use></svg></a>'
      .'</div></section>';
    $two=rp_two_games_only($four);
    $valid=$two===rp_two_games_only($two)
      && str_contains($two,'/games/sudoku/')
      && str_contains($two,'/games/word-riddle/')
      && !str_contains($two,'/games/daily-quiz/')
      && !str_contains($two,'/games/mini-crossword/')
      && str_contains($two,'/game-icons.svg#sudoku')
      && str_contains($two,'/game-icons.svg#word-riddle')
      && rp_two_games_only('unrelated content')==='unrelated content';
    if(!$valid){fwrite(STDERR,"FAIL: two-original-icons test\n");exit(1);}
    echo "PASS: exactly two original-logo Games cards; idempotent\n";exit(0);
}
$root='/home/u757195205/domains/theraisinapost.com/public_html';
$index=$root.'/index.html';
$backup='/home/u757195205/raisina-index-before-daily-games-20261118.html';
$stylesheet=$root.'/rp-home-daily-games.css';
$marker='id="rp-home-daily-games"';
if(!is_file($index))throw new RuntimeException('Homepage not found');
$before=file_get_contents($index);
if(!is_string($before))throw new RuntimeException('Unable to read homepage');
if(strpos($before,$marker)!==false){
    $after=rp_two_games_only($before);
    if($after===$before){echo "TWO_GAMES_ALREADY_CURRENT\n";exit(0);}
    $twoBackup='/home/u757195205/raisina-games-before-two-20261118.html';
    if(!is_file($twoBackup)&&!copy($index,$twoBackup))throw new RuntimeException('Two-games backup failed');
    $tmp=$index.'.two-games.tmp';
    if(file_put_contents($tmp,$after,LOCK_EX)!==strlen($after)||!rename($tmp,$index))
        throw new RuntimeException('Two-games homepage update failed');
    echo "TWO_GAMES_UPDATED\n";exit(0);
}
if(strpos($before,'home-bulletins--hero home-24h--hero')===false||strpos($before,'id="home-24h-grid"')===false)throw new RuntimeException('24 Hours sidebar or grid not found');
$games=<<<'HTML'

          <section class="rp-home-daily-games" id="rp-home-daily-games" aria-labelledby="rp-home-daily-games-title">
            <div class="rp-home-daily-games-head">
              <h2 id="rp-home-daily-games-title">Games of the Day</h2>
              <a href="/games/">All games</a>
            </div>
            <div class="rp-home-daily-games-list">
              <a class="rp-home-daily-game rp-home-daily-game--featured" href="/games/sudoku/" aria-label="Play today's Sudoku">
                <span class="rp-home-daily-game-icon" aria-hidden="true"><svg viewBox="0 0 64 64"><use href="/game-icons.svg#sudoku"></use></svg></span>
                <span class="rp-home-daily-game-copy"><strong>Today's Sudoku</strong><small>Today's 9×9 logic puzzle</small></span>
              </a>
              <a class="rp-home-daily-game" href="/games/word-riddle/" aria-label="Play today's Word Riddle">
                <span class="rp-home-daily-game-icon" aria-hidden="true"><svg viewBox="0 0 64 64"><use href="/game-icons.svg#word-riddle"></use></svg></span>
                <span class="rp-home-daily-game-copy"><strong>Today's Word Riddle</strong><small>One clue. Five guesses.</small></span>
              </a>
            </div>
          </section>
HTML;
$css=<<<'CSS'
/* Games of the Day — homepage sidebar only; retains existing game SVG symbols. */
body:has(.editorial-cover) #latest .home-24h--hero .rp-home-daily-games{margin-top:26px;padding-top:22px;border-top:1px solid #e9e9e9;min-width:0;font-family:Manrope,Inter,Arial,sans-serif}
body:has(.editorial-cover) #latest .rp-home-daily-games-head{display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin-bottom:15px}
body:has(.editorial-cover) #latest .rp-home-daily-games-head h2{font:650 clamp(17px,1.28vw,19px)/1.35 Manrope,Inter,Arial,sans-serif;letter-spacing:-.03em;color:#242424;margin:0}
body:has(.editorial-cover) #latest .rp-home-daily-games-head a{font:500 11px/1.5 Manrope,Inter,Arial,sans-serif;color:#656565;text-decoration:none;white-space:nowrap}
body:has(.editorial-cover) #latest .rp-home-daily-games-head a:hover{color:#a32331}
body:has(.editorial-cover) #latest .rp-home-daily-games-list{display:grid;gap:9px}
body:has(.editorial-cover) #latest .rp-home-daily-game{display:flex;align-items:center;gap:13px;min-width:0;min-height:68px;padding:11px 13px;background:#f9f9f9;border-radius:8px;border:0;text-decoration:none;color:#222;transition:background .16s ease}
body:has(.editorial-cover) #latest .rp-home-daily-game:hover{background:#f1f1f1}
body:has(.editorial-cover) #latest .rp-home-daily-game:focus-visible{outline:2px solid #a32331;outline-offset:3px}
body:has(.editorial-cover) #latest .rp-home-daily-game-icon{display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;border-radius:7px;background:#191919}
body:has(.editorial-cover) #latest .rp-home-daily-game-icon svg{display:block;width:31px;height:31px;overflow:visible}
body:has(.editorial-cover) #latest .rp-home-daily-game--featured{padding:15px 14px;min-height:88px}
body:has(.editorial-cover) #latest .rp-home-daily-game--featured .rp-home-daily-game-icon{flex-basis:58px;width:58px;height:58px}
body:has(.editorial-cover) #latest .rp-home-daily-game--featured .rp-home-daily-game-icon svg{width:39px;height:39px}
body:has(.editorial-cover) #latest .rp-home-daily-game-copy{display:block;flex:1;min-width:0}
body:has(.editorial-cover) #latest .rp-home-daily-game-copy strong{display:block;font:650 14px/1.36 Manrope,Inter,Arial,sans-serif;letter-spacing:-.02em;color:#242424;overflow-wrap:break-word}
body:has(.editorial-cover) #latest .rp-home-daily-game--featured .rp-home-daily-game-copy strong{font-size:16px}
body:has(.editorial-cover) #latest .rp-home-daily-game-copy small{display:block;margin-top:4px;font:400 11px/1.5 Manrope,Inter,Arial,sans-serif;color:#6b6b6b;overflow-wrap:break-word}
body:has(.editorial-cover) #latest .rp-home-daily-game:hover .rp-home-daily-game-copy strong{color:#a32331}
@media(max-width:760px){body:has(.editorial-cover) #latest .home-24h--hero .rp-home-daily-games{margin-top:24px;padding-top:20px}body:has(.editorial-cover) #latest .rp-home-daily-game{min-height:70px}}
@media(prefers-reduced-motion:reduce){body:has(.editorial-cover) #latest .rp-home-daily-game{transition:none}}
CSS;
$pattern='~(<aside\s+class="home-bulletins--hero\s+home-24h--hero"[^>]*>.*?<div\s+class="home-24h-grid"\s+id="home-24h-grid"[^>]*>.*?</div>)(\s*</aside>)~s';
$count=preg_match_all($pattern,$before);
if($count!==1)throw new RuntimeException('Expected exactly one 24 Hours insertion point, found '.$count);
$after=preg_replace_callback($pattern,static function(array $m)use($games):string{return $m[1].$games.$m[2];},$before,1,$replacements);
if(!is_string($after)||$replacements!==1||substr_count($after,$marker)!==1)throw new RuntimeException('Unexpected insertion result');
$cssTag='<link rel="stylesheet" href="/rp-home-daily-games.css?v=20261118a">';
if(strpos($after,$cssTag)===false){$after=str_replace('</head>',$cssTag."\n".'</head>',$after,$heads);if($heads!==1)throw new RuntimeException('Stylesheet insertion failed');}
if(!is_file($backup)&&!copy($index,$backup))throw new RuntimeException('Pre-change backup failed');
$tmpCss=$stylesheet.'.tmp';$tmpIndex=$index.'.tmp';
if(file_put_contents($tmpCss,$css,LOCK_EX)!==strlen($css)||!rename($tmpCss,$stylesheet))throw new RuntimeException('CSS write failed');
if(file_put_contents($tmpIndex,$after,LOCK_EX)!==strlen($after)||!rename($tmpIndex,$index))throw new RuntimeException('HTML write failed');
echo "GAMES_INSTALLED; HTML=".strlen($after)." CSS=".strlen($css)." BACKUP=".$backup."\n";
