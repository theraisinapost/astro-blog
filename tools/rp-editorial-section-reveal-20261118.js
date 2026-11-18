/* The Raisina Post — quiet one-time editorial chapter reveals.
 * Animate headings/rules only. Never hide editorial content before JS loads.
 */
(() => {
  'use strict';
  function init() {
    if (typeof IntersectionObserver === 'undefined') return;
    if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) return;
    const sections = ['home-crossover','archive','research-desk']
      .map(id => document.getElementById(id)).filter(Boolean);
    if (!sections.length) return;
    const observer = new IntersectionObserver(entries => {
      for (const entry of entries) {
        if (!entry.isIntersecting) continue;
        entry.target.classList.add('rp-editorial-chapter-reveal');
        observer.unobserve(entry.target);
      }
    }, {rootMargin:'0px 0px -8% 0px',threshold:0.025});
    for (const section of sections) {
      const box=section.getBoundingClientRect();
      // Already-visible sections are immediately readable, without re-entry flash.
      if (box.top <= window.innerHeight * .9 && box.bottom >= 0) continue;
      observer.observe(section);
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',init,{once:true});
  else init();
})();
