<?php
session_start();
require_once("../../config/database.php");

// Middleware Mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'calon_mahasiswa') {
    header("Location: ../../public/login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = (int)$user['id'];

// Ambil status akun aktual dari DB (lebih aman)
$q = query("SELECT nama_lengkap, username, status_akun FROM users WHERE id = $user_id LIMIT 1");
if ($q->num_rows === 0) {
    $nama = htmlspecialchars($user['nama_lengkap'] ?? '');
    $username = htmlspecialchars($user['username'] ?? '');
    $statusAkun = 'unknown';
} else {
    $u = $q->fetch_assoc();
    $nama = htmlspecialchars($u['nama_lengkap']);
    $username = htmlspecialchars($u['username']);
    $statusAkun = $u['status_akun'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Akun | PMB ITS NU</title>
<link rel="icon" type="image/jpeg" href="../../assets/img/ITS.jpeg">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Poppins',sans-serif;
    background:radial-gradient(circle at bottom,#0f172a,#000);
    color:white;
    overflow-x:hidden;
}

/* Container */
.container { max-width: 920px; margin: 0 auto; }

/* Neon Glow Card */
.card {
    position: relative;
    background: rgba(15,23,42,.65);
    border: 1px solid rgba(16,185,129,.3);
    backdrop-filter: blur(10px);
    border-radius: 14px;
    padding: 1.5rem;
    transition: all .4s ease;
    box-shadow: 0 0 20px rgba(16,185,129,.2);
    animation: fadeIn 0.8s ease both;
}
.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 25px rgba(16,185,129,.45),
                0 0 35px rgba(16,185,129,.25);
    border-color: rgba(16,185,129,.5);
}

/* Fade In Animation */
@keyframes fadeIn {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

/* Label Style */
.label {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    color: #9ca3af;
    font-weight: 600;
}

/* Status */
.status-pill {
    display:inline-block;
    padding:.5rem 1rem;
    border-radius:999px;
    font-weight:700;
    box-shadow:0 0 10px rgba(16,185,129,.3);
    transition: all .3s ease;
}
.status-pill:hover { box-shadow:0 0 15px rgba(16,185,129,.6); transform:scale(1.05); }

/* Hint */
.hint { color:#94a3b8; font-size:.9rem; }

/* Layout Offset for Sidebar */
.main-offset { padding-top:90px; }

/* Inner border glowing pulse */
@keyframes neonPulse {
  0%,100% { box-shadow:0 0 20px rgba(16,185,129,.4), inset 0 0 10px rgba(16,185,129,.2); }
  50% { box-shadow:0 0 30px rgba(16,185,129,.6), inset 0 0 15px rgba(16,185,129,.4); }
}
.card::before {
  content:'';
  position:absolute;
  inset:0;
  border-radius:14px;
  border:1px solid rgba(16,185,129,.25);
  animation: neonPulse 3s ease-in-out infinite;
  z-index:-1;
}
</style>
</head>

<body class="flex">

<?php include __DIR__ . "/sidebar_mahasiswa.php"; ?>

<div class="flex-1 flex flex-col min-h-screen main-offset ml-64">
    <?php include __DIR__ . "/header_mahasiswa.php"; ?>

    <main class="flex-1 p-6 container">
        <h1 class="text-2xl font-bold text-emerald-400 mb-6 flex items-center gap-2 animate-[fadeIn_0.6s_ease]">
            <i data-feather="user"></i> Data Akun Anda
        </h1>

        <div class="card">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-emerald-400 to-sky-600 
                                flex items-center justify-center text-black font-bold text-xl shadow-inner animate-[fadeIn_1s_ease]">
                        <?= strtoupper(substr($nama,0,1) ?: 'U') ?>
                    </div>
                    <div>
                        <p class="text-xl font-semibold"><?= $nama ?></p>
                        <p class="hint">Calon Mahasiswa</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="text-right sm:text-left">
                        <p class="label"><i data-feather="at-sign" class="text-emerald-400"></i> Username</p>
                        <div class="mt-1 p-2 bg-gray-800/60 rounded-md border border-gray-700 font-medium">
                            <?= $username ?>
                        </div>
                    </div>

                    <div class="text-right sm:text-left">
                        <p class="label"><i data-feather="shield" class="text-emerald-400"></i> Status Akun</p>
                        <div class="mt-1">
                            <?php if ($statusAkun === 'approved'): ?>
                                <span class="status-pill" style="background:#064e3b;color:#d1fae5;">Approved</span>
                            <?php elseif ($statusAkun === 'pending'): ?>
                                <span class="status-pill" style="background:#fef3c7;color:#111827;">Pending</span>
                            <?php elseif ($statusAkun === 'rejected'): ?>
                                <span class="status-pill" style="background:#7f1d1d;color:#fff6f6;">Rejected</span>
                            <?php else: ?>
                                <span class="status-pill" style="background:#334155;color:#e2e8f0;">Unknown</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-6 border-emerald-600/30">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-400 mb-2">Nama Lengkap</p>
                    <div class="bg-gray-800/60 p-3 rounded-md border border-gray-700"><?= $nama ?></div>
                </div>

                <div>
                    <p class="text-sm text-gray-400 mb-2">Username</p>
                    <div class="bg-gray-800/60 p-3 rounded-md border border-gray-700"><?= $username ?></div>
                </div>

                <div>
                    <p class="text-sm text-gray-400 mb-2">Status Akun (DB)</p>
                    <div class="bg-gray-800/60 p-3 rounded-md border border-gray-700">
                        <?php
                        $badge = '<span class="font-semibold">';
                        if ($statusAkun === 'approved') $badge .= '<span style="color:#10b981">Approved</span>';
                        elseif ($statusAkun === 'pending') $badge .= '<span style="color:#f59e0b">Pending</span>';
                        elseif ($statusAkun === 'rejected') $badge .= '<span style="color:#ef4444">Rejected</span>';
                        else $badge .= 'Unknown';
                        $badge .= '</span>';
                        echo $badge;
                        ?>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="index.php" class="px-4 py-2 bg-gray-800 rounded-md border border-gray-700 hover:bg-gray-700">Kembali</a>
                <a href="edit_akun.php" class="px-4 py-2 bg-emerald-600 rounded-md hover:bg-emerald-700 font-semibold shadow-[0_0_10px_rgba(16,185,129,.3)] hover:shadow-[0_0_20px_rgba(16,185,129,.6)] transition-all">Edit Akun</a>
            </div>
        </div>
    </main>

    <?php include __DIR__ . "/footer_mahasiswa.php"; ?>
</div>

<script>
feather.replace();
</script>
</body>
</html>
