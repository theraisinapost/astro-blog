(function raisinaFrontPageV4(){
  "use strict";
  if (location.pathname !== "/" && location.pathname !== "/index.html") return;
  const SELECTED = "#rp-home-v4";
  const FILE_ROOT = "/";
  const fallback = {
    business:"/assets/editorial/business.webp",
    governance:"/assets/editorial/governance.webp",
    depth:"/assets/editorial/research.webp",
    opinion:"/assets/editorial/books.webp"
  };
  const tools = [
    ["Ownership Atlas","Company ownership, dated shareholding records and issuer disclosures.","/ownership-atlas/"],
    ["Parliament File","Parliamentary committee reports and the original record.","/parliament-file/"],
    ["India in Data","Economic indicators with periods, definitions and sources.","/india-data/"],
    ["Document Library","Original policy, company and institutional documents.","/document-library/"],
    ["Policy Monitor","Official notices, circulars and regulatory developments.","/policy-monitor/"],
    ["Country Desk","Compare economies using consistently dated data.","/country-desk/"],
    ["Markets","Market context and links to primary data sources.","/markets/"],
    ["Election Results","Constituency results and election reporting records.","/elections/"],
    ["Research Directory","Browse the complete collection of Raisina research tools.","/research-directory/"]
  ];
  const escape = x => String(x == null ? "" : x).replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
  const absolute = s => {
    try {const u = new URL(s,location.origin); return (u.protocol==="https:"||u.protocol==="http:")?u.href:"";}catch(e){return "";}
  };
  const internal = s => { try{const u=new URL(s,location.href);return u.origin===location.origin?u.pathname:"";}catch(e){return "";} };
  const plain = s => String(s||"").replace(/\s+/g," ").trim();
  const category = href => {
    const p=internal(href);
    if(/^\/business\/.+\/$/.test(p))return "business";
    if(/^\/governance\/.+\/$/.test(p))return "governance";
    if(/^\/in-depth\/.+\/$/.test(p))return "depth";
    if(/^\/(opinion|exchange)\/.+\/$/.test(p))return "opinion";
    return "";
  };
  function fallbackImage(story){
    const t=story.title.toLowerCase();
    if(t.includes("tata sons"))return "/images/tata-sons-bombay-house-archival-2016.jpg";
    if(t.includes("wholesale inflation"))return "/images/india-wholesale-inflation-industrial-kiln-archival.jpg";
    if(t.includes("mahanadi"))return "/images/mahanadi-coalfields-ipo-mining-aerial-illustrative.jpg";
    if(t.includes("razorpay"))return "/images/razorpay-office-cover.webp";
    if(t.includes("railways"))return "/assets/editorial/trade.webp";
    if(t.includes("freedom of expression"))return "/images/mumtaz-patel-colour-red-2026.webp";
    if(t.includes("pollution"))return "/images/delhi-smog-archival-2017-industrial-enforcement.jpg";
    return fallback[story.cat] || fallback.depth;
  }
  function imageFor(story){
    const src=absolute(story.image||fallbackImage(story));
    return '<span class="rpv-image"><img loading="lazy" decoding="async" src="'+escape(src)+'" alt="'+escape(story.alt||"")+'"></span>';
  }
  function card(story,kind){
    const cls=kind==="support"?"rpv-support":kind==="reading"?"rpv-reading":"rpv-article";
    const img=kind==="support"?"":imageFor(story);
    const body='<span class="rpv-storybody"><span class="rpv-headline">'+escape(story.title)+'</span>'+(story.description?'<span class="rpv-deck">'+escape(story.description)+'</span>':"")+'</span>';
    return '<a class="'+cls+'" href="'+escape(story.href)+'">'+img+body+'</a>';
  }
  function getStories(source){
    const found=new Map();
    const selectors=Array.from(source.querySelectorAll('a[href]'));
    for(const a of selectors){
      const cat=category(a.href);if(!cat)continue;
      const p=internal(a.href);if(found.has(p))continue;
      const h=a.querySelector("h1,h2,h3")||(
        a.closest("article,.newsroom-card,.story-card,.newsroom-item,.story-item,.feature-card,.lead-story")||a.parentElement
      )?.querySelector("h1,h2,h3");
      const title=plain(h?.textContent);
      if(title.length<14)continue;
      const wrapper=a.closest("article,.newsroom-card,.story-card,.newsroom-item,.story-item,.feature-card,.lead-story")||a.parentElement?.parentElement||a;
      const desc=plain((wrapper?.querySelector(".lead-description,.story-description,.newsroom-summary,.deck,.summary,p")||a.querySelector("p"))?.textContent||"");
      const img=wrapper?.querySelector("img")||a.querySelector("img");
      found.set(p,{href:a.href,title,description:desc.slice(0,190),image:img?.currentSrc||img?.getAttribute("src")||"",alt:img?.alt||"",cat});
    }
    const leadLink=source.querySelector(".lead-story h2 a,.lead-story h1 a");
    if(leadLink){
      const key=internal(leadLink.href);
      const lead=found.get(key);
      if(lead) {
        lead.image=source.querySelector(".lead-story .lead-visual img")?.getAttribute("src")||lead.image;
        lead.description=plain(source.querySelector(".lead-story .lead-description")?.textContent||lead.description);
      }
      return {items:[...found.values()],lead:lead||[...found.values()][0]};
    }
    return {items:[...found.values()],lead:[...found.values()][0]};
  }
  function renderStories(source,home){
    const data=getStories(source),all=data.items,lead=data.lead;
    if(!lead)return;
    const excluded=new Set([internal(lead.href)]);
    const support=all.filter(x=>!excluded.has(internal(x.href))).slice(0,2);
    support.forEach(x=>excluded.add(internal(x.href)));
    const desks={business:all.filter(x=>x.cat==="business"&&!excluded.has(internal(x.href))).slice(0,3),
      governance:all.filter(x=>x.cat==="governance"&&!excluded.has(internal(x.href))).slice(0,3),
      depth:all.filter(x=>x.cat==="depth"&&!excluded.has(internal(x.href))).slice(0,1),
      opinion:all.filter(x=>x.cat==="opinion"&&!excluded.has(internal(x.href))).slice(0,1)};
    // Never leave a desk empty when its only article was promoted to the main lead.
    for(const k of Object.keys(desks))if(!desks[k].length)desks[k]=all.filter(x=>x.cat===k).slice(0,k==="business"||k==="governance"?3:1);
    const hero=home.querySelector("#rpv-lead");
    if(hero)hero.innerHTML='<a class="rpv-hero" href="'+escape(lead.href)+'">'+imageFor(lead)+'<span class="rpv-hero-copy"><span class="rpv-hero-title">'+escape(lead.title)+'</span><span class="rpv-hero-deck">'+escape(lead.description)+'</span></span></a><div class="rpv-supports">'+support.map(x=>card(x,"support")).join("")+'</div>';
    [["business","#rpv-business"],["governance","#rpv-governance"]].forEach(([k,sel])=>{
      const el=home.querySelector(sel);if(el)el.innerHTML=desks[k].map(x=>card(x,"article")).join("");
    });
    const reading=home.querySelector("#rpv-readings");
    if(reading)reading.innerHTML=[desks.depth[0],desks.opinion[0]].filter(Boolean).map(s=>'<div class="rpv-reading-slot"><span class="rpv-reading-label">'+(s.cat==="opinion"?"Exchange":"In Depth")+'</span>'+card(s,"reading")+'</div>').join("");
  }
  function getInitialHours(source){
    const a=[...source.querySelectorAll(".home-24h-card")];
    return a.map(n=>({href:n.href,kind:n.dataset.desk||"",title:plain(n.querySelector("h3")?.textContent),date:n.querySelector("time")?.dateTime||""}))
      .filter(x=>x.href&&x.title&&/^(india|world)$/.test(x.kind));
  }
  function renderHours(home,items){
    const list=home.querySelector("#rpv-edition-list");if(!list)return;
    const safe=[...new Map(items.map(x=>[internal(x.href),x])).values()].slice(0,6);
    list.innerHTML=safe.map(x=>'<a class="rpv-edition" data-desk="'+escape(x.kind)+'" href="'+escape(x.href)+'"><span class="rpv-edition-meta">'+escape(x.kind==="world"?"World":"India")+' · '+escape((x.date||"").slice(0,10))+'</span><span class="rpv-edition-title">'+escape(x.title)+'</span></a>').join("");
    applyHourFilter(home);
  }
  function applyHourFilter(home){
    const selected=home.querySelector(".rpv-hour-tab[aria-selected=true]")?.dataset.tab||"all";
    home.querySelectorAll(".rpv-edition").forEach(el=>{el.hidden=selected!=="all"&&el.dataset.desk!==selected;});
  }
  async function updateHours(home,initial){
    try{
      const r=await fetch("/24-hours/",{cache:"no-store",credentials:"same-origin"});
      if(!r.ok)throw new Error("Archive unavailable");
      const dom=new DOMParser().parseFromString(await r.text(),"text/html"),found=[];
      for(const a of dom.querySelectorAll('a[href*="/24-hours/"]')){
        const path=internal(a.href);
        const m=path.match(/^\/24-hours\/(india-news|world-news)\/(\d{4}-\d{2}-\d{2})\/$/);
        if(!m)continue;
        const title=plain(a.querySelector("h2,h3")?.textContent||a.textContent);
        if(title.length<12)continue;
        found.push({href:a.href,kind:m[1]==="india-news"?"india":"world",date:m[2],title:title.replace(/^(India|World)( in 24 Hours)?\s*[—–-]\s*\d{1,2}\s+\w+\s+\d{4}\s*:\s*/i,"")});
      }
      if(found.length){
        found.sort((a,b)=>b.date.localeCompare(a.date) || (a.kind==="india"?1:-1));
        renderHours(home,found);
      }
    }catch(e){renderHours(home,initial);}
  }
  async function updateBrief(home){
    try{
      const r=await fetch("/power-brief/",{cache:"no-store",credentials:"same-origin"});
      if(!r.ok)return;
      const dom=new DOMParser().parseFromString(await r.text(),"text/html");
      const links=[...dom.querySelectorAll('a[href*="/power-brief/"]')].map(a=>({a,match:internal(a.href).match(/^\/power-brief\/(\d{4}-\d{2}-\d{2})\/$/)})).filter(x=>x.match);
      if(!links.length)return;
      links.sort((a,b)=>b.match[1].localeCompare(a.match[1]));
      const top=links[0],date=top.match[1],label=new Date(date+"T12:00:00Z").toLocaleDateString("en-IN",{day:"numeric",month:"long",year:"numeric",timeZone:"UTC"});
      const title=plain(top.a.textContent);
      const link=home.querySelector("#rpv-power-link"),dateNode=home.querySelector("#rpv-power-date"),titleNode=home.querySelector("#rpv-power-title");
      if(title&&link){link.href=top.a.href;titleNode.textContent=title;if(dateNode)dateNode.textContent=label;}
    }catch(e){}
  }
  function startup(){
    if(document.querySelector(SELECTED))return;
    const main=document.getElementById("main");
    if(!main)return;
    const initial=getInitialHours(main),storyData=getStories(main);
    if(!storyData.lead)return;
    const legacy=document.createElement("div");
    legacy.className="rp-home-v4-legacy";
    legacy.setAttribute("aria-hidden","true");
    while(main.firstChild)legacy.appendChild(main.firstChild);
    const home=document.createElement("div");
    home.id="rp-home-v4";
    home.innerHTML=
      '<div class="rpv-grid">'+
        '<section class="rpv-lead" id="rpv-lead" aria-label="Lead reporting"></section>'+
        '<aside class="rpv-side" aria-label="Daily editions and games">'+
          '<section class="rpv-hours" aria-labelledby="rpv-hours-heading">'+
            '<div class="rpv-side-head"><h2 id="rpv-hours-heading">24 Hours</h2><a href="/24-hours/">All editions</a></div>'+
            '<div class="rpv-hour-controls" role="tablist" aria-label="24 Hours filters">'+
              '<button type="button" class="rpv-hour-tab" data-tab="all" role="tab" aria-selected="true">Latest</button>'+
              '<button type="button" class="rpv-hour-tab" data-tab="india" role="tab" aria-selected="false">India</button>'+
              '<button type="button" class="rpv-hour-tab" data-tab="world" role="tab" aria-selected="false">World</button>'+
            '</div><div id="rpv-edition-list"></div></section>'+
          '<section class="rpv-games" aria-labelledby="rpv-games-heading">'+
            '<div class="rpv-side-head"><h2 id="rpv-games-heading">Games of the Day</h2><a href="/games/">All games</a></div>'+
            '<a class="rpv-sudoku" href="/games/sudoku/"><img src="/images/games/sudoku.webp" alt="" width="76" height="76" loading="lazy"><span><strong>Today’s Sudoku</strong><small>Play the daily grid</small></span></a>'+
            '<a class="rpv-game-link" href="/games/word-riddle/">Word Riddle</a>'+
            '<a class="rpv-game-link" href="/games/daily-quiz/">Daily Knowledge Quiz</a>'+
          '</section>'+
        '</aside>'+
        '<section class="rpv-brief" aria-labelledby="rpv-brief-heading">'+
          '<div class="rpv-brief-name"><h2 id="rpv-brief-heading">The Power Brief</h2><span id="rpv-power-date">18 November 2026</span></div>'+
          '<a id="rpv-power-link" href="/power-brief/2026-11-18/"><strong id="rpv-power-title">Quad leaders announce first maritime patrol-aircraft exercise for 2027</strong><span>Read the latest morning edition</span></a>'+
        '</section>'+
        '<div class="rpv-desks">'+
          '<section class="rpv-desk"><div class="rpv-section-head"><h2>Business</h2><a href="/business/">All business</a></div><div class="rpv-card-grid" id="rpv-business"></div></section>'+
          '<section class="rpv-desk"><div class="rpv-section-head"><h2>Governance</h2><a href="/governance/">All governance</a></div><div class="rpv-card-grid" id="rpv-governance"></div></section>'+
          '<section class="rpv-desk"><div class="rpv-section-head"><h2>In Depth &amp; Exchange</h2><a href="/in-depth/">Explore reporting</a></div><div class="rpv-reading-grid" id="rpv-readings"></div></section>'+
        '</div>'+
        '<section class="rpv-research" aria-labelledby="rpv-research-heading"><div class="rpv-section-head"><h2 id="rpv-research-heading">Research Tools</h2><a href="/research-directory/">All research</a></div>'+
          '<div class="rpv-research-scroll" tabindex="0" aria-label="Scrollable research tools">'+tools.map(t=>
              '<a class="rpv-tool" href="'+escape(t[2])+'"><strong>'+escape(t[0])+'</strong><span>'+escape(t[1])+'</span></a>'
          ).join("")+'</div></section>'+
      '</div>';
    main.append(home,legacy);
    renderStories(legacy,home);
    renderHours(home,initial);
    updateHours(home,initial);
    updateBrief(home);
    home.querySelector(".rpv-hour-controls").addEventListener("click",e=>{
      const b=e.target.closest("button[data-tab]");
      if(!b)return;
      home.querySelectorAll(".rpv-hour-tab").forEach(t=>t.setAttribute("aria-selected",String(t===b)));
      applyHourFilter(home);
    });
    let previous=storyData.items.map(x=>x.href+"|"+x.title).join(";");
    let refresh=0;
    const observer=new MutationObserver(()=>{
      clearTimeout(refresh);
      refresh=setTimeout(()=>{
        const newer=getStories(legacy),fingerprint=newer.items.map(x=>x.href+"|"+x.title).join(";");
        if(fingerprint!==previous){previous=fingerprint;renderStories(legacy,home);}
      },400);
    });
    observer.observe(legacy,{subtree:true,childList:true,characterData:true});
    document.documentElement.classList.add("rp-home-v4-active");
  }
  if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",startup,{once:true});
  else startup();
})();