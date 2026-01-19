<?php
session_start();
require_once("../../config/database.php");

// Middleware mahasiswa
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

// Update form
if (isset($_POST['update_pmb'])) {
    $fields = [
        'nisn', 'email', 'no_wa', 'asal_sekolah', 'alamat', 'tempat_lahir', 'tanggal_lahir',
        'hobby', 'minat_bakat', 'kompetisi', 'prestasi_akademik', 'prestasi_non_akademik',
        'minat_prodi', 'pesantren_status', 'info_pendaftaran'
    ];

    $data = [];
    foreach ($fields as $f) {
        $data[$f] = escape($_POST[$f]);
    }

    query("UPDATE pendaftar SET
        nisn='{$data['nisn']}',
        email='{$data['email']}',
        no_wa='{$data['no_wa']}',
        asal_sekolah='{$data['asal_sekolah']}',
        alamat='{$data['alamat']}',
        tempat_lahir='{$data['tempat_lahir']}',
        tanggal_lahir='{$data['tanggal_lahir']}',
        hobby='{$data['hobby']}',
        minat_bakat='{$data['minat_bakat']}',
        kompetisi='{$data['kompetisi']}',
        prestasi_akademik='{$data['prestasi_akademik']}',
        prestasi_non_akademik='{$data['prestasi_non_akademik']}',
        minat_prodi='{$data['minat_prodi']}',
        pesantren_status='{$data['pesantren_status']}',
        info_pendaftaran='{$data['info_pendaftaran']}'
        WHERE id = {$pendaftar['id']}
    ");

    header("Location: pendaftaran.php?update=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Pendaftaran | PMB ITS NU</title>
<link rel="icon" href="../../assets/img/ITS.jpeg">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body {
    background: radial-gradient(circle at bottom, #0f172a, #000);
    color: white;
    font-family: 'Poppins', sans-serif;
}

/* Fade mount animation */
.fade-in {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeIn .6s ease forwards;
}
@keyframes fadeIn {
    to { opacity: 1; transform: translateY(0); }
}

/* Card */
.card {
    background: rgba(15,23,42,0.6);
    border: 1px solid rgba(16,185,129,.35);
    border-radius: 16px;
    padding: 28px;
    backdrop-filter: blur(12px);
    box-shadow: 0 0 25px rgba(16,185,129,0.15);
}

/* Input & textarea */
.input {
    padding: 12px;
    background: rgba(30,41,59,.7);
    border: 1px solid rgba(51,65,85,.7);
    border-radius: 10px;
    width: 100%;
    color: white;
    transition: all .25s ease;
}
.input:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 10px #10b98180;
}

/* Button save */
.btn-save {
    background: #10b981;
    color: #000;
    padding: 12px;
    border-radius: 10px;
    font-weight: bold;
    font-size: 1.1rem;
    transition: all .3s ease;
    box-shadow: 0 0 15px rgba(16,185,129,.3);
}
.btn-save:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 0 25px rgba(16,185,129,.4);
}

/* Label for field grouping */
.label {
    color: #9ca3af;
    font-size: .9rem;
}
</style>
</head>

<body class="flex">

<?php include "sidebar_mahasiswa.php"; ?>

<div class="flex-1 flex flex-col min-h-screen ml-64">

<?php include "header_mahasiswa.php"; ?>

<main class="p-6 pt-[100px] fade-in max-w-4xl mx-auto">

    <h1 class="text-3xl font-bold text-emerald-400 mb-8 flex items-center gap-2 justify-center">
        <i data-feather="edit-3"></i> Edit Formulir Pendaftaran
    </h1>

    <div class="card">
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div>
                <label class="label">NISN</label>
                <input name="nisn" value="<?= $pendaftar['nisn'] ?>" class="input" required>
            </div>
            <div>
                <label class="label">Email</label>
                <input name="email" type="email" value="<?= $pendaftar['email'] ?>" class="input" required>
            </div>

            <div>
                <label class="label">No WhatsApp</label>
                <input name="no_wa" value="<?= $pendaftar['no_wa'] ?>" class="input">
            </div>
            <div>
                <label class="label">Asal Sekolah</label>
                <input name="asal_sekolah" value="<?= $pendaftar['asal_sekolah'] ?>" class="input">
            </div>

            <div>
                <label class="label">Tempat Lahir</label>
                <input name="tempat_lahir" value="<?= $pendaftar['tempat_lahir'] ?>" class="input">
            </div>
            <div>
                <label class="label">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="<?= $pendaftar['tanggal_lahir'] ?>" class="input bg-gray-800">
            </div>

            <div>
                <label class="label">Hobby</label>
                <input name="hobby" value="<?= $pendaftar['hobby'] ?>" class="input">
            </div>
            <div>
                <label class="label">Minat & Bakat</label>
                <input name="minat_bakat" value="<?= $pendaftar['minat_bakat'] ?>" class="input">
            </div>

            <div class="md:col-span-2">
                <label class="label">Kompetisi Pernah Diikuti</label>
                <input name="kompetisi" value="<?= $pendaftar['kompetisi'] ?>" class="input">
            </div>

            <div class="md:col-span-2">
                <label class="label">Prestasi Akademik</label>
                <textarea name="prestasi_akademik" class="input" rows="2"><?= $pendaftar['prestasi_akademik'] ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label class="label">Prestasi Non Akademik</label>
                <textarea name="prestasi_non_akademik" class="input" rows="2"><?= $pendaftar['prestasi_non_akademik'] ?></textarea>
            </div>

            <div>
                <label class="label">Program Studi Pilihan</label>
                <select name="minat_prodi" class="input bg-gray-800">
                    <option <?= $pendaftar['minat_prodi']=='S-1 Teknik Industri'?'selected':'' ?>>S-1 Teknik Industri</option>
                    <option <?= $pendaftar['minat_prodi']=='S-1 Teknik Komputer'?'selected':'' ?>>S-1 Teknik Komputer</option>
                    <option <?= $pendaftar['minat_prodi']=='S-1 Teknik Lingkungan'?'selected':'' ?>>S-1 Teknik Lingkungan</option>
                </select>
            </div>

            <div>
                <label class="label">Status Pesantren</label>
                <select name="pesantren_status" class="input bg-gray-800">
                    <option <?= $pendaftar['pesantren_status']=='Pernah'?'selected':'' ?>>Pernah</option>
                    <option <?= $pendaftar['pesantren_status']=='Tidak Pernah'?'selected':'' ?>>Tidak Pernah</option>
                    <option <?= $pendaftar['pesantren_status']=='Minat'?'selected':'' ?>>Minat</option>
                    <option <?= $pendaftar['pesantren_status']=='Belum Minat'?'selected':'' ?>>Belum Minat</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="label">Alamat Lengkap</label>
                <textarea name="alamat" class="input" rows="2"><?= $pendaftar['alamat'] ?></textarea>
            </div>

            <div class="md:col-span-2">
                <label class="label">Info Pendaftaran (dapat info dari mana?)</label>
                <textarea name="info_pendaftaran" class="input" rows="2"><?= $pendaftar['info_pendaftaran'] ?></textarea>
            </div>

            <div class="md:col-span-2 flex justify-center mt-4">
                <button name="update_pmb" class="btn-save flex items-center gap-2">
                    <i data-feather="save"></i> Simpan Perubahan
                </button>
            </div>

        </form>
    </div>

</main>

<?php include "footer_mahasiswa.php"; ?>
</div>

<script>
feather.replace();
</script>

</body>
</html>
