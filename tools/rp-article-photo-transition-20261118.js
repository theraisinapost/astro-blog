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
