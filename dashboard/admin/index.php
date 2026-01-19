<?php
session_start();
require_once("../../config/database.php");

// Middleware - hanya admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../public/login.php");
    exit;
}

$nama_admin = htmlspecialchars($_SESSION['user']['nama_lengkap']);
$role = ucfirst($_SESSION['user']['role']);

// ==== Ambil data statistik ====
$totalAkun          = (int) query("SELECT COUNT(*) AS jml FROM users")->fetch_assoc()['jml'];
$akunApproved       = (int) query("SELECT COUNT(*) AS jml FROM users WHERE status_akun='approved'")->fetch_assoc()['jml'];
$akunPending        = (int) query("SELECT COUNT(*) AS jml FROM users WHERE status_akun='pending'")->fetch_assoc()['jml'];
$akunRejected       = (int) query("SELECT COUNT(*) AS jml FROM users WHERE status_akun='rejected'")->fetch_assoc()['jml'];

$totalPendaftar     = (int) query("SELECT COUNT(*) AS jml FROM pendaftar")->fetch_assoc()['jml'];
$pendaftarApproved  = (int) query("SELECT COUNT(*) AS jml FROM pendaftar WHERE status_pendaftaran='approved'")->fetch_assoc()['jml'];
$pendaftarPending   = (int) query("SELECT COUNT(*) AS jml FROM pendaftar WHERE status_pendaftaran='pending'")->fetch_assoc()['jml'];
$pendaftarRejected  = (int) query("SELECT COUNT(*) AS jml FROM pendaftar WHERE status_pendaftaran='rejected'")->fetch_assoc()['jml'];

// ==== Data line chart: pendaftar per bulan (tahun berjalan) ====
$currentYear = (int) date('Y');
$monthCounts = array_fill(1, 12, 0); // 1..12

