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
        '$1?v=20261118f$2', $html
    );
    if (!is_string($updated)) throw new RuntimeException('Cannot normalise script URL');
    if (!str_contains($updated, 'src="/home-latest-auto.js')) {
        $updated=str_replace('</head>', '<script defer src="/home-latest-auto.js?v=20261118f"></script>' . "\n</head>", $updated);
    }
    if (str_contains($updated, 'id="rp-home-daily-games"')) {
        $updated=preg_replace(
            '~(<link\b[^>]*href="/rp-home-daily-games\.css)(?:\?[^"]*)?("[^>]*>)~i',
            '$1?v=20261118f$2', $updated
        );
        if (!is_string($updated)) throw new RuntimeException('Cannot normalise Games style URL');
        if (!str_contains($updated,'href="/rp-home-daily-games.css')) {
            $updated=str_replace('</head>', '<link rel="stylesheet" href="/rp-home-daily-games.css?v=20261118f">' . "\n</head>", $updated);
        }
    }
    /* Hairline section rules on the homepage only; one link through rebuilds. */
    $updated = preg_replace(
        '~(<link\\b[^>]*href="/rp-home-dividers\\.css)(?:\\?[^"]*)?("[^>]*>)~i',
        '$1?v=20261118g$2', $updated
    );
    if (!is_string($updated)) throw new RuntimeException('Cannot normalise divider style URL');
    if (!str_contains($updated, 'href="/rp-home-dividers.css')) {
        $updated=str_replace('</head>',
            '<link rel="stylesheet" href="/rp-home-dividers.css?v=20261118g">' . "\n</head>", $updated);
    }
    /* Always-visible story titles + hover focus, limited to homepage rails. */
    $updated = preg_replace(
        '~(<link\b[^>]*href="/rp-home-card-focus\.css)(?:\?[^"]*)?("[^>]*>)~i',
        '$1?v=20261118h$2', $updated
    );
    if (!is_string($updated)) throw new RuntimeException('Cannot normalise focus style URL');
    if (!str_contains($updated, 'href="/rp-home-card-focus.css')) {
        $updated=str_replace('</head>',
            '<link rel="stylesheet" href="/rp-home-card-focus.css?v=20261118h">' . "\n</head>", $updated);
    }
    /* Swiss vertical NEWSROOM label; keep the underlying semantic heading. */
    $updated = preg_replace(
        '~(<link\b[^>]*href="/rp-home-newsroom-vertical\.css)(?:\?[^"]*)?("[^>]*>)~i',
        '$1?v=20261118o$2', $updated
    );
    if (!is_string($updated)) throw new RuntimeException('Cannot normalise Newsroom stylesheet');
    if (!str_contains($updated, 'href="/rp-home-newsroom-vertical.css')) {
        $updated=str_replace('</head>',
            '<link rel="stylesheet" href="/rp-home-newsroom-vertical.css?v=20261118o">' . "\n</head>", $updated);
    }
    $updated = preg_replace(
        '~(<h3\b[^>]*id="section-more-reporting"[^>]*>)(?:Across the newsroom|Newsroom)(</h3>)~i',
        '$1Newsroom$2', $updated
    );
    if (!is_string($updated)) throw new RuntimeException('Cannot normalise Newsroom heading');
    /* Subtle scroll position + live story count for the first reporting rail. */
    $updated = preg_replace(
        '~(<link\b[^>]*href="/rp-newsroom-rail-ux\.css)(?:\?[^"]*)?("[^>]*>)~i',
        '$1?v=20261118p$2', $updated
    );
    if (!is_string($updated)) throw new RuntimeException('Cannot normalise Newsroom rail progress CSS');
    if (!str_contains($updated,'href="/rp-newsroom-rail-ux.css')) {
        $updated=str_replace('</head>',
            '<link rel="stylesheet" href="/rp-newsroom-rail-ux.css?v=20261118p">' . "\n</head>",$updated);
    }
    $updated = preg_replace(
        '~(<script\b[^>]*src="/rp-newsroom-rail-ux\.js)(?:\?[^"]*)?("[^>]*></script>)~i',
        '$1?v=20261118p$2', $updated
    );
    if (!is_string($updated)) throw new RuntimeException('Cannot normalise Newsroom rail progress JS');
    if (!str_contains($updated,'src="/rp-newsroom-rail-ux.js')) {
        $updated=str_replace('</head>',
            '<script defer src="/rp-newsroom-rail-ux.js?v=20261118p"></script>' . "\n</head>",$updated);
    }
    /* One-time section-heading + hairline entrance; never affect stories. */
    $updated = preg_replace(
        '~(<link\b[^>]*href="/rp-editorial-section-reveal\.css)(?:\?[^"]*)?("[^>]*>)~i',
        '$1?v=20261118q$2',$updated
    );
    if (!is_string($updated)) throw new RuntimeException('Cannot normalise editorial section CSS');
    if (!str_contains($updated,'href="/rp-editorial-section-reveal.css')) {
        $updated=str_replace('</head>',
            '<link rel="stylesheet" href="/rp-editorial-section-reveal.css?v=20261118q">' . "\n</head>",$updated);
    }
    $updated = preg_replace(
        '~(<script\b[^>]*src="/rp-editorial-section-reveal\.js)(?:\?[^"]*)?("[^>]*></script>)~i',
        '$1?v=20261118q$2',$updated
    );
    if (!is_string($updated)) throw new RuntimeException('Cannot normalise editorial section JS');
    if (!str_contains($updated,'src="/rp-editorial-section-reveal.js')) {
        $updated=str_replace('</head>',
            '<script defer src="/rp-editorial-section-reveal.js?v=20261118q"></script>' . "\n</head>",$updated);
    }
    /* Native image-to-article navigation: both pages opt in, never intercept links. */
    $updated = preg_replace(
        '~(<link\b[^>]*href="/rp-article-photo-transition\.css)(?:\?[^"]*)?("[^>]*>)~i',
        '$1?v=20261119t$2',$updated
    );
    if (!is_string($updated)) throw new RuntimeException('Cannot normalise photo transition CSS');
    if (!str_contains($updated,'href="/rp-article-photo-transition.css')) {
        $updated=str_replace('</head>',
            '<link rel="stylesheet" href="/rp-article-photo-transition.css?v=20261119t">' . "\n</head>",$updated);
    }
    $updated = preg_replace(
        '~(<script\b[^>]*src="/rp-article-photo-transition\.js)(?:\?[^"]*)?("[^>]*></script>)~i',
        '$1?v=20261119t$2',$updated
    );
    if (!is_string($updated)) throw new RuntimeException('Cannot normalise photo transition JS');
    if (!str_contains($updated,'src="/rp-article-photo-transition.js')) {
        $updated=str_replace('</head>',
            '<script src="/rp-article-photo-transition.js?v=20261119t"></script>' . "\n</head>",$updated);
    }
    return $updated;
}

