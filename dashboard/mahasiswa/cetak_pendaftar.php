<?php
session_start();
require_once("../../config/database.php");
require_once("../../vendor/autoload.php");

use Dompdf\Dompdf;

// Cek login mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'calon_mahasiswa') {
    die("Akses ditolak.");
}

$user_id = $_SESSION['user']['id'];
$nama_mahasiswa = $_SESSION['user']['nama_lengkap'];

// Ambil data pendaftar
$q = query("
    SELECT p.*, u.nama_lengkap 
    FROM pendaftar p
    JOIN users u ON u.id = p.user_id
    WHERE user_id = $user_id
    LIMIT 1
");

if ($q->num_rows == 0) {
    die("Belum ada data pendaftaran.");
}

$data = $q->fetch_assoc();

// Fungsi tanggal Indo
function tglIndo($date) {
    $bulan = [
        1=>'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ];
    $ex = explode('-', $date);
    return $ex[2].' '.$bulan[(int)$ex[1]].' '.$ex[0];
}

$hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$hariIni = $hari[date('w')];
$tanggalCetak = $hariIni . ", " . tglIndo(date('Y-m-d'));

// Path logo untuk watermark
$logoPath = str_replace("\\", "/", realpath(__DIR__ . "/../../assets/img/ITS.jpeg"));

// =============================
// HTML PDF
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
.section-title {
    font-weight: bold;
    font-size: 14px;
    color: #065f46;
    margin-top: 20px;
    border-left: 5px solid #10b981;
    padding-left: 6px;
}
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
Yang Mengajukan:<br><br><br><b>{$data['nama_lengkap']}</b>
</td>
<td width='50%' align='center'>
Dicetak Oleh:<br><br><br><b>{$data['nama_lengkap']}</b>
</td>
</tr>
</table>

<!-- FOOTER -->
<div class='footer'>
Dicetak pada: <b>$tanggalCetak</b>
</div>
";

// =============================
// DOMPDF RENDER
// =============================
$pdf = new Dompdf();
$pdf->loadHtml($html);
$pdf->setPaper('A4', 'portrait');
$pdf->render();
$pdf->stream("Formulir_Pendaftar_$nama_mahasiswa.pdf", ["Attachment" => false]);
?>
