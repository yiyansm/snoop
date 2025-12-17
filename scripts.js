/* Rewritten scripts.js
   - Modular functions
   - IntersectionObserver reveal
   - Smooth scrolling
   - Header parallax
   - Nav active highlight
   - Optional lightweight particles (disabled by default)
*/

const CONFIG = {
  ENABLE_PARTICLES: false, // set true to enable header particles (costly on low-end devices)
  PARTICLE_DENSITY: 1/120, // particles per px of width
  REVEAL_THRESHOLD: 0.12
};

function initReveal(){
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{
      if(e.isIntersecting){
        e.target.classList.add('visible');
        io.unobserve(e.target);
      }
    });
  },{threshold: CONFIG.REVEAL_THRESHOLD});

  document.querySelectorAll('.fade-in, tbody tr').forEach(el=>{
    if(!el.classList.contains('fade-in')) el.classList.add('fade-in');
    io.observe(el);
  });
}

function initSmoothScroll(){
  document.querySelectorAll('a[href^="#"]').forEach(a=>{
    a.addEventListener('click', function(ev){
      const href = this.getAttribute('href');
      const target = document.querySelector(href);
      if(target){
        ev.preventDefault();
        const y = target.getBoundingClientRect().top + window.scrollY - 20;
        window.scrollTo({top: y, behavior: 'smooth'});
      }
    });
  });
}

function initParallax(){
  const header = document.querySelector('header');
  if(!header) return;
  window.addEventListener('scroll', ()=>{
    const s = window.scrollY;
    header.style.transform = `translateY(${Math.min(s * 0.05, 18)}px)`;
  }, {passive:true});
}

function initNavObserver(){
  const sections = Array.from(document.querySelectorAll('main section'));
  const navLinks = Array.from(document.querySelectorAll('nav a'));
  if(!sections.length) return;

  const navObserver = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{
      if(e.isIntersecting){
        const id = e.target.id;
        navLinks.forEach(a=> a.classList.toggle('active', a.getAttribute('href') === `#${id}`));
      }
    });
  },{threshold:0.36});

  sections.forEach(s=> navObserver.observe(s));
}

/* Mobile nav toggle */
function initMobileNav(){
  const btn = document.getElementById('nav-toggle');
  const nav = document.querySelector('nav');
  const list = document.querySelector('.nav-list');
  if(!btn || !nav || !list) return;

  btn.addEventListener('click', ()=>{
    const opened = nav.classList.toggle('open');
    btn.setAttribute('aria-expanded', String(opened));
    // animate hamburger
    btn.classList.toggle('is-open', opened);
  });

  // close menu when clicking a link
  list.querySelectorAll('a').forEach(a=> a.addEventListener('click', ()=>{
    nav.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
    btn.classList.remove('is-open');
  }));
}

/* Optional: lightweight particle field in header */
function startParticles(){
  if(!CONFIG.ENABLE_PARTICLES) return;
  const header = document.querySelector('header');
  if(!header) return;
  const canvas = document.createElement('canvas');
  canvas.className = 'header-canvas';
  canvas.style.position = 'absolute';
  canvas.style.inset = '0';
  canvas.style.pointerEvents = 'none';
  header.appendChild(canvas);
  const ctx = canvas.getContext && canvas.getContext('2d');
  if(!ctx) return;

  let DPR = window.devicePixelRatio || 1;
  function resize(){
    DPR = window.devicePixelRatio || 1;
    canvas.width = Math.floor(canvas.clientWidth * DPR);
    canvas.height = Math.floor(canvas.clientHeight * DPR);
  }

  let particles = [];
  function init(){
    resize();
    particles = [];
    const count = Math.max(6, Math.floor((canvas.clientWidth || 600) * CONFIG.PARTICLE_DENSITY));
    for(let i=0;i<count;i++){
      particles.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        r: (Math.random()*1.8 + 0.6) * DPR,
        vx: (Math.random()-0.5) * 0.25,
        vy: (Math.random()-0.5) * 0.08,
        alpha: Math.random()*0.18 + 0.06
      });
    }
  }

  let rafId;
  function draw(){
    ctx.clearRect(0,0,canvas.width,canvas.height);
    for(const p of particles){
      p.x += p.vx * DPR * 2;
      p.y += p.vy * DPR * 2;
      if(p.x < -30*DPR) p.x = canvas.width + 30*DPR;
      if(p.x > canvas.width + 30*DPR) p.x = -30*DPR;
      if(p.y < -30*DPR) p.y = canvas.height + 30*DPR;
      if(p.y > canvas.height + 30*DPR) p.y = -30*DPR;
      ctx.beginPath();
      ctx.fillStyle = `rgba(255,255,255,${p.alpha})`;
      ctx.arc(p.x, p.y, p.r, 0, Math.PI*2);
      ctx.fill();
    }
    rafId = requestAnimationFrame(draw);
  }

  window.addEventListener('resize', ()=>{ init(); });
  init();
  draw();

  // cleanup listener on unload
  window.addEventListener('pagehide', ()=>{ cancelAnimationFrame(rafId); });
}

