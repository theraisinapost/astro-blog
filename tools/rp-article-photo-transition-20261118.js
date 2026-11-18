/* The Raisina Post — shared photograph across full-page article navigation.
 * Uses native pageswap/pagereveal when available; never intercepts navigation.
 * No timers, preloading, scroll lock, SPA routing, or layout modifications.
 */
(() => {
  'use strict';
  const PHOTO_NAME='rp-story-cover';
  const ARTICLE_RE=/^\/(?:business|governance|in-depth|opinion)\/[a-z0-9]+(?:-[a-z0-9]+)*\/$/;
  const reduced=() => !!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
  const normalize=value=>{
    try {
      const u=new URL(value,document.baseURI);
      return u.origin===location.origin?u.pathname:null;
    } catch (_) {return null;}
  };
  const article=path=>typeof path==='string' && ARTICLE_RE.test(path);
  const home=path=>path==='/';
  const imageFromCard=card=>card?.querySelector('.rail-visual picture img, .rail-visual img, .article-media picture img, .article-media img');
  const articleImage=()=>document.querySelector('main.ra-article .ra-cover-frame img, main.ra-article figure.ra-cover img');
  const homeCard=path=>{
    const cards=document.querySelectorAll('main#main .rail-story, main#main .article-row');
    for (const card of cards) {
      const link=card.querySelector('h3 a[href],.rail-visual[href],.article-media[href]');
      if (normalize(link?.getAttribute('href'))===path) return card;
    }
    return null;
  };
  const stamp=image=>{
    if (!image || !image.isConnected) return;
    image.style.viewTransitionName=PHOTO_NAME;
  };
  const clear=image=>{
    if (image?.style.viewTransitionName===PHOTO_NAME) image.style.removeProperty('view-transition-name');
  };
  let requestedPath=null;
  let requestedImage=null;
  document.addEventListener('click',event=>{
    if (reduced() || event.defaultPrevented || event.button!==0 ||
        event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const link=event.target?.closest?.('a[href]');
    if (!link || link.target && link.target!=='_self' || link.hasAttribute('download')) return;
    const path=normalize(link.getAttribute('href'));
    if (!article(path) || !home(location.pathname)) return;
    const card=link.closest('.rail-story,.article-row');
    requestedPath=card && normalize(card.querySelector('h3 a[href],.rail-visual[href],.article-media[href]')?.getAttribute('href'))===path?path:null;
    requestedImage=requestedPath?imageFromCard(card):null;
  },{capture:true});

  // Event is fired before the outgoing snapshot; this is the only place
  // where the old document photo receives the shared transition name.
  window.addEventListener('pageswap',event=>{
    if (!event.viewTransition || reduced()) return;
    const destination=normalize(event.activation?.to?.url);
    let image=null;
    if (home(location.pathname) && article(destination) && requestedPath===destination) image=requestedImage;
    if (article(location.pathname) && home(destination)) image=articleImage();
    if (!image) return;
    stamp(image);
    event.viewTransition.finished.then(()=>clear(image),()=>clear(image));
  });

  // Script loads synchronously from <head> so this listener exists before
  // the first render / pagereveal snapshot on the incoming article.
  window.addEventListener('pagereveal',event=>{
    if (!event.viewTransition || reduced()) return;
    const source=normalize(window.navigation?.activation?.from?.url);
    let image=null;
    if (article(location.pathname) && home(source)) image=articleImage();
    if (home(location.pathname) && article(source)) image=imageFromCard(homeCard(source));
    if (!image) return; // no cover: keep regular understated page fade
    stamp(image);
    event.viewTransition.finished.then(()=>clear(image),()=>clear(image));
  });

  window.addEventListener('pageshow',event=>{
    if (!event.persisted) return;
    clear(requestedImage);
    clear(articleImage());
    requestedPath=null;
    requestedImage=null;
  });
})();
