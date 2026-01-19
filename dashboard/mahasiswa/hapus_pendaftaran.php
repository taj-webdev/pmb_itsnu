<?php
session_start();
require_once("../../config/database.php");

// Middleware keamanan
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'calon_mahasiswa') {
    header("Location: ../../public/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validasi ID
if ($id <= 0) {
    header("Location: pendaftaran.php?error=invalid_id");
    exit;
}

// Ambil data pendaftar untuk cek kepemilikan
$q = query("SELECT * FROM pendaftar WHERE id = $id AND user_id = $user_id LIMIT 1");
if ($q->num_rows === 0) {
    header("Location: pendaftaran.php?error=unauthorized");
    exit;
}
$pendaftar = $q->fetch_assoc();

// Hapus semua dokumen terkait jika ada
$dok = query("SELECT * FROM dokumen_pendaftar WHERE pendaftar_id = $id");
if ($dok->num_rows > 0) {
    $dokumen = $dok->fetch_assoc();
    $upload_path = "../../uploads/pendaftar";

    $fields = ['pasfoto', 'kartu_keluarga', 'ktp', 'ijazah', 'raport', 'bukti_pembayaran'];
    foreach ($fields as $f) {
        if (!empty($dokumen[$f]) && file_exists($upload_path . $dokumen[$f])) {
            unlink($upload_path . $dokumen[$f]);
        }
    }

    query("DELETE FROM dokumen_pendaftar WHERE pendaftar_id = $id");
}

// Hapus data pendaftar utama
query("DELETE FROM pendaftar WHERE id = $id AND user_id = $user_id");

header("Location: pendaftaran.php?deleted=1");
exit;
?>
