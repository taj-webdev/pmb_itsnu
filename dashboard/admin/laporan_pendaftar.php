<?php
session_start();
require_once("../../config/database.php");

// Akses admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../public/login.php");
    exit;
}

$nama_admin = $_SESSION['user']['nama_lengkap'];
$role = ucfirst($_SESSION['user']['role']);

// Pagination
$limit = 10;
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $limit;

// Pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where  = '';
if ($search !== '') {
    $esc = escape($search);
    $where = "WHERE u.nama_lengkap LIKE '%$esc%' 
              OR p.nisn LIKE '%$esc%' 
              OR p.email LIKE '%$esc%' 
              OR p.minat_prodi LIKE '%$esc%'";
}

// Total data
$rsTotal = query("SELECT COUNT(*) AS total 
                  FROM pendaftar p JOIN users u ON u.id = p.user_id
                  $where");
$totalData = (int)$rsTotal->fetch_assoc()['total'];
$totalPage = max(1, ceil($totalData / $limit));

// List data
$rs = query("SELECT p.id, p.nisn, p.email, p.no_wa, p.minat_prodi, 
             p.status_pendaftaran, u.nama_lengkap
             FROM pendaftar p
             JOIN users u ON u.id = p.user_id
             $where
             ORDER BY p.id DESC
             LIMIT $start, $limit");

// Delete fallback
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $id = (int) $_GET['id'];
    query("DELETE FROM pendaftar WHERE id=$id");
    header("Location: laporan_pendaftar.php?deleted=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Pendaftar | PMB ITS NU</title>
<link rel="icon" type="image/jpeg" href="../../assets/img/ITS.jpeg">

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/feather-icons"></script>

<style>
body{
    font-family:'Poppins',sans-serif;
    background:radial-gradient(circle at bottom,#0f172a 0%,#000 100%);
    color:#fff;
}
.fade-in{opacity:0;transform:translateY(18px);animation:fadeIn .7s ease forwards}
@keyframes fadeIn{to{opacity:1;transform:translateY(0)}}

.table-glass {
    background: rgba(15,23,42,0.55);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(16,185,129,0.18);
}

.header-glass {
    background: rgba(31,41,55,0.6);
    backdrop-filter: blur(8px);
    border-bottom:1px solid rgba(16,185,129,0.25);
}

.neon-btn {
    transition: .25s ease;
}
.neon-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 0 12px rgba(16,185,129,.5);
}

.glow-row:hover {
    background: rgba(16,185,129,0.05);
    transition: .25s;
}

</style>
</head>

<body class="flex">

<!-- SIDEBAR -->
<?php include __DIR__ . "/sidebar_admin.php"; ?>

<!-- MAIN -->
<div class="ml-64 w-full">

<!-- HEADER -->
<?php include __DIR__ . "/header_admin.php"; ?>

<!-- CONTENT -->
<main class="p-6 mt-28 fade-in">

    <!-- PAGE TITLE -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-emerald-400 flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-600/20 border border-emerald-500 text-emerald-400">
                <i data-feather="printer"></i>
            </span>
            Laporan Pendaftar
        </h1>

        <!-- CETAK -->
        <a href="cetak_laporan_pendaftar.php<?= $search!=='' ? '?search='.urlencode($search) : '' ?>"
            class="neon-btn inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 font-semibold shadow-lg">
            <i data-feather="download"></i> Cetak Laporan
        </a>
    </div>

    <!-- SEARCH BOX -->
    <form method="GET" class="mb-5">
        <div class="flex gap-2">
            <input type="text" name="search"
                value="<?= htmlspecialchars($search) ?>"
                class="w-80 px-4 py-2 rounded-lg bg-gray-900/70 border border-emerald-500/20 text-white focus:ring-2 focus:ring-emerald-400 outline-none"
                placeholder="Cari nama / NISN / email / prodi ...">

            <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 rounded-lg font-semibold neon-btn">
                Cari
            </button>

            <?php if ($search !== ''): ?>
            <a href="laporan_pendaftar.php"
                class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg">
                Reset
            </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- TABLE -->
    <div class="overflow-x-auto table-glass rounded-xl shadow-xl">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-gray-800/80 text-emerald-400 uppercase text-xs tracking-wide">
                    <th class="p-3 text-left">Nama</th>
                    <th class="p-3 text-left">NISN</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">No WA</th>
                    <th class="p-3 text-left">Prodi</th>
                    <th class="p-3 text-center">Status</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($totalData === 0): ?>
                <tr>
                    <td colspan="7" class="p-6 text-center text-gray-400 italic">
                        Tidak ada data ditemukan.
                    </td>
                </tr>
            <?php else: ?>

                <?php while($row = $rs->fetch_assoc()): ?>
                <tr class="border-t border-gray-700 glow-row">
                    <td class="p-3"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($row['nisn']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($row['email']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($row['no_wa']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($row['minat_prodi']) ?></td>

                    <td class="p-3 text-center">
                        <span class="px-3 py-1 rounded-lg text-white
                            <?php
                                if ($row['status_pendaftaran']=='approved') echo 'bg-emerald-600';
                                elseif ($row['status_pendaftaran']=='pending') echo 'bg-yellow-500';
                                else echo 'bg-red-600';
                            ?>">
                            <?= ucfirst($row['status_pendaftaran']) ?>
                        </span>
                    </td>

                    <td class="p-3 text-center">
                        <div class="flex justify-center gap-2">

                            <!-- Detail -->
                            <a href="detail_pendaftar.php?id=<?= $row['id'] ?>#formSection"
                                class="neon-btn bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded-lg flex items-center">
                                <i data-feather="eye"></i>
                            </a>

                            <!-- Cetak individual -->
                            <a href="cetak_pendaftar.php?id=<?= $row['id'] ?>"
                                class="neon-btn bg-emerald-600 hover:bg-emerald-700 px-3 py-1 rounded-lg flex items-center">
                                <i data-feather="download"></i>
                            </a>

                            <!-- Delete -->
                            <button onclick="deleteAction(<?= $row['id'] ?>)"
                                class="neon-btn bg-red-600 hover:bg-red-700 px-3 py-1 rounded-lg flex items-center">
                                <i data-feather="trash"></i>
                            </button>

                        </div>
                    </td>

                </tr>
                <?php endwhile; ?>

            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <?php $q = $search !== '' ? '&search='.urlencode($search) : ''; ?>
    <div class="mt-5 flex flex-wrap gap-2">
        <?php for ($i=1; $i <= $totalPage; $i++): ?>
            <a href="?page=<?= $i . $q ?>"
               class="px-3 py-1 rounded-lg bg-gray-800 hover:bg-gray-700 transition
                <?= $i==$page ? 'text-emerald-400 font-semibold border border-emerald-500' : 'text-gray-300' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>

</main>

<?php include __DIR__ . "/footer_admin.php"; ?>

</div>

<script>
feather.replace();

// Delete confirm
function deleteAction(id){
  Swal.fire({
    title:'Hapus Data?',
    text:'Data pendaftar & semua dokumennya akan dihapus permanen.',
    icon:'warning',
    showCancelButton:true,
    confirmButtonColor:'#EF4444',
    cancelButtonColor:'#6B7280',
    confirmButtonText:'Hapus'
  }).then((result)=>{
    if(result.isConfirmed){
        const params = new URLSearchParams(window.location.search);
        params.set('action','delete');
        params.set('id',id);
        window.location = 'laporan_pendaftar.php?' + params.toString();
    }
  });
}

// Toast sukses
<?php if (isset($_GET['deleted'])): ?>
Swal.fire({
    icon:'success',
    title:'Data berhasil dihapus',
    timer:1300,
    showConfirmButton:false
});
<?php endif; ?>
</script>

</body>
</html>