// Mini-IDE: list and show files from pinterest-clone/files.json
async function initMiniIDE(){
  const embedded = document.getElementById('mini-ide-embedded');
  const modal = document.getElementById('mini-ide');
  const container = embedded || modal;
  if(!container) return; // nothing to mount into
  const isEmbedded = Boolean(embedded);

  const fileList = container.querySelector('#mini-ide-file-list') || document.getElementById('mini-ide-file-list');
  const codeEl = container.querySelector('#mini-ide-code') || document.getElementById('mini-ide-code');
  const currentEl = container.querySelector('#mini-ide-current') || document.getElementById('mini-ide-current');
  const searchInput = container.querySelector('#mini-ide-search') || document.getElementById('mini-ide-search');
  const downloadBtn = container.querySelector('#mini-ide-download') || document.getElementById('mini-ide-download');

  // For modal mode, keep open/close behavior
  if(!isEmbedded){
    const toggle = document.getElementById('mini-ide-toggle');
    const backdrop = modal && modal.querySelector('.mini-ide-backdrop');
    function open(){ if(modal){ modal.setAttribute('aria-hidden','false'); modal.style.display='flex'; } if(searchInput) setTimeout(()=>searchInput.focus(),80); }
    function close(){ if(modal){ modal.setAttribute('aria-hidden','true'); modal.style.display='none'; } }
    if(toggle) toggle.addEventListener('click', open);
    if(backdrop) backdrop.addEventListener('click', close);
    modal && modal.querySelectorAll('[data-action="close"]').forEach(b=> b.addEventListener('click', close));
    window.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && modal && modal.getAttribute('aria-hidden')==='false') close(); });
  }

  let files = [];
  try{
    const res = await fetch('pinterest-clone/files.json');
    if(res.ok) files = await res.json();
  }catch(e){ files = []; }

  function renderList(filter = ''){
    if(!fileList) return;
    fileList.innerHTML = '';
    const filtered = files.filter(f => f.toLowerCase().includes(filter.toLowerCase()));
    if(filtered.length === 0){
      fileList.innerHTML = '<li class="muted">No se encontraron archivos</li>';
      return;
    }
    filtered.forEach(f=>{
      const li = document.createElement('li');
      li.textContent = f;
      li.tabIndex = 0;
      li.addEventListener('click', ()=> loadFile(f, li));
      li.addEventListener('keyup', (ev)=>{ if(ev.key==='Enter') loadFile(f, li); });
      fileList.appendChild(li);
    });
  }

  async function loadFile(path, node){
    if(!fileList || !codeEl || !currentEl) return;
    fileList.querySelectorAll('li').forEach(n => n.classList.remove('active'));
    node && node.classList.add('active');
    currentEl.textContent = path;
    codeEl.textContent = 'Cargando...';
    try{
      const r = await fetch('pinterest-clone/' + path);
      if(!r.ok) throw new Error('Fetch failed');
      const text = await r.text();
      codeEl.textContent = text;
      if(downloadBtn){ downloadBtn.disabled = false; downloadBtn.onclick = ()=>{ downloadFile(path, text); }; }
    }catch(err){
      codeEl.textContent = 'Error al cargar el archivo.';
      downloadBtn && (downloadBtn.disabled = true);
    }
  }
  function downloadFile(filename, text){
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([text], { type: 'text/plain' }));
    a.download = filename.replace(/\//g,'_');
    document.body.appendChild(a);
    a.click();
    a.remove();
  }

  renderList();
  if(searchInput) searchInput.addEventListener('input', ()=> renderList(searchInput.value));

  // Mobile: add tree toggle and auto-close behavior
  if(isEmbedded){
    const treeToggle = container.querySelector('#mini-ide-tree-toggle');
    if(treeToggle){
      treeToggle.addEventListener('click', ()=>{
        const opened = embedded.classList.toggle('show-tree');
        treeToggle.setAttribute('aria-expanded', String(opened));
      });
    }
    // ensure tree closes after selecting a file on small screens
    const originalLoadFile = loadFile;
    loadFile = async function(path, node){
      await originalLoadFile(path, node);
      try{
        if(window.innerWidth <= 720){
          embedded.classList.remove('show-tree');
          const t = container.querySelector('#mini-ide-tree-toggle'); if(t) t.setAttribute('aria-expanded','false');
        }
      }catch(e){}
    }
  }
}

document.addEventListener('DOMContentLoaded', ()=>{
  initReveal();
  initSmoothScroll();
  initParallax();
  initNavObserver();
  initMobileNav();
  startParticles();
  initMiniIDE();
});