/* Shared-cover transition links are also restored to article documents.
 * Conservative: only canonical sections and only <main class="ra-article">.
 */
function rp_article_transition_markup(string $html): string {
    if (strlen($html)<600 || !str_contains($html,'</head>') ||
        !preg_match('~<main\b[^>]*class="[^"]*\bra-article\b~i',$html)) return $html;
    foreach ([
        ['link','href','rp-article-photo-transition.css',
           '<link rel="stylesheet" href="/rp-article-photo-transition.css?v=20261119t">'],
        ['script','src','rp-article-photo-transition.js',
           '<script src="/rp-article-photo-transition.js?v=20261119t"></script>']
    ] as [$tag,$attr,$file,$tagMarkup]) {
        $pattern='~(<'.$tag.'\b[^>]*'.$attr.'="/'.preg_quote($file,'~').
            ')(?:\?[^"]*)?("[^>]*>)~i';
        $next=preg_replace($pattern,'$1?v=20261119t$2',$html);
        if (!is_string($next)) throw new RuntimeException('Invalid article transition link');
        $html=$next;
        if (!str_contains($html,$attr.'="/'.$file)) {
            $html=str_replace('</head>',$tagMarkup."\n</head>",$html);
        }
    }
    return $html;
}

/* Approved transition assets travel with this existing guard so Hostinger can
 * install them without a full-site redeploy or an additional cron job. */
