<?php
session_start();
require_once("../../config/database.php");
require_once("../../vendor/autoload.php");

use Dompdf\Dompdf;

// 🔒 Cek akses admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Akses ditolak.");
}

$admin_name = $_SESSION['user']['nama_lengkap'];

// 🔍 Pencarian (opsional)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where  = '';
if ($search !== '') {
    $esc = escape($search);
    $where = "WHERE u.nama_lengkap LIKE '%$esc%' 
              OR p.nisn LIKE '%$esc%' 
              OR p.email LIKE '%$esc%' 
              OR p.minat_prodi LIKE '%$esc%'";
}

// 🧾 Ambil semua data pendaftar
$rs = query("
    SELECT p.id, p.nisn, p.email, p.no_wa, p.minat_prodi, p.status_pendaftaran, u.nama_lengkap
    FROM pendaftar p
    JOIN users u ON u.id = p.user_id
    $where
    ORDER BY p.id DESC
");

// 🗓 Format tanggal Indo
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
$logoPath = realpath(__DIR__ . "/../../assets/img/ITS.jpeg");

// =============================
// 🧾 HTML PDF
// =============================
$html = "
<style>
@page { margin: 40px 40px 60px 40px; }
body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 11.5px;
    color: #222;
    background: url('file://$logoPath') no-repeat center center;
    background-size: 350px;
    opacity: 0.98;
}

/* HEADER */
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
}
.kop-sub {
    font-size: 11px;
    color: #374151;
}

/* TABEL */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
.table th {
    background: #10b981;
    color: white;
    border: 1px solid #059669;
    padding: 6px;
    text-align: center;
}
.table td {
    border: 1px solid #9ca3af;
    padding: 6px;
    text-align: center;
    background: rgba(249, 250, 251, 0.9);
}
.table td:first-child {
    text-align: center;
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

</style>

<!-- HEADER -->
<div class='header'>
    <div class='kop-title'>Institut Teknologi dan Sains Nahdlatul Ulama Kalimantan</div>
    <div class='kop-sub'>Jl. RTA Milono No.Km. 3, Langkai, Kec. Pahandut, Kota Palangka Raya</div>
    <div class='kop-sub'>Kalimantan Tengah 73111 | Telp. 0822-5154-8898</div>
    <div class='kop-sub'>Email: info@itsnu-kalimantan.ac.id</div>
</div>

<h3 style='text-align:center; margin-top:5px; color:#065f46;'>LAPORAN DATA PENDAFTAR MAHASISWA BARU</h3>

<table class='table'>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Lengkap</th>
            <th>NISN</th>
            <th>Email</th>
            <th>No WhatsApp</th>
            <th>Program Studi</th>
            <th>Status Pendaftaran</th>
        </tr>
    </thead>
    <tbody>";

$no = 1;
if ($rs->num_rows > 0) {
    while ($row = $rs->fetch_assoc()) {
        $statusColor = match($row['status_pendaftaran']) {
            'approved' => '#10b981',
            'pending' => '#eab308',
            default => '#ef4444',
        };
        $html .= "
        <tr>
            <td>{$no}</td>
            <td>{$row['nama_lengkap']}</td>
            <td>{$row['nisn']}</td>
            <td>{$row['email']}</td>
            <td>{$row['no_wa']}</td>
            <td>{$row['minat_prodi']}</td>
            <td style='color: $statusColor; font-weight:bold;'>".ucfirst($row['status_pendaftaran'])."</td>
        </tr>";
        $no++;
    }
} else {
    $html .= "
        <tr>
            <td colspan='7' style='text-align:center; color:gray;'>Tidak ada data ditemukan.</td>
        </tr>";
}

$html .= "
    </tbody>
</table>

<br><br>
<table width='100%'>
<tr>
<td width='50%' align='center'>
Diverifikasi Oleh:<br><br><br><b>$admin_name</b>
</td>
<td width='50%' align='center'>
Dicetak Oleh:<br><br><br><b>$admin_name</b>
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
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("Laporan_Pendaftar_ITSNU.pdf", ["Attachment" => false]);
?>
