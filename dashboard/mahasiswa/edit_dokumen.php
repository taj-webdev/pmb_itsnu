<?php
session_start();
require_once("../../config/database.php");

// Cek Role Mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'calon_mahasiswa') {
    header("Location: ../../public/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Ambil Data Pendaftar
$q = query("SELECT * FROM pendaftar WHERE user_id = $user_id LIMIT 1");
if ($q->num_rows == 0) {
    header("Location: pendaftaran.php?empty=1");
    exit;
}
$pendaftar = $q->fetch_assoc();

// Ambil Dokumen
$dok = query("SELECT * FROM dokumen_pendaftar WHERE pendaftar_id = {$pendaftar['id']} LIMIT 1");
$dokumen = ($dok->num_rows > 0) ? $dok->fetch_assoc() : [];

// Jika belum ada row dokumen, buat data kosong
if (!$dokumen) {
    query("INSERT INTO dokumen_pendaftar (pendaftar_id) VALUES ({$pendaftar['id']})");
    $dok = query("SELECT * FROM dokumen_pendaftar WHERE pendaftar_id = {$pendaftar['id']} LIMIT 1");
    $dokumen = $dok->fetch_assoc();
}

$upload_path = "../../uploads/pendaftar";

// Fungsi Upload Dokumen
function uploadFile($field, $oldFile, $upload_path) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== 0) {
        return $oldFile;
    }

    $allowed = ['jpg','jpeg','png','pdf'];
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return $oldFile;
    }

    if ($oldFile && file_exists($upload_path.$oldFile)) {
        unlink($upload_path.$oldFile);
    }

    $newName = $field."_".time().".".$ext;
    move_uploaded_file($_FILES[$field]['tmp_name'], $upload_path.$newName);
    return $newName;
}

// ACTION UPDATE
if (isset($_POST['update_dokumen'])) {
    $pasfoto = uploadFile("pasfoto", $dokumen['pasfoto'], $upload_path);
    $kk = uploadFile("kartu_keluarga", $dokumen['kartu_keluarga'], $upload_path);
    $ktp = uploadFile("ktp", $dokumen['ktp'], $upload_path);
    $ijazah = uploadFile("ijazah", $dokumen['ijazah'], $upload_path);
    $raport = uploadFile("raport", $dokumen['raport'], $upload_path);
    $bukti = uploadFile("bukti_pembayaran", $dokumen['bukti_pembayaran'], $upload_path);

    query("UPDATE dokumen_pendaftar SET 
        pasfoto='$pasfoto',
        kartu_keluarga='$kk',
        ktp='$ktp',
        ijazah='$ijazah',
        raport='$raport',
        bukti_pembayaran='$bukti'
        WHERE id = {$dokumen['id']}
    ");

    header("Location: pendaftaran.php?dokumen_update=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Dokumen | PMB ITS NU</title>
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
.fade-in {
    opacity: 0;
    transform: translateY(15px);
    animation: fadeIn .6s ease forwards;
}
@keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }

.card {
    background: rgba(15,23,42,0.6);
    border: 1px solid rgba(16,185,129,.35);
    border-radius: 16px;
    padding: 24px;
    backdrop-filter: blur(12px);
    box-shadow: 0 0 25px rgba(16,185,129,0.15);
}
.file-box {
    background: rgba(30,41,59,.7);
    border: 1px solid rgba(51,65,85,.7);
    border-radius: 12px;
    padding: 16px;
    transition: all .25s ease;
}
.file-box:hover {
    border-color: #10b981;
    transform: translateY(-3px);
}
.input-file {
    background: rgba(30,41,59,.7);
    border-radius: 10px;
    width: 100%;
    border: 1px solid rgba(51,65,85,.7);
    padding: 10px;
    color: #cbd5e1;
}
.input-file:focus {
    border-color: #10b981;
    box-shadow: 0 0 10px #10b98180;
}
.preview {
    margin-top: 10px;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid rgba(16,185,129,.25);
}
.preview img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}
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
</style>
</head>

<body class="flex">

<?php include "sidebar_mahasiswa.php"; ?>

<div class="flex-1 flex flex-col min-h-screen ml-64">
<?php include "header_mahasiswa.php"; ?>

<main class="p-6 pt-[100px] fade-in max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold text-emerald-400 mb-8 flex items-center gap-2 justify-center">
        <i data-feather="folder"></i> Edit Dokumen Pendaftaran
    </h1>

    <div class="card">
        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <?php
                function dokInput($label, $name, $file) {
                    $path = "../../uploads/pendaftar" . $file;
                    echo "
                    <div class='file-box'>
                        <p class='text-emerald-300 mb-2 font-semibold'>$label</p>
                        <input type='file' name='$name' class='input-file' onchange='previewFile(this, \"$name-preview\")'>
                        <div class='preview mt-2' id='$name-preview'>
                            ".($file ? (preg_match('/\\.pdf$/i',$file) 
                                ? "<iframe src='$path' class='w-full h-40'></iframe>" 
                                : "<img src='$path'>") 
                            : "<p class=\"text-gray-500 text-sm italic text-center py-10\">Belum ada file diunggah</p>")."
                        </div>
                    </div>";
                }

                dokInput("Pasfoto (L: Biru, P: Merah)", "pasfoto", $dokumen['pasfoto']);
                dokInput("Kartu Keluarga", "kartu_keluarga", $dokumen['kartu_keluarga']);
                dokInput("KTP / Kartu Pelajar", "ktp", $dokumen['ktp']);
                dokInput("Ijazah", "ijazah", $dokumen['ijazah']);
                dokInput("Raport", "raport", $dokumen['raport']);
                dokInput("Bukti Pembayaran", "bukti_pembayaran", $dokumen['bukti_pembayaran']);
            ?>

            <div class="col-span-2 flex justify-center">
                <button name="update_dokumen" class="btn-save flex items-center gap-2">
                    <i data-feather="save"></i> Simpan Perubahan Dokumen
                </button>
            </div>

        </form>
    </div>
</main>

<?php include "footer_mahasiswa.php"; ?>
</div>

<script>
feather.replace();

// Preview file
function previewFile(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    const ext = file.name.split('.').pop().toLowerCase();
    reader.onload = e => {
        if (ext === 'pdf') {
            preview.innerHTML = `<iframe src="${e.target.result}" class="w-full h-40 rounded-lg border border-emerald-500/30"></iframe>`;
        } else {
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-40 object-cover rounded-lg border border-emerald-500/30">`;
        }
    };
    reader.readAsDataURL(file);
}
</script>

</body>
</html>
