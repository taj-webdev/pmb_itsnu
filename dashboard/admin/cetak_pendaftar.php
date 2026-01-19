<?php
session_start();
require_once("../../config/database.php");
require_once("../../vendor/autoload.php");

use Dompdf\Dompdf;

// 🔒 Cek akses admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Akses ditolak.");
}

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan.");
}

$id = intval($_GET['id']);
$admin_name = $_SESSION['user']['nama_lengkap'];

// 🎓 Ambil data pendaftar
$q = query("
    SELECT p.*, u.nama_lengkap
    FROM pendaftar p
    JOIN users u ON u.id = p.user_id
    WHERE p.id = $id
");

if ($q->num_rows == 0) {
    die("Data tidak ditemukan.");
}

$data = $q->fetch_assoc();

// 🗓 Format tanggal Indonesia
function tglIndo($date) {
    $bulan = [
        1 => 'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ];
    $pecah = explode('-', $date);
    return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
}

$hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$hariIni = $hari[date('w')];
$tanggalCetak = $hariIni . ", " . tglIndo(date('Y-m-d'));

// Path logo untuk watermark
$logoPath = realpath(__DIR__ . "/../../assets/img/ITS.jpeg");

// =============================
// 🧾 HTML PDF
// =============================
$html = "
<style>
@page { margin: 40px 40px 60px 40px; }
body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 12px;
    color: #222;
    background: url('file://$logoPath') no-repeat center center;
    background-size: 350px;
    opacity: 0.98;
}

/* HEADER (KOP) */
.header {
    text-align: center;
    border-bottom: 2px solid #10b981;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.kop-title {
    font-size: 18px;
    font-weight: bold;
    color: #064e3b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.kop-sub {
    font-size: 11px;
    color: #374151;
}

/* TABLE */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
}
.table th {
    background: #10b981;
    color: white;
    padding: 6px;
    border: 1px solid #059669;
    text-align: left;
}
.table td {
    border: 1px solid #9ca3af;
    padding: 6px;
    background: rgba(249, 250, 251, 0.92);
}

/* SECTION TITLE */
.section-title {
    font-weight: bold;
    font-size: 14px;
    color: #065f46;
    margin-top: 20px;
    border-left: 5px solid #10b981;
    padding-left: 6px;
}

/* FOOTER */
.footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    border-top: 2px solid #10b981;
    text-align: right;
    font-size: 11px;
    color: #374151;
    padding-top: 6px;
}

/* WATERMARK (optional) */
.watermark {
    position: fixed;
    top: 35%;
    left: 0;
    right: 0;
    text-align: center;
    opacity: 0.08;
    z-index: -1;
}
.watermark img {
    width: 320px;
}
</style>

<!-- HEADER -->
<div class='header'>
    <div class='kop-title'>Institut Teknologi dan Sains Nahdlatul Ulama Kalimantan</div>
    <div class='kop-sub'>Jl. RTA Milono No.Km. 3, Langkai, Kec. Pahandut, Kota Palangka Raya</div>
    <div class='kop-sub'>Kalimantan Tengah 73111 | Telp. 0822-5154-8898</div>
    <div class='kop-sub'>Email: info@itsnu-kalimantan.ac.id</div>
</div>

<!-- WATERMARK -->
<div class='watermark'>
    <img src='file://$logoPath'>
</div>

<h3 style='text-align:center; margin-top:5px; color:#065f46;'>
    FORMULIR PENDAFTARAN MAHASISWA BARU
</h3>

<!-- SECTION A -->
<h4 class='section-title'>A. Data Pribadi</h4>

<table class='table'>
<tr><th width='35%'>Nama Lengkap</th><td>{$data['nama_lengkap']}</td></tr>
<tr><th>NISN</th><td>{$data['nisn']}</td></tr>
<tr><th>Email</th><td>{$data['email']}</td></tr>
<tr><th>No WhatsApp</th><td>{$data['no_wa']}</td></tr>
<tr><th>Asal Sekolah</th><td>{$data['asal_sekolah']}</td></tr>
<tr><th>Alamat Domisili</th><td>{$data['alamat']}</td></tr>
<tr><th>Tempat Lahir</th><td>{$data['tempat_lahir']}</td></tr>
<tr><th>Tanggal Lahir</th><td>".tglIndo($data['tanggal_lahir'])."</td></tr>
<tr><th>Hobby</th><td>{$data['hobby']}</td></tr>
<tr><th>Minat & Bakat</th><td>{$data['minat_bakat']}</td></tr>
<tr><th>Kompetisi Pernah Diikuti</th><td>{$data['kompetisi']}</td></tr>
<tr><th>Prestasi Akademik</th><td>{$data['prestasi_akademik']}</td></tr>
<tr><th>Prestasi Non Akademik</th><td>{$data['prestasi_non_akademik']}</td></tr>
<tr><th>Program Studi Pilihan</th><td>{$data['minat_prodi']}</td></tr>
<tr><th>Status Pesantren</th><td>{$data['pesantren_status']}</td></tr>
<tr><th>Informasi Pendaftaran</th><td>{$data['info_pendaftaran']}</td></tr>
<tr><th>Status Pendaftaran</th><td><b style='color:#065f46;'>".ucfirst($data['status_pendaftaran'])."</b></td></tr>
</table>

<br><br>
<table width='100%'>
<tr>
<td width='50%' align='center'>
Diverifikasi Oleh:<br><br><br><b>$admin_name</b>
</td>
<td width='50%' align='center'>
Disetujui Oleh:<br><br><br><b>$admin_name</b>
</td>
</tr>
</table>

<!-- FOOTER -->
<div class='footer'>
Dicetak pada: <b>$tanggalCetak</b>
</div>
";

// =============================
// 🖨 RENDER DOMPDF
// =============================
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Formulir_Pendaftar_$id.pdf", ["Attachment" => false]);
?>
