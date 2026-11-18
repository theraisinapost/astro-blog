/* The Raisina Post — homepage freshness controller.
 * Never downgrade a verified 24 Hours edition to an older JSON index entry.
 * Put the most recently published edition and reporting first, without changing
 * curated lead, Power Brief, Games, or original article card designs.
 */
(() => {
  'use strict';
  const grid = document.getElementById('home-24h-grid');
  const rail = document.querySelector('#latest .home-secondary-heading + .latest-rail.home-mini-rail');
  if (!grid && !rail) return;

  const origin = new URL(document.baseURI).origin;
  const editionPath = /^\/24-hours\/(india-news|world-news)\/(\d{4}-\d{2}-\d{2})\/$/;
  const articlePath = /^\/(?:business|governance|in-depth|opinion)\/[a-z0-9]+(?:-[a-z0-9]+)*\/$/;
  const clock = value => {
    const valueMs = typeof value === 'string' && value ? Date.parse(value) : NaN;
    return Number.isFinite(valueMs) && valueMs <= Date.now() + 300000 ? valueMs : NaN;
  };
  const safeLocal = value => {
    try { const url = new URL(value, document.baseURI); return url.origin === origin ? url : null; }
    catch (_) { return null; }
  };
  const deskName = desk => desk === 'india' ? 'india-news' : desk === 'world' ? 'world-news' : '';
  const existingCard = desk => grid?.querySelector('.home-24h-card[data-desk="' + desk + '"]');
  const cardTime = card => card ? clock(card.querySelector('time[datetime]')?.dateTime || '') : NaN;
  const newer = (candidate, current) => Number.isFinite(candidate) && (!Number.isFinite(current) || candidate > current);

  const isoDateFormat = new Intl.DateTimeFormat('en-GB', {
    day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',
    hourCycle:'h23',timeZone:'Asia/Kolkata'
  });
  function renderTime(card, publishedAt) {
    const t = card.querySelector('time') || document.createElement('time');
    t.className = 'home-24h-time';
    t.dateTime = publishedAt;
    t.textContent = isoDateFormat.format(new Date(publishedAt)) + ' IST';
    if (!t.isConnected) card.append(t);
  }
  function orderEditions() {
    if (!grid) return;
    const cards = [...grid.querySelectorAll(':scope > .home-24h-card')];
    const ranked = cards.map((card,index) => ({card,index,stamp:cardTime(card)}))
      .sort((a,b) => (Number.isFinite(b.stamp)?b.stamp:0) - (Number.isFinite(a.stamp)?a.stamp:0) || a.index-b.index);
    if (ranked.some((item,index)=>item.card!==cards[index])) grid.append(...ranked.map(item=>item.card));
  }
  function validIndexItem(item) {
    if (!item || !['india','world'].includes(item.desk)) return false;
    if (typeof item.date !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(item.date)) return false;
    if (typeof item.title !== 'string' || item.title.trim().length < 12 || item.title.length > 240) return false;
    if (!Number.isFinite(clock(item.published_at))) return false;
    const url=safeLocal(item.url),expected='/24-hours/'+deskName(item.desk)+'/'+item.date+'/';
    return !!url && url.pathname === expected;
  }
  function applyIndex(items) {
    if (!grid || !Array.isArray(items)) return;
    const records=items.filter(validIndexItem).sort((a,b)=>clock(b.published_at)-clock(a.published_at));
    for (const desk of ['india','world']) {
      const item=records.find(record=>record.desk===desk);
      const card=existingCard(desk);
      if (!card || !item || !newer(clock(item.published_at),cardTime(card))) continue;
      card.href=item.url;
      const heading=card.querySelector('h3');if (heading) heading.textContent=item.title;
      const summary=card.querySelector('p');
      if (summary) summary.textContent='The latest source-verified daily edition, with reporting, context and original source links.';
      renderTime(card,item.published_at);
    }
    orderEditions();
  }
  function applyHomeEditions(parsed) {
    if (!grid) return;
    for (const desk of ['india','world']) {
      const fresh=parsed.querySelector('#home-24h-grid > .home-24h-card[data-desk="'+desk+'"]');
      const current=existingCard(desk),time=cardTime(fresh);
      if (!fresh || !current || !newer(time,cardTime(current))) continue;
      const url=safeLocal(fresh.getAttribute('href'));
      if (!url || !editionPath.test(url.pathname) || !url.pathname.includes('/'+deskName(desk)+'/')) continue;
      const title=fresh.querySelector('h3')?.textContent?.trim();
      if (!title || title.length < 12 || title.length > 240) continue;
      current.href=url.pathname;
      current.querySelector('h3').textContent=title;
      const originalSummary=fresh.querySelector('p')?.textContent?.trim();
      if (originalSummary && current.querySelector('p')) current.querySelector('p').textContent=originalSummary;
      renderTime(current,fresh.querySelector('time').dateTime);
    }
    orderEditions();
  }
  function railEntry(card) {
    const a=card.querySelector('h3 a[href]'),url=safeLocal(a?.getAttribute('href'));
    if (!url || !articlePath.test(url.pathname)) return null;
    const t=card.querySelector('time[datetime]')?.getAttribute('datetime')||'';
    // The homepage may have date-only timestamps; preserve chronology by day.
    if (!/^\d{4}-\d{2}-\d{2}/.test(t) || !Number.isFinite(clock(t))) return null;
    return {url:url.pathname,stamp:clock(t),card};
  }
  function sortNewsroom() {
    if (!rail) return;
    const nodes=[...rail.children];
    const ranked=nodes.map((node,index) => ({node,index,stamp:railEntry(node)?.stamp||0}))
      .sort((a,b)=>b.stamp-a.stamp || a.index-b.index);
    if (ranked.some((item,index)=>item.node!==nodes[index])) rail.append(...ranked.map(item=>item.node));
  }
  function applyNewsroom(parsed) {
    if (!rail) return;
    const source=parsed.querySelector('#latest .home-secondary-heading + .latest-rail.home-mini-rail');
    if (!source) return;
    const known=new Set([...rail.children].map(card=>railEntry(card)?.url).filter(Boolean));
    const incoming=[...source.children].map(railEntry).filter(Boolean);
    // Only prepend actual published cards already rendered by our publication pipeline.
    // Preserve all existing curated cards and their original image elements.
    for (const item of incoming) {
      if (known.has(item.url)) continue;
      known.add(item.url);
      rail.insertBefore(document.importNode(item.card,true),rail.firstChild);
    }
    sortNewsroom();
  }

  let indexInFlight=false,homeInFlight=false,lastHomeCheck=0;
  async function refreshIndex() {
    if (!grid || indexInFlight || document.hidden || navigator.onLine===false) return;
    indexInFlight=true;
    try {
      const response=await fetch('/data/24-hours.json?latest='+Date.now(),{cache:'no-store',credentials:'same-origin'});
      if (!response.ok) return;
      const data=await response.json();
      if (data?.version===1 && Array.isArray(data.editions)) applyIndex(data.editions);
    } catch (_) { /* Never remove already published verified editions on a fetch failure. */ }
    finally { indexInFlight=false; }
  }
  async function refreshHomepage(force=false) {
    if (homeInFlight || document.hidden || navigator.onLine===false) return;
    if (!force && Date.now()-lastHomeCheck < 300000) return;
    lastHomeCheck=Date.now();homeInFlight=true;
    try {
      const response=await fetch('/?home-latest='+Date.now(),{cache:'no-store',credentials:'same-origin'});
      if (!response.ok) return;
      const html=await response.text();
      if (html.length<500 || html.length>500000) return;
      const parsed=new DOMParser().parseFromString(html,'text/html');
      applyHomeEditions(parsed);
      applyNewsroom(parsed);
    } catch (_) { /* Preserve visible cards and existing content. */ }
    finally {homeInFlight=false;}
  }
  orderEditions();sortNewsroom();
  void refreshIndex();
  void refreshHomepage(true);
  window.setInterval(()=>{if (!document.hidden) void refreshIndex();},120000);
  window.setInterval(()=>{if (!document.hidden) void refreshHomepage();},300000);
  document.addEventListener('visibilitychange',()=>{if(!document.hidden){void refreshIndex();void refreshHomepage();}});
  window.addEventListener('online',()=>{void refreshIndex();void refreshHomepage(true);});
})();