function rp_transition_asset_payloads(): array {
    return [
        'rp-article-photo-transition-master.css' => <<<'RP_CSS'
/* The Raisina Post — editorial shared-cover transition, version 20261119t.
 * Pure progressive enhancement: native same-origin document navigation.
 */
@view-transition { navigation: auto; }

@keyframes rpPageLeave {
  from { opacity:1; transform:translateY(0) scale(1); }
  to { opacity:.62; transform:translateY(-9px) scale(.995); }
}
@keyframes rpPageArrive {
  from { opacity:0; transform:translateY(20px) scale(.992); }
  to { opacity:1; transform:translateY(0) scale(1); }
}
@keyframes rpTitleDepart {
  from { opacity:1; }
  to { opacity:0; }
}
@keyframes rpTitleArrive {
  from { opacity:0; }
  to { opacity:1; }
}
::view-transition-old(root) {
  animation: 260ms cubic-bezier(.22,.7,.2,1) both rpPageLeave;
}
::view-transition-new(root) {
  animation: 390ms cubic-bezier(.16,1,.3,1) both rpPageArrive;
}
::view-transition-group(rp-story-cover) {
  animation-duration: 560ms;
  animation-timing-function: cubic-bezier(.18,.82,.18,1);
  overflow: clip;
}
::view-transition-image-pair(rp-story-cover) {
  isolation: isolate;
}
::view-transition-old(rp-story-cover),
::view-transition-new(rp-story-cover) {
  object-fit: cover;
  width: 100%;
  height: 100%;
}
::view-transition-group(rp-story-title) {
  animation-duration: 470ms;
  animation-timing-function: cubic-bezier(.2,.72,.22,1);
}
::view-transition-old(rp-story-title) {
  animation: 180ms ease-out both rpTitleDepart;
}
::view-transition-new(rp-story-title) {
  animation: 350ms 110ms ease-out both rpTitleArrive;
}
@media (prefers-reduced-motion: reduce) {
  ::view-transition-old(root),
  ::view-transition-new(root),
  ::view-transition-group(rp-story-cover),
  ::view-transition-old(rp-story-cover),
  ::view-transition-new(rp-story-cover),
  ::view-transition-group(rp-story-title),
  ::view-transition-old(rp-story-title),
  ::view-transition-new(rp-story-title) {
    animation: none !important;
    animation-duration: .01ms !important;
    animation-delay: 0ms !important;
  }
}

html.rp-photo-arriving main.ra-article .ra-cover-frame img,
html.rp-photo-arriving main.ra-article figure.ra-cover img,
html.rp-photo-arriving main.ra-article .article-hero img,
html.rp-photo-arriving main.ra-article .article-cover img,
html.rp-photo-arriving figure.ra-cover img {visibility:hidden!important;}
RP_CSS,
        'rp-article-photo-transition-master.js' => <<<'RP_JS'
/* The Raisina Post — cinematic shared-cover and headline navigation, 20261119t.
 * Same-origin native View Transitions; no click interception or loading screens.
 * Select media by its actual article link instead of depending on card classes.
 */
(() => {
  'use strict';
  const ARTICLE_RE = /^\/(?:business|governance|in-depth|opinion)\/[a-z0-9]+(?:-[a-z0-9]+)*\/?$/;
  const COVER = 'rp-story-cover';
  const TITLE = 'rp-story-title';
  const reduced = () => !!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
  const canonical = value => {
    try {
      const url = new URL(value, document.baseURI);
      if (url.origin !== location.origin) return null;
      const path = url.pathname.replace(/\/+$/, '') || '/';
      return path;
    } catch (_) { return null; }
  };
  const article = path => typeof path === 'string' && ARTICLE_RE.test(path);
  const home = path => path === '/';
  const homepageLinks = () => document.querySelectorAll('main a[href]');
  const homeMedia = path => {
    // The image may be wrapped by its own anchor, separate from the headline.
    let storyLink = null;
    for (const link of homepageLinks()) {
      if (canonical(link.getAttribute('href')) !== path) continue;
      storyLink ||= link;
      const ownImage = link.querySelector('img');
      if (ownImage) return ownImage;
      const card = link.closest('.rail-story, .article-row, .story-card, .news-card, .feature-card, article');
      const cardImage = card?.querySelector('picture img, figure img, img');
      if (cardImage) return cardImage;
    }
    // Nonstandard cards: find an adjacent photo without assigning unrelated images.
    return storyLink?.parentElement?.querySelector('picture img, figure img') || null;
  };
  const homeTitle = path => {
    for (const link of homepageLinks()) {
      if (canonical(link.getAttribute('href')) !== path) continue;
      const heading = link.closest('h1, h2, h3, h4');
      if (heading) return heading;
    }
    return null;
  };
  const articleMedia = () => document.querySelector(
    'main.ra-article .ra-cover-frame img,' +
    'main.ra-article figure.ra-cover img,' +
    'main.ra-article .article-hero img,' +
    'main.ra-article .article-cover img,' +
    'main.ra-article figure img,' +
    'main.ra-article header img,' +
    'main.ra-article img'
  );
  const articleTitle = () => document.querySelector('main.ra-article h1');
  const stamp = (node, name) => {
    if (node?.isConnected) node.style.viewTransitionName = name;
  };
  const clear = (node, name) => {
    if (node?.style.viewTransitionName === name) node.style.removeProperty('view-transition-name');
  };
  const cleanup = nodes => {
    clear(nodes.cover, COVER);
    clear(nodes.title, TITLE);
  };
  const nodesForHome = path => ({ cover: homeMedia(path), title: homeTitle(path) });
  const nodesForArticle = () => ({ cover: articleMedia(), title: articleTitle() });
  const mark = nodes => {
    stamp(nodes.cover, COVER);
    stamp(nodes.title, TITLE);
  };
  let requested = null;
  let lastNodes = null;

  document.addEventListener('click', event => {
    if (reduced() || event.defaultPrevented || event.button !== 0 ||
        event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ||
        !home(canonical(location.href))) return;
    const link = event.target?.closest?.('a[href]');
    if (!link || (link.target && link.target !== '_self') || link.hasAttribute('download')) return;
    const destination = canonical(link.getAttribute('href'));
    requested = article(destination) ? destination : null;
  }, { capture: true });

  window.addEventListener('pageswap', event => {
    if (!event.viewTransition || reduced()) return;
    const destination = canonical(event.activation?.to?.url);
    const path = canonical(location.href);
    let nodes = null;
    if (home(path) && article(destination)) {
      // A shared photo is eligible only on an actual article navigation.
      if (requested === destination) nodes = nodesForHome(destination);
    } else if (article(path) && home(destination)) {
      nodes = nodesForArticle();
    }
    if (!nodes) return;
    mark(nodes);
    lastNodes = nodes;
    event.viewTransition.finished.then(() => cleanup(nodes), () => cleanup(nodes));
  });

  window.addEventListener('pagereveal', event => {
    if (!event.viewTransition || reduced()) return;
    // A same-origin referrer is a fallback when Navigation.activation is absent.
    const source = canonical(window.navigation?.activation?.from?.url) ||
                   canonical(document.referrer);
    const path = canonical(location.href);
    let nodes = null;
    if (article(path) && home(source)) nodes = nodesForArticle();
    else if (home(path) && article(source)) nodes = nodesForHome(source);
    if (!nodes) return;
    mark(nodes);
    lastNodes = nodes;
    event.viewTransition.finished.then(() => cleanup(nodes), () => cleanup(nodes));
  });

  window.addEventListener('pageshow', event => {
    if (!event.persisted) return;
    if (lastNodes) cleanup(lastNodes);
    lastNodes = null;
    requested = null;
  });
})();

/* Visible editorial zoom fallback — v20261119t.
 * Runs from a real article click: readers see the photo expand even when
 * cross-document View Transitions only produce a full-page fade.
 */
(() => {
  'use strict';
  const KEY='rp-visible-photo-navigation-v1';
  const isArticle=p=>/^\/(?:business|governance|in-depth|opinion)\/[a-z0-9-]+$/.test(p||'');
  const canonical=raw=>{
    try {const u=new URL(raw,document.baseURI);return u.origin===location.origin?(u.pathname.replace(/\/+$/,'')||'/'):null;}
    catch(_){return null;}
  };
  const reduce=()=>!!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
  const box=el=>{const r=el.getBoundingClientRect();return {left:r.left,top:r.top,width:r.width,height:r.height};};
  const valid=r=>r&&r.width>30&&r.height>25;
  const imageFor=p=>{
    for(const a of document.querySelectorAll('main a[href]')){
      if(canonical(a.getAttribute('href'))!==p)continue;
      const pic=a.querySelector('picture img,img')||
        a.closest('.rail-story,.article-row,.story-card,.news-card,article')?.querySelector('picture img,img');
      if(pic&&valid(box(pic)))return pic;
    }
    return null;
  };
  const articleImage=()=>document.querySelector(
    'main.ra-article .ra-cover-frame img,main.ra-article figure.ra-cover img,'+
    'main.ra-article .article-hero img,main.ra-article .article-cover img,'+
    'figure.ra-cover img,main.ra-article figure img,main.ra-article img,article figure img');
  const articleHeading=()=>document.querySelector('main.ra-article h1,article h1,main h1');
  const toStyle=r=>({left:r.left+'px',top:r.top+'px',width:r.width+'px',height:r.height+'px'});
  const create=(tag,style)=>{
    const el=document.createElement(tag);
    Object.assign(el.style,{position:'fixed',pointerEvents:'none',margin:'0',padding:'0',...style});
    el.setAttribute('aria-hidden','true');
    document.body.appendChild(el);
    return el;
  };
  const curtain=()=>create('div',{inset:'0',zIndex:'2147483638',backgroundColor:'#f9f9f9',opacity:'0'});
  const clone=(src,r)=>{
    const el=create('img',{...toStyle(r),zIndex:'2147483639',objectFit:'cover',maxWidth:'none',
      borderRadius:'4px',boxShadow:'0 12px 36px rgba(0,0,0,.12)'});
    el.src=src;el.alt='';return el;
  };
  const flight=(el,a,b,duration)=>el.animate?.([a,b],{duration,fill:'forwards',
    easing:'cubic-bezier(.18,.82,.18,1)'});
  const store=x=>{try{sessionStorage.setItem(KEY,JSON.stringify(x));}catch(_){}};
  const take=()=>{try{const x=JSON.parse(sessionStorage.getItem(KEY)||'null');
    sessionStorage.removeItem(KEY);return x;}catch(_){return null;}};
  const preview=()=>{
    const width=Math.min(innerWidth*.88,980),height=Math.min(innerHeight*.59,width*.66);
    return {left:(innerWidth-width)/2,top:Math.max(40,innerHeight*.12),width,height};
  };
  let leaving=false;
  document.addEventListener('click',e=>{
    if(leaving||reduce()||canonical(location.href)!=='/'||e.defaultPrevented||
      e.button!==0||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey)return;
    const a=e.target?.closest?.('a[href]');
    if(!a||a.hasAttribute('download')||(a.target&&a.target!=='_self'))return;
    const target=canonical(a.getAttribute('href'));
    if(!isArticle(target))return;
    const img=imageFor(target);
    if(!img)return;
    const r=box(img),src=img.currentSrc||img.src;
    if(!valid(r)||!src)return;
    e.preventDefault();
    leaving=true;
    const end=preview();
    store({target,src,rect:end,stamp:Date.now()});
    const backdrop=curtain(),ghost=clone(src,r);
    flight(backdrop,{opacity:0},{opacity:.94},350);
    const anim=flight(ghost,{...toStyle(r),borderRadius:'4px'},
      {...toStyle(end),borderRadius:'12px'},350);
    const dest=new URL(a.getAttribute('href'),document.baseURI).href;
    let done=false;
    const navigate=()=>{if(done)return;done=true;location.assign(dest);};
    anim?.finished.then(navigate,navigate);
    window.setTimeout(navigate,450);
  },{capture:true});

  const path=canonical(location.href);
  let carry=null;
  try{const data=JSON.parse(sessionStorage.getItem(KEY)||'null');
    if(isArticle(path)&&data?.target===path&&Date.now()-data.stamp<12000&&valid(data.rect))
      carry=data;
    else if(data)sessionStorage.removeItem(KEY);
  }catch(_){}
  if(!carry||reduce())return;
  document.documentElement.classList.add('rp-photo-arriving');
  window.setTimeout(()=>document.documentElement.classList.remove('rp-photo-arriving'),1900);
  const arrive=()=>{
    take();
    const destination=articleImage();
    if(!destination){document.documentElement.classList.remove('rp-photo-arriving');return;}
    const end=box(destination);
    if(!valid(end)){document.documentElement.classList.remove('rp-photo-arriving');return;}
    const backdrop=curtain();backdrop.style.opacity='.94';
    const ghost=clone(carry.src,carry.rect);
    destination.style.visibility='hidden';
    document.documentElement.classList.remove('rp-photo-arriving');
    flight(backdrop,{opacity:.94},{opacity:0},460);
    const heading=articleHeading();
    if(heading)flight(heading,{opacity:.05,transform:'translateY(20px)'},
      {opacity:1,transform:'translateY(0)'},460);
    const anim=flight(ghost,{...toStyle(carry.rect),borderRadius:'12px'},
      {...toStyle(end),borderRadius:'4px'},460);
    let finished=false;
    const complete=()=>{
      if(finished)return;finished=true;
      destination.style.removeProperty('visibility');ghost.remove();backdrop.remove();
    };
    anim?.finished.then(complete,complete);
    window.setTimeout(complete,620);
  };
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',arrive,{once:true});
  else window.requestAnimationFrame(arrive);
})();
RP_JS,
    ];
}

