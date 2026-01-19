<?php 
session_start();
require_once("../../config/database.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'calon_mahasiswa') {
    header("Location: ../../public/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$nama = $_SESSION['user']['nama_lengkap'];

// Status Akun
$akun = query("SELECT status_akun FROM users WHERE id=$user_id")->fetch_assoc();
$statusAkun = $akun['status_akun'];

// Status Pendaftaran
$q = query("SELECT status_pendaftaran FROM pendaftar WHERE user_id=$user_id");
$statusDaftar = $q->num_rows > 0 ? $q->fetch_assoc()['status_pendaftaran'] : "Belum Mengajukan";

$ajukan = ($statusDaftar == "Belum Mengajukan") ? "Belum" : "Sudah";
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Mahasiswa | PMB ITS NU</title>
<link rel="icon" type="image/jpeg" href="../../assets/img/ITS.jpeg">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>

<style>

/* ------------------ PAGE OPENING BLUR ------------------ */
body{
    font-family:'Poppins',sans-serif;
    background:radial-gradient(circle at bottom,#0f172a,#000);
    color:white;
    filter: blur(8px);
    animation: blurIn .8s ease forwards;
}
@keyframes blurIn {
    to { filter: blur(0); }
}

/* Fade in content */
.fade-in{
    opacity:0;
    transform:translateY(20px);
    animation:fadeIn .6s ease forwards .4s;
}
@keyframes fadeIn{
    to{opacity:1;transform:translateY(0);}
}

/* ------------------ CARD BASE ------------------ */
.card{
    position: relative;
    background:rgba(15,23,42,.6);
    border:1px solid rgba(16,185,129,.3);
    backdrop-filter:blur(10px);
    border-radius: 12px;
    overflow: hidden;
    transition: all .3s ease;
}

/* ------------------ CARD HOVER NEON GLOW ------------------ */
.card:hover {
    transform: translateY(-4px) scale(1.03);
    border-color: rgba(16,185,129,.7);
    box-shadow: 0 0 25px rgba(16,185,129,.45);
}

/* ------------------ GRADIENT GLOW BORDER ANIMATION ------------------ */
.card::before {
    content: '';
    position: absolute;
    inset: 0;
    padding: 2px;
    border-radius: 12px;
    background: linear-gradient(120deg, #10b981, #1e3a8a, #10b981);
    background-size: 200% 200%;
    animation: borderMove 4s linear infinite;
    mask: 
      linear-gradient(#000 0 0) content-box, 
      linear-gradient(#000 0 0);
    mask-composite: exclude;
}
@keyframes borderMove {
    0% { background-position: 0% 50%; }
    100% { background-position: 200% 50%; }
}

/* ------------------ ICON PULSE ------------------ */
.icon-pulse {
    animation: pulse 2s infinite ease-in-out;
}
@keyframes pulse {
    0% { opacity: .7; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.2); }
    100% { opacity: .7; transform: scale(1); }
}

</style>
</head>

<body class="flex">

<!-- SIDEBAR -->
<?php include "sidebar_mahasiswa.php"; ?>

<!-- LAYOUT WRAPPER (HEADER + MAIN + FOOTER) -->
<div class="flex flex-col flex-1 ml-64 min-h-screen">

    <!-- HEADER -->
    <?php include "header_mahasiswa.php"; ?>

    <!-- MAIN CONTENT -->
    <main class="p-6 mt-24 fade-in flex-1">

        <h1 class="text-center text-2xl font-bold text-emerald-400 mb-6">
            SELAMAT DATANG PADA PORTAL PMB ITS NU KALIMANTAN
        </h1>

        <!-- 3 Card Status dengan Ikon Futuristik -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Sudah Ajukan -->
            <div class="card p-6 rounded-xl text-center">
                <i data-feather="send" class="w-6 h-6 mb-2 text-emerald-400 icon-pulse"></i>
                <h3 class="text-lg font-semibold text-gray-300 mb-2">Sudah Ajukan Pendaftaran?</h3>
                <p class="text-2xl font-bold"><?= $ajukan ?></p>
            </div>

            <!-- Status Akun -->
            <div class="card p-6 rounded-xl text-center">
                <i data-feather="shield" class="w-6 h-6 mb-2 text-emerald-400 icon-pulse"></i>
                <h3 class="text-lg font-semibold text-gray-300 mb-2">Status Akun</h3>
                <p class="text-2xl font-bold 
                    <?php 
                        if($statusAkun=='approved') echo 'text-emerald-400';
                        elseif($statusAkun=='pending') echo 'text-yellow-400';
                        else echo 'text-red-400';
                    ?>">
                    <?= ucfirst($statusAkun) ?>
                </p>
            </div>

            <!-- Status Pendaftaran -->
            <div class="card p-6 rounded-xl text-center">
                <i data-feather="file-text" class="w-6 h-6 mb-2 text-emerald-400 icon-pulse"></i>
                <h3 class="text-lg font-semibold text-gray-300 mb-2">Status Pendaftaran</h3>
                <p class="text-2xl font-bold 
                    <?php 
                        if($statusDaftar=='approved') echo 'text-emerald-400';
                        elseif($statusDaftar=='pending') echo 'text-yellow-400';
                        elseif($statusDaftar=='rejected') echo 'text-red-400';
                        else echo 'text-gray-400';
                    ?>">
                    <?= ucfirst($statusDaftar) ?>
                </p>
            </div>

        </div>

    </main>

    <!-- FOOTER -->
    <?php include "footer_mahasiswa.php"; ?>
</div>

<script>
    feather.replace();
</script>

</body>
</html>
