/* The Raisina Post — cinematic shared-cover and headline navigation, 20261118s.
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