if (in_array('--self-test', $argv, true)) {
    $html='<html><head></head><body><aside><div id="home-24h-grid"></div><section id="rp-home-daily-games"></section></aside><div class="home-secondary-heading"><h3 id="section-more-reporting">Across the newsroom</h3></div></body></html>';
    $html=str_replace('</body>',str_repeat('x',600).'</body>',$html);
    $once=rp_latest_transform($html);
    $twice=rp_latest_transform($once);
    if ($once!==$twice || substr_count($twice,'src="/home-latest-auto.js')!==1 ||
        substr_count($twice,'href="/rp-home-daily-games.css')!==1 ||
        substr_count($twice,'href="/rp-home-dividers.css')!==1 ||
        substr_count($twice,'href="/rp-home-card-focus.css')!==1 ||
        substr_count($twice,'href="/rp-home-newsroom-vertical.css')!==1 ||
        substr_count($twice,'href="/rp-newsroom-rail-ux.css')!==1 ||
        substr_count($twice,'src="/rp-newsroom-rail-ux.js')!==1 ||
        substr_count($twice,'href="/rp-editorial-section-reveal.css')!==1 ||
        substr_count($twice,'src="/rp-editorial-section-reveal.js')!==1 ||
        substr_count($twice,'href="/rp-article-photo-transition.css')!==1 ||
        substr_count($twice,'src="/rp-article-photo-transition.js')!==1 ||
        substr_count($twice,'id="section-more-reporting">Newsroom</h3>')!==1 ||
        str_contains($twice,'>Across the newsroom</h3>') ||
        rp_latest_transform('<html><head></head><body>not homepage</body></html>')!==
            '<html><head></head><body>not homepage</body></html>') {
        fwrite(STDERR, "FAIL: idempotent homepage guard\n"); exit(1);
    }
    $sample='<!doctype html><html><head></head><body><main class="ra-article">'.str_repeat('<p>reporting</p>',50).'</main></body></html>';
    $articleOnce=rp_article_transition_markup($sample);
    if ($articleOnce!==rp_article_transition_markup($articleOnce) ||
        substr_count($articleOnce,'src="/rp-article-photo-transition.js')!==1 ||
        substr_count($articleOnce,'href="/rp-article-photo-transition.css')!==1 ||
        rp_article_transition_markup('<html><head></head><body>no article</body></html>')!==
          '<html><head></head><body>no article</body></html>') {
        fwrite(STDERR,"FAIL: article transition links\n");exit(1);
    }
    $photoPayloads=rp_transition_asset_payloads();
    if (!str_contains($photoPayloads['rp-article-photo-transition-master.js'],'e.preventDefault()') ||
        !str_contains($photoPayloads['rp-article-photo-transition-master.js'],'rp-story-title') ||
        !str_contains($photoPayloads['rp-article-photo-transition-master.css'],'560ms')) {
        fwrite(STDERR,"FAIL: approved cinematic transition payloads".PHP_EOL);exit(1);
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
    $photoAssetsChanged=0;
    foreach (rp_transition_asset_payloads() as $approvedFilename => $bytes) {
        $master=$private.'/'.$approvedFilename;
        if (is_file($master) && hash_file('sha256',$master)===hash('sha256',$bytes)) continue;
        $temp=$master.'.rp-photo-upgrade.tmp';
        if (file_put_contents($temp,$bytes,LOCK_EX)!==strlen($bytes) || !rename($temp,$master)) {
            @unlink($temp);
            throw new RuntimeException('Could not install approved transition asset '.basename($master));
        }
        $photoAssetsChanged++;
    }
    if ($photoAssetsChanged) echo "PHOTO_TRANSITION_ASSETS version=s refreshed=$photoAssetsChanged".PHP_EOL;
    $filePairs=[[$scriptSource,$root.'/home-latest-auto.js',6000],[$styleSource,$root.'/rp-home-daily-games.css',2500],
      [$private.'/raisina-sudoku-art.webp',$root.'/images/raisina-sudoku-art.webp',3000],
      [$private.'/raisina-word-riddle-art.webp',$root.'/images/raisina-word-riddle-art.webp',3000],
      [$private.'/rp-home-dividers-master.css',$root.'/rp-home-dividers.css',1200],
      [$private.'/rp-home-card-focus-master.css',$root.'/rp-home-card-focus.css',2400],
      [$private.'/rp-home-newsroom-vertical-master.css',$root.'/rp-home-newsroom-vertical.css',2200],
      [$private.'/rp-newsroom-rail-ux-master.css',$root.'/rp-newsroom-rail-ux.css',1400],
      [$private.'/rp-newsroom-rail-ux-master.js',$root.'/rp-newsroom-rail-ux.js',2500],
      [$private.'/rp-editorial-section-reveal-master.css',$root.'/rp-editorial-section-reveal.css',1700],
      [$private.'/rp-editorial-section-reveal-master.js',$root.'/rp-editorial-section-reveal.js',1000],
      [$private.'/rp-article-photo-transition-master.css',$root.'/rp-article-photo-transition.css',700],
      [$private.'/rp-article-photo-transition-master.js',$root.'/rp-article-photo-transition.js',3000]];
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
    /* Cheap five-minute pass: new/rebuilt articles get their two head links.
     * The site's existing every-minute guard is already verified working. */
    $scanStamp=$private.'/rp-article-photo-transitions-last-scan.stamp';
    $prevScan=is_file($scanStamp)?(int)filemtime($scanStamp):0;
    if (is_file($root.'/rp-article-photo-transition.js') &&
        is_file($root.'/rp-article-photo-transition.css') &&
        time()-$prevScan>=240) {
        $articleScanned=0;$articlePatched=0;$articleErrors=0;
        foreach (['business','governance','in-depth','opinion'] as $section) {
            foreach (glob($root.'/'.$section.'/*/index.html') ?: [] as $articlePath) {
                if (!is_file($articlePath) || is_link($articlePath)) continue;
                $size=filesize($articlePath);
                if (!is_int($size) || $size<12000 || $size>2500000) continue;
                $articleScanned++;
                $beforeArticle=file_get_contents($articlePath);
                if (!is_string($beforeArticle)) {$articleErrors++;continue;}
                $afterArticle=rp_article_transition_markup($beforeArticle);
                if ($afterArticle===$beforeArticle) continue;
                $temporaryArticle=$articlePath.'.rp-photo-guard.tmp';
                if (file_put_contents($temporaryArticle,$afterArticle,LOCK_EX)!==strlen($afterArticle) ||
                    !rename($temporaryArticle,$articlePath)) {
                    @unlink($temporaryArticle);$articleErrors++;continue;
                }
                $articlePatched++;
            }
        }
        if ($articleErrors===0) touch($scanStamp);
        echo "PHOTO_TRANSITIONS scanned=$articleScanned updated=$articlePatched errors=$articleErrors\n";
        if ($articleErrors) exit(1);
    }
} catch(Throwable $error) {
    fwrite(STDERR,"GUARD_ERROR: ".$error->getMessage()."\n");exit(1);
}