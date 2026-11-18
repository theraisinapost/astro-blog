/* The Raisina Post — live Newsroom rail progress, 18 November 2026.
 * Decorative feedback for EXISTING native scroll, mouse-drag, and snap.
 * Does not add competing pointer handlers or change article/card content.
 */
(() => {
  'use strict';
  function init() {
    const rail = document.querySelector('#latest > .latest-rail.home-mini-rail');
    if (!rail || rail.nextElementSibling?.className === 'rp-newsroom-rail-status') return;

    const status = document.createElement('div');
    status.className = 'rp-newsroom-rail-status';
    const progress = document.createElement('div');
    progress.className = 'rp-newsroom-rail-progress';
    progress.setAttribute('role','progressbar');
    progress.setAttribute('aria-label','Newsroom browsing progress');
    progress.setAttribute('aria-valuemin','0');
    progress.setAttribute('aria-valuemax','100');
    const thumb = document.createElement('span');
    thumb.className = 'rp-newsroom-rail-thumb';
    thumb.setAttribute('aria-hidden','true');
    progress.append(thumb);
    const count = document.createElement('span');
    count.className = 'rp-newsroom-rail-counter';
    count.setAttribute('aria-hidden','true');
    status.append(progress,count);
    rail.insertAdjacentElement('afterend',status);

    let scheduled = false;
    const two = value => String(value).padStart(2,'0');
    function render() {
      scheduled = false;
      const cards = Array.from(rail.children).filter(el => el.matches('.rail-story'));
      const maximum = Math.max(0, rail.scrollWidth - rail.clientWidth);
      status.hidden = cards.length < 2 || maximum < 2;
      if (status.hidden) return;
      const portion = Math.min(1, Math.max(0, rail.scrollLeft / maximum));
      const thumbWidth = Math.min(100, Math.max(14, 100 * rail.clientWidth / rail.scrollWidth));
      thumb.style.width = thumbWidth.toFixed(3) + '%';
      thumb.style.left = (portion * (100 - thumbWidth)).toFixed(3) + '%';
      progress.setAttribute('aria-valuenow',String(Math.round(portion*100)));

      const left = rail.getBoundingClientRect().left + (parseFloat(window.getComputedStyle(rail).paddingLeft)||0);
      let index = 0, nearest = Infinity;
      cards.forEach((card,i) => {
        const delta = Math.abs(card.getBoundingClientRect().left - left);
        if (delta < nearest) { nearest = delta; index = i; }
      });
      if (maximum - rail.scrollLeft < 2) index = cards.length - 1;
      count.textContent = two(index + 1) + ' / ' + two(cards.length);
      progress.setAttribute('aria-valuetext','Story '+(index+1)+' of '+cards.length);
    }
    function queue() {
      if (scheduled) return;
      scheduled = true;
      requestAnimationFrame(render);
    }
    rail.addEventListener('scroll',queue,{passive:true});
    window.addEventListener('resize',queue,{passive:true});
    if (typeof ResizeObserver !== 'undefined') new ResizeObserver(queue).observe(rail);
    if (typeof MutationObserver !== 'undefined') new MutationObserver(queue).observe(rail,{childList:true});
    queue();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',init,{once:true});
  else init();
})();
