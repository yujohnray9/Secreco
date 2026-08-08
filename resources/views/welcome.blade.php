<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CVAARRD — Member Institutions</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --green: #3d7a3f; --green-dark: #2d5a2f; --yellow: #c9a961;
      --white: #ffffff; --bg: #f5f5f5; --text: #1a1a1a; --text-light: #666666;
    }
    body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); height: 100vh; overflow: hidden; display: flex; flex-direction: column; }

    /* HEADER */
    header { background: var(--white); border-bottom: 1px solid #e0e0e0; padding: 20px 48px; display: flex; align-items: center; justify-content: space-between; animation: slideDown 0.5s ease both; }
    .header-left { display: flex; align-items: center; gap: 16px; }
    .logo-box { width: 48px; height: 48px; background: var(--green); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 20px; color: var(--white); overflow: hidden; }
    .logo-box img { width: 100%; height: 100%; object-fit: contain; }
    .brand-info h1 { font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 700; color: var(--green); margin: 0; }
    .brand-info p { font-size: 11px; color: var(--text-light); margin: 2px 0 0; }
    .header-right { display: flex; align-items: center; gap: 16px; }
    .stat-badge { text-align: center; padding: 0 16px; border-right: 1px solid #e0e0e0; }
    .stat-badge .num { font-size: 24px; font-weight: 700; color: var(--green); }
    .stat-badge .lbl { font-size: 10px; color: var(--text-light); margin-top: 2px; }
    .header-btn { padding: 8px 18px; border: 1px solid #d0d0d0; background: var(--white); color: var(--text); border-radius: 4px; font-weight: 500; font-size: 12px; cursor: pointer; text-decoration: none; transition: all 0.2s; }
    .header-btn:hover { border-color: var(--green); color: var(--green); }
    .header-btn.primary { background: var(--green); color: var(--white); border-color: var(--green); }
    .header-btn.primary:hover { background: var(--green-dark); }

    /* MAIN */
    main { flex: 1; display: grid; grid-template-columns: 280px 1fr; overflow: hidden; animation: fadeUp 0.6s ease 0.1s both; }
    .sidebar { background: var(--green); color: var(--white); padding: 32px 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 24px; }
    .sidebar-title { font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 700; margin: 0; }
    .sidebar-text { font-size: 12px; line-height: 1.6; opacity: 0.9; margin: 0; }
    .sidebar-section { border-top: 1px solid rgba(255,255,255,0.2); padding-top: 20px; }
    .sidebar-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.8; margin-bottom: 12px; }
    .sidebar-content { font-size: 12px; line-height: 1.7; opacity: 0.95; }
    .content-area { background: var(--white); padding: 32px 48px; overflow-y: auto; display: flex; flex-direction: column; gap: 32px; }
    .section-title { font-family: 'Poppins', sans-serif; font-size: 24px; font-weight: 700; color: var(--green); margin: 0; }
    .cta-btn { display: inline-block; background: var(--green); color: var(--white); padding: 12px 32px; border-radius: 4px; font-weight: 600; text-decoration: none; transition: all 0.2s; font-size: 12px; width: fit-content; }
    .cta-btn:hover { background: var(--green-dark); }
    .members-header { font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 10px; margin: 0; }
    .members-header::before { content: ''; width: 3px; height: 22px; background: var(--yellow); border-radius: 1px; }
    .institutions-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .inst-card { background: var(--white); border: 1px solid #e0e0e0; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; align-items: center; text-align: center; text-decoration: none; transition: all 0.2s; }
    .inst-card:hover { border-color: var(--yellow); background: #fffef9; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(201,169,97,0.1); }
    .inst-logo { width: 48px; height: 48px; background: var(--green); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; overflow: hidden; font-weight: 700; font-size: 16px; color: var(--white); }
    .inst-logo img { width: 100%; height: 100%; object-fit: contain; }
    .inst-name { font-size: 11px; font-weight: 500; color: var(--text); line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    @keyframes slideDown { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:translateY(0)} }
    @keyframes fadeUp    { from{opacity:0;transform:translateY(14px)}  to{opacity:1;transform:translateY(0)} }
  </style>
</head>
<body>

<header>
  <div class="header-left">
    <div class="logo-box">
      <img src="/assets/img/cvaarrd.png" alt="CVAARRD" onerror="this.style.display='none'"/>
    </div>
    <div class="brand-info">
      <h1>CVAARRD Consortium Office</h1>
      <p>Cagayan Valley Agricultural and Aquatic Natural Resources Research and Development</p>
    </div>
  </div>
  <div class="header-right">
    <div class="stat-badge">
      <div class="num">22</div>
      <div class="lbl">Member Institutions</div>
    </div>
    <a href="/login" class="header-btn">Log In</a>
    <a href="/login#register" class="header-btn primary">Get Started</a>
  </div>
</header>

<main>
  <div class="sidebar">
    <div>
      <h3 class="sidebar-title">About CVAARRD</h3>
      <p class="sidebar-text">A prime research and technology management consortium dedicated to advancing agriculture, aquatic, and natural resources research and development in Cagayan Valley.</p>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Vision</div>
      <div class="sidebar-content">A prime research and technology management consortium and agriculture, aquatic and natural resources for an improved quality of life.</div>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Mission</div>
      <div class="sidebar-content">Provide high quality leadership and policy direction focusing and harmonizing the R&amp;D thrusts and capabilities of various institutions for the well-being of the people and sustainable development in the Cagayan Valley.</div>
    </div>
  </div>

  <div class="content-area">
    <h2 class="section-title">Member Institutions</h2>
    <div>
      <h3 class="members-header">Regional Partner Institutions</h3>
      <div class="institutions-grid">
        @php
        $institutions = [
          ['name' => 'Isabela State University - Echague',    'logo' => 'logo1.jpg'],
          ['name' => 'Isabela State University - Cabagan',    'logo' => 'logo1.jpg'],
          ['name' => 'Batanes State College',                 'logo' => 'logo3.jpg'],
          ['name' => 'Cagayan State University',              'logo' => 'logo4.jpg'],
          ['name' => 'Nueva Vizcaya State University',        'logo' => 'logo5.jpg'],
          ['name' => 'Quirino State University',              'logo' => 'logo6.jpg'],
          ['name' => 'University of La Salette',              'logo' => 'logo7.jpg'],
          ['name' => 'DA - Agricultural Training Institute Region II', 'logo' => 'logo8.jpg'],
          ['name' => 'DA - Regional Field Office 2',          'logo' => 'logo9.jpg'],
          ['name' => 'Bureau of Fisheries & Aquatic Resources - R2', 'logo' => 'logo10.jpg'],
          ['name' => 'Department of Environment and Natural Resources - Region II', 'logo' => 'logo11.jpg'],
          ['name' => 'Department of Science and Technology - Region II', 'logo' => 'logo12.jpg'],
          ['name' => 'Department of Trade and Industry - Region II', 'logo' => 'logo13.jpg'],
          ['name' => 'Department of Economy, Planning and Development - Region II', 'logo' => 'logo14.jpg'],
          ['name' => 'National Tobacco Administration',       'logo' => 'logo15.jpg'],
          ['name' => 'DA - Philippine Rice Research Institute - Isabela', 'logo' => 'logo16.jpg'],
          ['name' => 'Philippine Council for Agriculture, Aquatic and Natural Resources Research and Development', 'logo' => 'logo17.jpg'],
          ['name' => 'DA - Bureau of Agricultural Research',  'logo' => 'logo18.jpg'],
          ['name' => 'Watershed & Water Resources Research Development and Extension Center', 'logo' => 'logo19.jpg'],
          ['name' => 'Mabuwaya Foundation Inc.',              'logo' => 'logo20.jpg'],
          ['name' => 'Government City of Santiago',           'logo' => 'logo21.jpg'],
          ['name' => 'Commission on Higher Education - Regional Office 2', 'logo' => 'logo22.jpg'],
        ];
        @endphp
        @foreach ($institutions as $inst)
          <a href="/login" class="inst-card">
            <div class="inst-logo">
              <img src="/assets/img/{{ $inst['logo'] }}" alt="" onerror="this.style.display='none'"/>
            </div>
            <div class="inst-name">{{ $inst['name'] }}</div>
          </a>
        @endforeach
      </div>
    </div>
  </div>
</main>

</body>
</html>