$rs = query("
    SELECT MONTH(tanggal_daftar) AS m, COUNT(*) AS cnt
    FROM pendaftar
    WHERE YEAR(tanggal_daftar) = $currentYear
    GROUP BY MONTH(tanggal_daftar)
");
while ($r = $rs->fetch_assoc()) {
    $m = (int)$r['m'];
    $monthCounts[$m] = (int)$r['cnt'];
}

// For date display (e.g., Rabu, 12 November 2025)
setlocale(LC_TIME, 'id_ID.UTF-8');
$tanggal_hari_ini = strftime('%A, %d %B %Y');

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard Admin | PMB ITS NU</title>
<link rel="icon" type="image/jpeg" href="../../assets/img/ITS.jpeg">

<!-- Tailwind, Feather, Chart.js, SweetAlert -->
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">

<style>
/* --- Base --- */
body {
  font-family: 'Poppins',sans-serif;
  background: radial-gradient(circle at bottom, #0f172a 0%, #000 100%);
  color: #e6eef0;
  min-height: 100vh;
  margin: 0;
  display: flex;
  flex-direction: column;
}

/* Sidebar layout reserved (ml-64) */
.main-wrapper { display:flex; min-height:100vh; }

/* spinner */
#loading-spinner {
  position:fixed; inset:0; display:flex; align-items:center; justify-content:center;
  background: linear-gradient(180deg, rgba(2,6,23,0.85), rgba(2,6,23,0.95)); z-index:60;
}
.spinner {
  width:64px; height:64px; border-radius:50%;
  border:6px solid rgba(255,255,255,0.07); border-top-color:#10b981;
  animation:spin 1s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}

/* Sidebar placeholder styles are handled in include file */
.content {
  flex:1; margin-left:16rem; display:flex; flex-direction:column;
}

/* Header */
.header {
  position:sticky; top:0; z-index:40;
  backdrop-filter: blur(6px);
  background: rgba(17,24,39,0.75);
  border-bottom:1px solid rgba(16,185,129,0.12);
}

/* Fade-in */
.fade-in { opacity:0; transform:translateY(18px); animation:fadeIn 0.8s ease forwards; }
@keyframes fadeIn { to { opacity:1; transform:translateY(0); } }

/* Cards */
.cards-grid { display:grid; grid-template-columns: repeat(1,1fr); gap:1rem; }
@media(min-width:640px){ .cards-grid { grid-template-columns: repeat(2,1fr); } }
@media(min-width:1024px){ .cards-grid { grid-template-columns: repeat(3,1fr); } }

.stat-card {
  border-radius:14px; padding:1rem; position:relative; overflow:hidden;
  color:white; min-height:110px;
  box-shadow: 0 6px 26px rgba(2,6,23,0.6);
  transition: transform .18s ease, box-shadow .18s ease;
}
.stat-card:hover { transform:translateY(-6px) scale(1.01); box-shadow: 0 20px 50px rgba(2,6,23,0.7); }

/* neon border glow variants */
.glow-1 { background: linear-gradient(135deg,#0ea5a4 0%, #06b6d4 100%); }
.glow-2 { background: linear-gradient(135deg,#06b6d4 0%, #60a5fa 100%); }
.glow-3 { background: linear-gradient(135deg,#34d399 0%, #10b981 100%); }
.glow-4 { background: linear-gradient(135deg,#60a5fa 0%, #7c3aed 100%); }
.glow-5 { background: linear-gradient(135deg,#f59e0b 0%, #fb923c 100%); }
.glow-6 { background: linear-gradient(135deg,#f472b6 0%, #fb7185 100%); }

.stat-card .label { font-size:0.95rem; opacity:0.95; }
.stat-card .value { font-size:1.9rem; font-weight:800; margin-top:0.35rem; letter-spacing:0.5px; }

/* charts container */
.charts-wrap { display:grid; gap:1rem; grid-template-columns:1fr; }
@media(min-width:1024px){ .charts-wrap { grid-template-columns: 1fr 420px; } }

.chart-card {
  background: rgba(15,23,42,0.55); border:1px solid rgba(16,185,129,0.12);
  padding:1rem; border-radius:12px; position:relative; overflow:visible;
  box-shadow: 0 8px 30px rgba(2,6,23,0.6);
}

/* pie+line stack on small */
.side-charts { display:flex; flex-direction:column; gap:1rem; margin-top:0.5rem; }
@media(min-width:1024px){ .side-charts { margin-top:0; } }

.canvas-responsive { width:100% !important; height:auto !important; aspect-ratio: 2 / 1; display:block; }

/* small tweaks */
.small-note { color:#9ca3af; font-size:0.92rem; }
.footer { margin-top:auto; }

/* neon tooltip class for Chart.js external tooltip */
.chartjs-neon-tooltip {
  position: absolute; transform: translate(-50%, -110%);
  background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(6,95,70,0.06));
  border:1px solid rgba(16,185,129,0.16);
  padding:8px 10px; border-radius:8px; color:#e6fff9; font-size:13px; pointer-events:none; z-index:99;
}

/* adjust canvas wrappers to stay proportional */
.canvas-box { width:100%; display:block; }
.canvas-box-lg { height:360px; }
@media(min-width:1280px){ .canvas-box-lg { height:420px; } }
</style>
</head>
<body>

<!-- Loading spinner -->
<div id="loading-spinner"><div class="spinner"></div></div>

<div class="main-wrapper">
    <!-- Sidebar include -->
    <?php include __DIR__ . "/sidebar_admin.php"; ?>

    <div class="content">
        <!-- Header include -->
        <?php include __DIR__ . "/header_admin.php"; ?>

        <!-- Main area -->
        <main class="p-6 space-y-8 mt-28">
            <!-- Intro / Statistik PMB -->
            <section class="bg-gray-900/60 p-6 rounded-xl border border-emerald-600/20 shadow-lg max-w-6xl mx-auto fade-in" style="animation-delay:0.05s;">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <h2 class="text-2xl font-bold text-emerald-400 flex items-center gap-2"><i data-feather="activity"></i> Statistik PMB</h2>
                        <p class="small-note mt-1">Halo, <span class="font-semibold"><?= $nama_admin ?></span>. Ringkasan data dan tren pendaftar saat ini.</p>
                    </div>
                    <div class="text-right">
                        <div class="small-note flex items-center gap-2"><i data-feather="calendar"></i> <?= ucfirst($tanggal_hari_ini) ?></div>
                    </div>
                </div>
            </section>

            <!-- Statistik cards -->
            <section class="max-w-6xl mx-auto fade-in cards-grid" style="animation-delay:0.12s;">
                <div class="stat-card glow-1">
                    <div class="label">Total Akun</div>
                    <div class="value"><?= number_format($totalAkun) ?></div>
                    <div class="small-note mt-3">Jumlah user terdaftar di sistem.</div>
                </div>

                <div class="stat-card glow-3">
                    <div class="label">Akun Approved</div>
                    <div class="value"><?= number_format($akunApproved) ?></div>
                    <div class="small-note mt-3">Akun yang sudah diverifikasi.</div>
                </div>

                <div class="stat-card glow-5">
                    <div class="label">Akun Pending</div>
                    <div class="value"><?= number_format($akunPending) ?></div>
                    <div class="small-note mt-3">Akun menunggu verifikasi.</div>
                </div>

                <div class="stat-card glow-2">
                    <div class="label">Total Pendaftar</div>
                    <div class="value"><?= number_format($totalPendaftar) ?></div>
                    <div class="small-note mt-3">Form pendaftar masuk.</div>
                </div>

                <div class="stat-card glow-4">
                    <div class="label">Pendaftar Approved</div>
                    <div class="value"><?= number_format($pendaftarApproved) ?></div>
                    <div class="small-note mt-3">Pengajuan diterima.</div>
                </div>

                <div class="stat-card glow-6">
                    <div class="label">Pendaftar Pending</div>
                    <div class="value"><?= number_format($pendaftarPending) ?></div>
                    <div class="small-note mt-3">Menunggu review admin.</div>
                </div>
            </section>

            <!-- Charts area -->
            <section class="max-w-6xl mx-auto fade-in charts-wrap" style="animation-delay:0.18s;">
                <!-- Large bar chart -->
                <div class="chart-card">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-cyan-200">📊 Statistik Sistem (Bar)</h3>
                        <div class="small-note">Overview jumlah akun & pendaftar</div>
                    </div>

                    <div class="canvas-box canvas-box-lg">
                        <canvas id="barChart" class="canvas-responsive"></canvas>
                    </div>
                </div>

                <!-- Side: Pie + Line -->
                <div class="side-charts">
                    <div class="chart-card">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-md font-semibold text-emerald-300">🟣 Komposisi (Pie)</h3>
                            <div class="small-note">Persentase cepat</div>
                        </div>
                        <div class="canvas-box" style="height:260px;">
                            <canvas id="pieChart" class="canvas-responsive"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-md font-semibold text-sky-300">📈 Tren Bulanan (Line)</h3>
                            <div class="small-note">Jumlah pendaftar per bulan (<?= $currentYear ?>)</div>
                        </div>
                        <div class="canvas-box" style="height:220px;">
                            <canvas id="lineChart" class="canvas-responsive"></canvas>
                        </div>
                    </div>
                </div>
            </section>

        </main>

        <!-- Footer include -->
        <?php include __DIR__ . "/footer_admin.php"; ?>
    </div>
</div>

<script>
feather.replace();

// hide spinner then show content
window.addEventListener('load', ()=> {
    const s = document.getElementById('loading-spinner');
    if(s){ s.style.opacity='0'; setTimeout(()=>s.style.display='none',350); }
});

/* -----------------------
   Prepare data from PHP
   ----------------------- */
const barLabels = [
  'Total Akun','Akun Approved','Akun Pending',
  'Total Pendaftar','Pendaftar Approved','Pendaftar Pending'
];
const barValues = [
  <?= $totalAkun ?>, <?= $akunApproved ?>, <?= $akunPending ?>,
  <?= $totalPendaftar ?>, <?= $pendaftarApproved ?>, <?= $pendaftarPending ?>
];
const barColors = ['#60f0ff','#7ef7bf','#ffe66a','#60d0ff','#7dff9a','#ff7aa0'];

/* Pie breakdown: we will use counts for accounts + pendaftar (grouped to 6 slices as above) */
const pieValues = [...barValues];
const pieColors = barColors;

/* Line data (months) */
const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
const monthlyCounts = [
  <?php
    // echo monthCounts 1..12
    $arr = [];
    for ($i=1;$i<=12;$i++) $arr[] = (int)$monthCounts[$i];
    echo implode(',', $arr);
  ?>
];

/* Neon tooltip external (simple) */
const neonExternalTooltip = {
  external: function(context){
    const {chart, tooltip} = context;
    let el = chart.canvas.parentNode.querySelector('.chartjs-neon-tooltip');
    if(!el){ el = document.createElement('div'); el.className='chartjs-neon-tooltip'; chart.canvas.parentNode.appendChild(el); }
    if(tooltip.opacity === 0){ el.style.opacity = 0; return; }
    if(tooltip.body){
      const bodyLines = tooltip.body.map(b => b.lines.join('')).join('<br>');
      el.innerHTML = bodyLines;
    }
    const pos = chart.canvas.getBoundingClientRect();
    el.style.left = (pos.left + window.pageXOffset + tooltip.caretX) + 'px';
    el.style.top  = (pos.top + window.pageYOffset + tooltip.caretY) + 'px';
    el.style.opacity = 1;
  }
};

/* small common options */
const commonOptions = {
  plugins: {
    legend: { display:false },
    tooltip: { enabled: false, external: neonExternalTooltip.external }
  },
  maintainAspectRatio: false,
  responsive: true
};

/* -----------------------
   BAR CHART
   ----------------------- */
const barCtx = document.getElementById('barChart').getContext('2d');
const barChart = new Chart(barCtx, {
  type: 'bar',
  data: {
    labels: barLabels,
    datasets: [{
      label: 'Statistik PMB',
      data: barValues,
      backgroundColor: barColors,
      borderColor: '#0ff',
      borderWidth: 1,
      borderRadius: 8
    }]
  },
  options: Object.assign({}, commonOptions, {
    scales: {
      y: { beginAtZero:true, grid:{ color:'rgba(255,255,255,0.06)' }, ticks: { color:'#cbd5e1' } },
      x: { ticks:{ color:'#cbd5e1' }, grid:{ display:false } }
    },
    animation: { duration: 900, easing: 'easeOutQuart' }
  })
});

/* -----------------------
   PIE CHART
   ----------------------- */
const pieCtx = document.getElementById('pieChart').getContext('2d');
const pieChart = new Chart(pieCtx, {
  type: 'pie',
  data: {
    labels: barLabels,
    datasets: [{
      data: pieValues,
      backgroundColor: pieColors,
      borderColor: 'rgba(0,0,0,0.18)',
      borderWidth: 1
    }]
  },
  options: Object.assign({}, commonOptions, {
    plugins: {
      legend: { display:true, position:'bottom', labels: { color:'#d1fae5' } },
      tooltip: { enabled:false, external: neonExternalTooltip.external }
    },
    animation: { duration: 800 }
  })
});

/* -----------------------
   LINE CHART (monthly)
   ----------------------- */
const lineCtx = document.getElementById('lineChart').getContext('2d');
const lineChart = new Chart(lineCtx, {
  type: 'line',
  data: {
    labels: months,
    datasets: [{
      label: 'Pendaftar per Bulan',
      data: monthlyCounts,
      fill: true,
      tension: 0.28,
      pointRadius: 4,
      borderWidth: 2.5,
      borderColor: '#34d399',
      backgroundColor: (ctx) => {
        const g = ctx.chart.ctx.createLinearGradient(0,0,0,200);
        g.addColorStop(0,'rgba(34,197,94,0.18)');
        g.addColorStop(1,'rgba(34,197,94,0)');
        return g;
      }
    }]
  },
  options: Object.assign({}, commonOptions, {
    scales: {
      y: { beginAtZero:true, ticks:{ color:'#cbd5e1' }, grid:{ color:'rgba(255,255,255,0.05)' } },
      x: { ticks:{ color:'#cbd5e1' }, grid:{ display:false } }
    },
    animation: { duration: 900 }
  })
});

/* small accessibility: redraw charts on resize to keep tooltip positioning reliable */
let resizeTimer;
window.addEventListener('resize', ()=> {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(()=> {
    barChart.resize();
    pieChart.resize();
    lineChart.resize();
  }, 250);
});

/* Logout confirmation (if header button has id logoutBtn) */
const logoutBtn = document.getElementById('logoutBtn');
if(logoutBtn){
  logoutBtn.addEventListener('click', ()=> {
    Swal.fire({
      title:'Yakin mau Logout bro?',
      text:'Sesi akan berakhir.',
      icon:'warning',
      showCancelButton:true,
      confirmButtonColor:'#10b981',
      cancelButtonColor:'#ef4444',
      confirmButtonText:'Ya, Logout!'
    }).then(res => {
      if(res.isConfirmed) window.location.href='../../public/logout.php';
    });
  });
}
</script>

</body>
</html>
