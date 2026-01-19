<?php
session_start();
require_once("../../config/database.php");

// Middleware calon mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'calon_mahasiswa') {
    header("Location: ../../public/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Ambil data pendaftar
$q = query("SELECT * FROM pendaftar WHERE user_id = $user_id LIMIT 1");
if ($q->num_rows === 0) {
    header("Location: pendaftaran.php?empty=1");
    exit;
}
$pendaftar = $q->fetch_assoc();

// Ambil dokumen
$dok = query("SELECT * FROM dokumen_pendaftar WHERE pendaftar_id = " . $pendaftar['id']);
$dokumen = $dok->num_rows > 0 ? $dok->fetch_assoc() : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Pendaftaran | PMB ITS NU</title>
<link rel="icon" href="../../assets/img/ITS.jpeg">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">

<style>
body {
    background: radial-gradient(circle at bottom, #0f172a, #000);
    color: white;
    font-family: 'Poppins', sans-serif;
}

/* Tab style */
.tab-active {
    border-bottom: 3px solid #10b981;
    color: #10b981;
}
.tab-inactive {
    color: #94a3b8;
}
.tab-btn {
    transition: all .3s ease;
}
.tab-btn:hover {
    color: #10b981;
}

/* Animasi smooth antar tab */
.section {
    opacity: 0;
    transform: translateY(15px);
    transition: all .5s ease;
}
.section.active {
    opacity: 1;
    transform: translateY(0);
}

/* Card futuristik */
.card {
    background: rgba(15, 23, 42, .6);
    border: 1px solid rgba(16, 185, 129, .35);
    backdrop-filter: blur(12px);
    border-radius: 14px;
    padding: 1.5rem;
    box-shadow: 0 0 20px rgba(16,185,129,.15);
}

/* Item data */
.item-box {
    background: rgba(30, 41, 59, .7);
    border: 1px solid rgba(51, 65, 85, .6);
    border-radius: 10px;
    padding: 12px 16px;
    transition: all .3s ease;
}
.item-box:hover {
    border-color: #10b981;
    transform: translateY(-3px);
}

/* Dokumen card */
.doc-box {
    background: rgba(30,41,59,.8);
    border: 1px solid rgba(16,185,129,.25);
    border-radius: 14px;
    overflow: hidden;
    transition: all .4s ease;
}
.doc-box:hover {
    transform: scale(1.03);
    box-shadow: 0 0 20px rgba(16,185,129,.3);
}
.doc-img {
    height: 180px;
    width: 100%;
    object-fit: cover;
    border-bottom: 1px solid rgba(16,185,129,.2);
}

/* Fade mount */
.fade-in {
    opacity: 0;
    transform: translateY(10px);
    animation: fadeIn .6s ease forwards;
}
@keyframes fadeIn {
    to { opacity: 1; transform: translateY(0); }
}
</style>
</head>

<body class="flex">

<?php include "sidebar_mahasiswa.php"; ?>

<div class="flex-1 flex flex-col min-h-screen ml-64">

<?php include "header_mahasiswa.php"; ?>

<main class="p-6 pt-[100px] max-w-5xl mx-auto fade-in">

    <!-- HEADER -->
    <h1 class="text-3xl font-bold text-emerald-400 mb-6 flex items-center gap-2 justify-center">
        <i data-feather="file-text"></i> Detail Pendaftaran
    </h1>

    <!-- TAB NAV -->
    <div class="flex justify-center gap-10 border-b border-emerald-500/30 mb-8 pb-3">
        <button id="tabForm" class="tab-btn tab-active text-lg font-semibold">Formulir PMB</button>
        <button id="tabDokumen" class="tab-btn tab-inactive text-lg font-semibold">Dokumen Upload</button>
    </div>

    <!-- ====================== -->
    <!-- FORM PMB -->
    <!-- ====================== -->
    <section id="formSection" class="section active">
        <div class="card grid grid-cols-1 md:grid-cols-2 gap-4">

            <?php
                function detailItem($label, $value) {
                    echo "
                    <div class='item-box'>
                        <p class='text-sm text-gray-400'>$label</p>
                        <p class='text-lg font-semibold text-emerald-300 mt-1'>$value</p>
                    </div>";
                }

                detailItem("NISN", $pendaftar['nisn']);
                detailItem("Email", $pendaftar['email']);
                detailItem("No WhatsApp", $pendaftar['no_wa']);
                detailItem("Asal Sekolah", $pendaftar['asal_sekolah']);
                detailItem("Tempat Lahir", $pendaftar['tempat_lahir']);
                detailItem("Tanggal Lahir", $pendaftar['tanggal_lahir']);
                detailItem("Hobby", $pendaftar['hobby']);
                detailItem("Minat & Bakat", $pendaftar['minat_bakat']);
                detailItem("Kompetisi Pernah Diikuti", $pendaftar['kompetisi']);
                detailItem("Program Studi Pilihan", $pendaftar['minat_prodi']);
                detailItem("Status Pesantren", $pendaftar['pesantren_status']);

                // Status Pendaftaran dengan warna dinamis
                $status = ucfirst($pendaftar['status_pendaftaran']);
                $color = "text-gray-400";
                if ($pendaftar['status_pendaftaran'] == 'approved') $color = "text-emerald-400";
                elseif ($pendaftar['status_pendaftaran'] == 'pending') $color = "text-yellow-400";
                elseif ($pendaftar['status_pendaftaran'] == 'rejected') $color = "text-red-400";

                echo "
                <div class='item-box col-span-2'>
                    <p class='text-sm text-gray-400'>Status Pendaftaran</p>
                    <p class='text-lg font-bold mt-1 $color'>$status</p>
                </div>";
            ?>

            <!-- Textarea-style long data -->
            <div class="item-box col-span-2">
                <p class="text-sm text-gray-400">Prestasi Akademik</p>
                <p class="mt-1 text-emerald-200"><?= nl2br($pendaftar['prestasi_akademik']) ?></p>
            </div>

            <div class="item-box col-span-2">
                <p class="text-sm text-gray-400">Prestasi Non Akademik</p>
                <p class="mt-1 text-emerald-200"><?= nl2br($pendaftar['prestasi_non_akademik']) ?></p>
            </div>

            <div class="item-box col-span-2">
                <p class="text-sm text-gray-400">Alamat</p>
                <p class="mt-1 text-emerald-200"><?= nl2br($pendaftar['alamat']) ?></p>
            </div>

            <div class="item-box col-span-2">
                <p class="text-sm text-gray-400">Info Pendaftaran</p>
                <p class="mt-1 text-emerald-200"><?= nl2br($pendaftar['info_pendaftaran']) ?></p>
            </div>

        </div>
    </section>

    <!-- ====================== -->
    <!-- DOKUMEN -->
    <!-- ====================== -->
    <section id="dokumenSection" class="section hidden">
        <?php if (!$dokumen): ?>
            <p class="text-gray-400 italic text-center mt-6">Belum ada dokumen diunggah.</p>
        <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

            <?php
                function dokItem($label, $file) {
                    if (!$file) return;
                    $path = "../../uploads/pendaftar$file";
                    echo "
                    <div class='doc-box'>
                        <img src='$path' alt='$label' class='doc-img'>
                        <div class='p-4 text-center'>
                            <p class='text-emerald-300 font-semibold mb-2'>$label</p>
                            <a href='$path' target='_blank' class='bg-emerald-500 hover:bg-emerald-600 text-black font-bold px-4 py-2 rounded-lg inline-block transition'>
                                <i data-feather=\"download\" class=\"inline\"></i> Lihat / Unduh
                            </a>
                        </div>
                    </div>";
                }

                dokItem("Pas Foto", $dokumen['pasfoto']);
                dokItem("Kartu Keluarga", $dokumen['kartu_keluarga']);
                dokItem("KTP / Kartu Pelajar", $dokumen['ktp']);
                dokItem("Ijazah", $dokumen['ijazah']);
                dokItem("Raport", $dokumen['raport']);
                dokItem("Bukti Pembayaran", $dokumen['bukti_pembayaran']);
            ?>

        </div>
        <?php endif; ?>
    </section>

</main>

<?php include "footer_mahasiswa.php"; ?>
</div>

<script>
feather.replace();

// Smooth tab switching
const tabForm = document.getElementById("tabForm");
const tabDok = document.getElementById("tabDokumen");
const formSec = document.getElementById("formSection");
const dokSec = document.getElementById("dokumenSection");

function activateTab(tab) {
    if (tab === "form") {
        tabForm.classList.add("tab-active");
        tabForm.classList.remove("tab-inactive");
        tabDok.classList.remove("tab-active");
        tabDok.classList.add("tab-inactive");

        formSec.classList.add("active");
        dokSec.classList.remove("active");
        setTimeout(()=> {
            dokSec.classList.add("hidden");
            formSec.classList.remove("hidden");
        }, 250);
    } else {
        tabDok.classList.add("tab-active");
        tabDok.classList.remove("tab-inactive");
        tabForm.classList.remove("tab-active");
        tabForm.classList.add("tab-inactive");

        dokSec.classList.remove("hidden");
        formSec.classList.add("hidden");
        setTimeout(()=> {
            dokSec.classList.add("active");
        }, 60);
        window.scrollTo({top:0,behavior:'smooth'});
    }
}

tabForm.onclick = () => activateTab("form");
tabDok.onclick = () => activateTab("dokumen");

// Initial active anim
setTimeout(()=>document.querySelector(".section.active")?.classList.add("active"),100);
</script>

</body>
</html>
