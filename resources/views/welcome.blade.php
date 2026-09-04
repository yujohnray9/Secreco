  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CVAARRD — Cagayan Valley Agricultural &amp; Aquatic Resources Consortium</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

    :root{
      --cream:#faf6ec;
      --paper:#f3ede0;
      --forest:#173820;
      --leaf:#2d6a30;
      --leaf-light:#4a8a4d;
      --harvest:#c9a24b;
      --harvest-light:#e4c888;
      --soil:#8a5a34;
      --ink:#152016;
      --muted:#5c6b5c;
      --line:#e2dbc9;
      --white:#ffffff;
    }

    body{
      font-family:'Inter',sans-serif;
      background:var(--cream);
      color:var(--ink);
      height:100vh;
      overflow:hidden;
      display:flex;
      flex-direction:column;
      -webkit-font-smoothing:antialiased;
    }

    /* ============ HEADER ============ */
    header{
      background:var(--white);
      border-bottom:1px solid var(--line);
      padding:12px 36px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      height:68px;
      flex-shrink:0;
      z-index:40;
      position:relative;
    }
    .header-left{display:flex;align-items:center;gap:14px;}
    .logo-box{
      width:44px;height:44px;background:#ffffff;border:1.5px solid var(--line);border-radius:50%;
      display:flex;align-items:center;justify-content:center;overflow:hidden;padding:4px;flex-shrink:0;
      box-shadow:0 2px 6px rgba(0,0,0,.06);
    }
    .logo-box img{width:100%;height:100%;object-fit:contain;}
    .brand-info h1{font-family:'Poppins',sans-serif;font-size:16px;font-weight:700;color:var(--forest);line-height:1.2;}
    .brand-info p{font-size:11px;color:var(--muted);margin-top:2px;}

    .header-right{display:flex;align-items:center;gap:18px;}
    .stat-badge{text-align:right;padding-right:18px;border-right:1px solid var(--line);}
    .stat-badge .num{font-family:'Poppins',sans-serif;font-size:20px;font-weight:700;color:var(--leaf);line-height:1;}
    .stat-badge .lbl{font-size:10.5px;color:var(--muted);margin-top:2px;}

    .header-btn{padding:8px 18px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;transition:.2s;cursor:pointer;display:inline-flex;align-items:center;gap:6px;}
    .header-btn.secondary{background:var(--white);border:1px solid #cbd5c9;color:var(--ink);}
    .header-btn.secondary:hover{border-color:var(--leaf);color:var(--leaf);}
    .header-btn.primary{background:var(--leaf);color:#fff;border:1px solid var(--leaf);}
    .header-btn.primary:hover{background:var(--forest);}

    /* ============ MAIN ============ */
    main{
      flex:1;
      display:grid;
      grid-template-columns:280px 1fr;
      overflow:hidden;
      height:calc(100vh - 68px);
    }

    /* ============ SIDEBAR ============ */
    .sidebar{
      background:var(--forest);
      color:#fff;
      padding:32px 24px;
      overflow-y:auto;
      display:flex;
      flex-direction:column;
      gap:28px;
    }
    .sidebar::-webkit-scrollbar{width:4px;}
    .sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.25);border-radius:4px;}
    .sidebar-section{
      display:flex;
      flex-direction:column;
      gap:10px;
    }
    .sidebar-section + .sidebar-section {
      border-top:1px solid rgba(255,255,255,.18);
      padding-top:24px;
    }
    .sidebar-label{
      font-size:12px;
      font-weight:700;
      text-transform:uppercase;
      letter-spacing:.12em;
      color:#facc15;
      display:flex;
      align-items:center;
      gap:7px;
    }
    .sidebar-label.mission-lbl{
       color:#facc15;
    }
    .sidebar-content{
      font-size:13.5px;
      line-height:1.7;
      opacity:.95;
      color:rgba(255,255,255,0.95);
    }

    /* ============ SHOWCASE ============ */
    .showcase{
      flex:1;
      width:100%;
      position:relative;
      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:center;
      gap:16px;
      padding:20px;
      overflow:hidden;
      background:
        radial-gradient(circle at 50% 45%, var(--paper) 0%, var(--cream) 62%);
    }

    .showcase-heading{text-align:center;}
    .showcase-eyebrow{
      font-size:10.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--harvest);
      display:flex;align-items:center;justify-content:center;gap:8px;
    }
    .showcase-eyebrow::before,.showcase-eyebrow::after{content:'';width:22px;height:1px;background:var(--harvest);}
    .showcase-title{
      font-family:'Poppins',sans-serif;font-weight:700;color:var(--forest);
      font-size:clamp(18px,2.4vw,26px);margin-top:6px;
    }
    .showcase-sub{font-size:12px;color:var(--muted);margin-top:4px;}

    /* ---- Badge / Orbit ---- */
    .orbit-wrap{
      position:relative;
      --D:min(72vh,54vw,560px);
      width:var(--D);
      aspect-ratio:1/1;
      --radius:calc(var(--D) * 0.448);
    }

    .orbit-ring{
      position:absolute;inset:0;
      z-index:3;
      animation:spinRing 45s linear infinite;
    }
    .orbit-wrap:hover .orbit-ring,
    .orbit-wrap:hover .orbit-counter{
      animation-play-state:paused;
    }

    @keyframes spinRing{from{transform:rotate(0deg);}to{transform:rotate(360deg);}}
    @keyframes spinCounter{from{transform:rotate(0deg);}to{transform:rotate(-360deg);}}

    .orbit-item{
      position:absolute;top:50%;left:50%;
      width:0;height:0;
      transform:rotate(var(--angle)) translateY(calc(-1 * var(--radius)));
    }
    .orbit-counter{
      animation:spinCounter 45s linear infinite;
      transform:translate(-50%,-50%) rotate(calc(-1 * var(--angle)));
    }

    .disc{
      position:relative;
      width:38px;height:38px;
      background:#ffffff;
      border-radius:50%;
      border:2px solid rgba(255,255,255,0.85);
      display:flex;align-items:center;justify-content:center;
      overflow:hidden;
      box-shadow:0 3px 10px rgba(0,0,0,.25);
      cursor:pointer;
      padding:2.5px;
      transition:transform .2s ease, box-shadow .2s ease;
    }

    .disc:hover{
      transform:scale(1.32);
      box-shadow:0 8px 22px rgba(0,0,0,.45);
      border-color:#ffffff;
      z-index:20;
    }

    .disc img{
      width:100%;height:100%;
      object-fit:contain;
      display:block;
      position:relative;
      z-index:2;
      border-radius:50%;
    }
    .disc-fallback{
      position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
      font-family:'Poppins',sans-serif;font-size:11px;font-weight:700;color:var(--forest);
      background:#f0ede4;border-radius:50%;z-index:1;
    }

    /* ---- Central Badge ---- */
    .badge-ring-bg{
      position:absolute;inset:-2%;
      border-radius:50%;
      z-index:1;
      background:linear-gradient(160deg, var(--leaf) 0%, var(--forest) 100%);
      box-shadow:
        0 18px 44px rgba(23,56,32,.3),
        inset 0 0 0 5px rgba(255,255,255,.14),
        inset 0 -8px 20px rgba(0,0,0,.22);
    }
    .badge-photo{
      position:absolute;inset:13%;
      border-radius:50%;
      overflow:hidden;
      z-index:2;
      background:#ffffff;
      box-shadow:
        inset 0 0 0 3px rgba(255,255,255,.4),
        0 4px 14px rgba(0,0,0,.3);
    }
    .badge-photo img{
      width:100%;height:100%;
      object-fit:cover;
      object-position:center;
      display:block;
    }
    .badge-emblem{
      position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
      width:22%;aspect-ratio:1/1;
      background:#ffffff;
      border-radius:50%;
      overflow:hidden;
      border:2.5px solid var(--leaf);
      box-shadow:0 5px 16px rgba(0,0,0,.35);
      z-index:4;
      display:flex;align-items:center;justify-content:center;
      padding:8px;
      pointer-events:none;
    }
    .badge-emblem img{
      width:100%;height:100%;
      object-fit:contain;
      display:block;
    }

    /* legend row under orbit for small screens fallback */
    .mobile-legend{display:none;}

    /* ============ MODAL ============ */
    .modal-overlay{
      position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:100;
      display:flex;align-items:center;justify-content:center;padding:20px;
      opacity:0;visibility:hidden;transition:all .2s ease;
    }
    .modal-overlay.active{opacity:1;visibility:visible;}
    .modal-card{
      background:#fff;border:1px solid var(--line);border-radius:12px;width:100%;max-width:440px;
      padding:28px;box-shadow:0 20px 40px rgba(0,0,0,.15);
      transform:scale(.95) translateY(10px);transition:all .2s ease;position:relative;
    }
    .modal-overlay.active .modal-card{transform:scale(1) translateY(0);}
    .modal-close{
      position:absolute;top:18px;right:18px;width:28px;height:28px;background:#f1f5f9;border:none;
      border-radius:50%;color:var(--muted);cursor:pointer;display:flex;align-items:center;justify-content:center;
      font-size:16px;transition:.2s;
    }
    .modal-close:hover{background:#e2e8f0;color:var(--ink);}
    .modal-header{display:flex;align-items:center;gap:16px;margin-bottom:16px;}
    .modal-logo{width:56px;height:56px;background:#fff;border:1px solid var(--line);border-radius:50%;padding:6px;object-fit:contain;}
    .modal-badge{
      display:inline-block;padding:3px 8px;background:#eaf3eb;border:1px solid #c7e6cc;border-radius:4px;
      font-size:9.5px;font-weight:700;color:var(--leaf);text-transform:uppercase;margin-bottom:4px;
    }
    .modal-title{font-family:'Poppins',sans-serif;font-size:15px;font-weight:700;color:var(--ink);line-height:1.3;}
    .modal-sub{font-size:11px;color:var(--muted);margin-top:2px;}
    .modal-desc{
      font-size:12px;line-height:1.6;color:#334155;margin-bottom:20px;background:#faf6ec;
      padding:14px;border-radius:8px;border:1px solid var(--line);
    }
    .modal-actions{display:flex;gap:10px;}
    .modal-btn{flex:1;padding:10px;border-radius:6px;font-size:12px;font-weight:600;text-align:center;text-decoration:none;cursor:pointer;transition:.2s;}
    .modal-btn.primary{background:var(--leaf);color:#fff;}
    .modal-btn.primary:hover{background:var(--forest);}
    .modal-btn.secondary{background:#f1f5f9;color:#334155;border:1px solid var(--line);}
    .modal-btn.secondary:hover{background:#e2e8f0;}

    @media (max-width: 1024px) {
      body {
        height: auto;
        min-height: 100vh;
        overflow-y: auto;
      }
      header {
        padding: 12px 18px;
        height: auto;
        gap: 12px;
      }
      main {
        grid-template-columns: 1fr;
        height: auto;
        overflow: visible;
      }
      .sidebar {
        display: none;
      }
      .showcase {
        padding: 24px 14px;
        min-height: 460px;
      }
      .orbit-wrap {
        --D: min(84vw, 380px);
      }
      .disc {
        width: 32px;
        height: 32px;
      }
    }

    @media (max-width: 640px) {
      header {
        padding: 10px 14px;
      }
      .brand-info p {
        display: none;
      }
      .brand-info h1 {
        font-size: 14px;
      }
      .stat-badge {
        display: none;
      }
      .orbit-wrap {
        --D: min(88vw, 320px);
      }
      .disc {
        width: 28px;
        height: 28px;
      }
      .hub-disc {
        width: 58px;
        height: 58px;
      }
      .hub-disc img {
        width: 38px;
        height: 38px;
      }
      .modal-card {
        padding: 20px 16px;
      }
    }
  </style>
  </head>
  <body>

  <header>
    <div class="header-left">
      <div class="logo-box"><img src="/assets/logo/cvaarrd.jpeg" alt="CVAARRD" onerror="this.src='/assets/img/cvaarrd.png'"/></div>
      <div class="brand-info">
        <h1>CVAARRD Consortium</h1>
        <p>Cagayan Valley Agriculture Aquatic and Natural Resources Research and Development Consortium</p>
      </div>
    </div>
    <div class="header-right">
      <div class="stat-badge">
        <div class="num">22</div>
        <div class="lbl">Member Institutions</div>
      </div>
      <a href="/login" class="header-btn secondary">Log In</a>
      <a href="/login#register" class="header-btn primary">Get Started</a>
    </div>
  </header>

  <main>
    <aside class="sidebar">
      <div class="sidebar-section">
        <div class="sidebar-label">
          Vision
        </div>
        <div class="sidebar-content">A prime research and technology management consortium in agriculture, aquatic and natural resources for an improved quality of life.</div>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-label mission-lbl">
          Mission
        </div>
        <div class="sidebar-content">Provide high quality leadership and policy direction focusing and harmonizing the R&amp;D thrusts and capabilities of various institutions for the well-being of the people and sustainable development in the Cagayan Valley.</div>
      </div>
    </aside>

    <section class="showcase">
      <div class="showcase-heading">
        <div class="showcase-eyebrow">Consortium at a glance</div>
        <div class="showcase-title">Twenty-Two Institutions, One Mandate</div>
      </div>

      <div class="orbit-wrap" id="orbitWrap">
        <div class="badge-ring-bg"></div>

        <div class="badge-photo">
          <img src="/assets/logo/Hands.jpg" alt="CVAARRD member institutions, hands together"
              onerror="this.src='/assets/img/hands-huddle.jpg'"/>
        </div>

        <div class="orbit-ring" id="orbitRing"></div>

        <div class="badge-emblem">
          <img src="/assets/logo/cvaarrd.jpeg" alt="CVAARRD" onerror="this.src='/assets/img/cvaarrd.png'"/>
        </div>
      </div>
    </section>
  </main>

  <div class="modal-overlay" id="inst-modal">
    <div class="modal-card">
      <button class="modal-close" id="modal-close-btn">&times;</button>
      <div class="modal-header">
        <img src="" alt="" class="modal-logo" id="modal-logo-img" onerror="this.style.visibility='hidden'"/>
        <div>
          <span class="modal-badge" id="modal-badge-tag">Category</span>
          <h3 class="modal-title" id="modal-title-text">Institution Name</h3>
          <div class="modal-sub" id="modal-sub-text">Location</div>
        </div>
      </div>
      <div class="modal-desc" id="modal-desc-text">Institution profile description here.</div>
      <div class="modal-actions">
        <button class="modal-btn secondary" id="modal-cancel-btn">Close</button>
        <a href="/login" class="modal-btn primary" id="modal-login-link">Proceed to Portal ➔</a>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', () => {

    // ---- Institution data (all 22 members) mapped to public/assets/logo/ ----
    const institutions = [
      { name:'Isabela State University - Echague', sub:'Echague, Isabela', logo:'isu logo new.png', tag:'State University', category:'academic', desc:'Main campus leading agro-industrial R&D, smart agriculture, and forestry innovations in Isabela.' },
      { name:'Isabela State University - Cabagan', sub:'Cabagan, Isabela', logo:'isu logo new.png', tag:'State University', category:'academic', desc:'Pioneer in environmental science, agroforestry, and biodiversity research.' },
      { name:'Batanes State College', sub:'Basco, Batanes', logo:'1. Batanes_State_College.png', tag:'State College', category:'academic', desc:'Island heritage institute focusing on island agriculture, fisheries, and renewable resources.' },
      { name:'Cagayan State University', sub:'Tuguegarao, Cagayan', logo:'3. CSU.png', tag:'State University', category:'academic', desc:'Multi-campus university driving agricultural engineering and aquatic research in northern Cagayan.' },
      { name:'Nueva Vizcaya State University', sub:'Bayombong, NV', logo:'5. Nueva_Vizcaya_State_University.png', tag:'State University', category:'academic', desc:'Center of excellence in highland agriculture, citrus production, and environmental management.' },
      { name:'Quirino State University', sub:'Diffun, Quirino', logo:'6. Quirino_State_University.jpeg', tag:'State University', category:'academic', desc:'Leader in organic farming, agro-ecotourism, and indigenous tree conservation.' },
      { name:'University of La Salette', sub:'Santiago City', logo:'20. La salette.png', tag:'Private University', category:'academic', desc:'Premier private higher education institution collaborating on community-based development.' },
      { name:'CHED - Regional Office 2', sub:'Regional Office II', logo:'18. CHED.png', tag:'Higher Education', category:'academic', desc:'Commission on Higher Education overseeing tertiary academic R&D excellence in Region II.' },
      { name:'Watershed & Water Resources Dev Center', sub:'Region II Center', logo:'9. WWRRDEC.png', tag:'R&D Center', category:'partner', desc:'Dedicated to watershed dynamics, freshwater ecosystem sustainability, and soil preservation.' },
      { name:'Mabuwaya Foundation Inc.', sub:'San Mariano, Isabela', logo:'10. Mabuwaya-Foundation.png', tag:'NGO Partner', category:'partner', desc:'World-renowned NGO conserving the Philippine Crocodile and critical riverine habitats.' },
      { name:'Government City of Santiago', sub:'Santiago City', logo:'12. Santiago City.png', tag:'LGU Partner', category:'partner', desc:'Independent component city providing institutional synergy for agricultural commercialization.' },
      { name:'DA - Regional Field Office 2', sub:'Tuguegarao, Cagayan', logo:'4. DA.png', tag:'DA Regional', category:'agency', desc:'Department of Agriculture driving crop production, livestock, and farm modernization.' },
      { name:'DOST - Regional Office II', sub:'Tuguegarao City', logo:'19. DOST.png', tag:'DOST Regional', category:'agency', desc:'Leading science & technology transfers, innovation funding, and laboratory testing centers.' },
      { name:'BFAR - Regional Office 2', sub:'Tuguegarao City', logo:'13. BFAR.png', tag:'Fisheries R2', category:'agency', desc:'Promoting sustainable aquaculture, marine sanctuary protection, and fisherfolk livelihood.' },
      { name:'DENR - Regional Office II', sub:'Tuguegarao City', logo:'16. DENR.png', tag:'DENR Regional', category:'agency', desc:'Safeguarding forest lands, protected areas, biodiversity, and clean environmental policies.' },
      { name:'DA - ATI Region II', sub:'San Mateo, Isabela', logo:'7. DA-ATI.png', tag:'Training Inst.', category:'agency', desc:'The apex extension arm cultivating modern agricultural training and advisory services.' },
      { name:'DA - PhilRice Isabela', sub:'San Mateo, Isabela', logo:'11. PHILRICE.png', tag:'PhilRice', category:'agency', desc:'National institute pioneering climate-smart rice varieties and sustainable grain technologies.' },
      { name:'PCAARRD - DOST', sub:'Apex National Council', logo:'PCAARRD.png', tag:'Apex Council', category:'agency', desc:'Apex national council formulating policies and funding strategic R&D consortia.' },
      { name:'DA - Bureau of Agricultural Research', sub:'National Bureau', logo:'15. DA-BAR.png', tag:'BAR Bureau', category:'agency', desc:'Consolidating and funding strategic agricultural and fisheries research programs nationwide.' },
      { name:'DTI - Regional Office II', sub:'Tuguegarao City', logo:'2. DTI.png', tag:'Trade & Industry', category:'agency', desc:'Empowering MSMEs, agro-enterprise incubation, and value chain market linkages.' },
      { name:'NEDA - Regional Office II', sub:'Tuguegarao City', logo:'14. National_Economic_and_Development_Authority_(NEDA).svg.png', tag:'NEDA Regional', category:'agency', desc:'Socio-economic planning and investment coordination for sustainable regional growth.' },
      { name:'National Tobacco Administration', sub:'Ilagan, Isabela', logo:'17. NTA.png', tag:'NTA Agency', category:'agency', desc:'Promoting industrial diversification, farmer safety nets, and crop development.' }
    ];

    function initials(name){
      return name.split(/[\s-]+/).filter(Boolean).slice(0,2).map(w=>w[0]).join('').toUpperCase();
    }

    // ---- Build orbit ----
    const ring = document.getElementById('orbitRing');
    const total = institutions.length;

    institutions.forEach((inst, i) => {
      const angle = (360 / total) * i;

      const item = document.createElement('div');
      item.className = 'orbit-item';
      item.style.setProperty('--angle', angle + 'deg');
      item.dataset.category = inst.category;
      item.dataset.name = inst.name;
      item.dataset.sub = inst.sub;
      item.dataset.tag = inst.tag;
      item.dataset.desc = inst.desc;
      item.dataset.logo = inst.logo;

      item.innerHTML = `
        <div class="orbit-counter">
          <div class="disc" title="${inst.name}">
            <span class="disc-fallback">${initials(inst.name)}</span>
            <img src="/assets/logo/${encodeURI(inst.logo)}" alt="${inst.name}" onerror="this.style.display='none'"/>
          </div>
        </div>`;

      ring.appendChild(item);
    });

    // ---- Modal ----
    const modal = document.getElementById('inst-modal');
    const modalClose = document.getElementById('modal-close-btn');
    const modalCancel = document.getElementById('modal-cancel-btn');
    const modalLogo = document.getElementById('modal-logo-img');
    const modalTitle = document.getElementById('modal-title-text');
    const modalSub = document.getElementById('modal-sub-text');
    const modalBadge = document.getElementById('modal-badge-tag');
    const modalDesc = document.getElementById('modal-desc-text');

    function openModal(data){
      modalLogo.style.visibility = 'visible';
      modalLogo.src = '/assets/logo/' + encodeURI(data.logo);
      modalTitle.textContent = data.name;
      modalSub.textContent = data.sub;
      modalBadge.textContent = data.tag;
      modalDesc.textContent = data.desc;
      modal.classList.add('active');
    }
    function closeModal(){ modal.classList.remove('active'); }

    modalClose.addEventListener('click', closeModal);
    modalCancel.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    ring.addEventListener('click', e => {
      const item = e.target.closest('.orbit-item');
      if (!item) return;
      openModal(item.dataset);
    });
  });
  </script>

  </body>
  </html>