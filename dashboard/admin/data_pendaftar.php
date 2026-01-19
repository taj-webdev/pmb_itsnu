<?php
session_start();
require_once("../../config/database.php");

// Cek Akses Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../public/login.php");
    exit;
}

$nama_admin = $_SESSION['user']['nama_lengkap'];
$role = ucfirst($_SESSION['user']['role']);

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Searching
$search = "";
$where = "";
if (!empty($_GET['search'])) {
    $search = escape($_GET['search']);
    $where = "WHERE u.nama_lengkap LIKE '%$search%' OR p.nisn LIKE '%$search%' 
              OR p.email LIKE '%$search%' OR p.minat_prodi LIKE '%$search%'";
}

// Hitung total data
$qTotal = query("SELECT COUNT(*) AS total FROM pendaftar p 
                JOIN users u ON u.id = p.user_id $where");
$totalData = $qTotal->fetch_assoc()['total'];
$totalPage = ceil($totalData / $limit);

// Ambil data pendaftar
$data = query(
    "SELECT p.*, u.nama_lengkap 
     FROM pendaftar p
     JOIN users u ON u.id = p.user_id
     $where ORDER BY p.id DESC LIMIT $start, $limit"
);

// Action (Approve / Pending / Reject / Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === "approved") {
        query("UPDATE pendaftar SET status_pendaftaran='approved' WHERE id=$id");
    } elseif ($action === "pending") {
        query("UPDATE pendaftar SET status_pendaftaran='pending' WHERE id=$id");
    } elseif ($action === "rejected") {
        query("UPDATE pendaftar SET status_pendaftaran='rejected' WHERE id=$id");
    } elseif ($action === "delete") {
        query("DELETE FROM pendaftar WHERE id=$id");
    }

    header("Location: data_pendaftar.php?success=1");
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Pendaftar | Admin PMB ITS NU</title>
<link rel="icon" type="image/jpeg" href="../../assets/img/ITS.jpeg">

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/feather-icons"></script>

<style>
body{
    font-family:'Poppins',sans-serif;
    background: radial-gradient(circle at bottom,#0f172a,#000);
    color:white;
}
.fade-in{
    opacity:0;
    transform:translateY(18px);
    animation:fadeIn .7s ease forwards;
}
@keyframes fadeIn{to{opacity:1;transform:translateY(0)}}

.table-glass{
    background:rgba(15,23,42,0.55);
    backdrop-filter:blur(12px);
    border:1px solid rgba(16,185,129,0.25);
}
.table-glass tr:hover{
    background:rgba(255,255,255,0.05);
}

.badge{
    padding:6px 14px;
    border-radius:999px;
    font-weight:600;
    font-size:.85rem;
}
.neon-card{
    background:rgba(15,23,42,0.5);
    border:1px solid rgba(16,185,129,0.28);
    border-radius:16px;
    padding:18px;
    box-shadow:0 0 22px rgba(16,185,129,0.15);
}
.action-btn{
    padding:6px 10px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:.2s;
}
.action-btn:hover{
    transform:scale(1.08);
}
.search-box{
    background:rgba(15,23,42,0.6);
    border:1px solid rgba(255,255,255,0.15);
    backdrop-filter:blur(6px);
}
.search-box:focus{
    border-color:#10b981;
    box-shadow:0 0 12px rgba(16,185,129,0.4);
    outline:none;
}
</style>

</head>
<body class="flex">

<!-- Sidebar -->
<?php include __DIR__ . "/sidebar_admin.php"; ?>

<!-- Main -->
<div class="ml-64 w-full">

    <?php include __DIR__ . "/header_admin.php"; ?>

    <main class="p-6 mt-28 fade-in">

        <div class="flex items-center gap-2 mb-6">
            <h1 class="text-2xl font-bold text-emerald-400 flex items-center gap-2">
                <i data-feather="file-text"></i> Data Pendaftar
            </h1>
        </div>

        <!-- Search -->
        <form method="GET" class="mb-6">
            <div class="flex gap-3">
                <input type="text" name="search" value="<?= $search ?>"
                    class="w-80 px-4 py-2 rounded-lg search-box text-white"
                    placeholder="Cari nama / NISN / email / prodi ...">
                <button class="px-5 bg-emerald-600 rounded-lg hover:bg-emerald-700 font-semibold">
                    Cari
                </button>
            </div>
        </form>

        <!-- Table Wrapper -->
        <div class="overflow-x-auto neon-card">

            <table class="min-w-full table-glass rounded-xl">
                <thead>
                    <tr class="bg-gray-800/60 text-emerald-400 text-sm">
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
                <?php while ($d = $data->fetch_assoc()): ?>
                <tr class="border-t border-gray-700/40 text-sm">
                    <td class="p-3"><?= $d['nama_lengkap'] ?></td>
                    <td class="p-3"><?= $d['nisn'] ?></td>
                    <td class="p-3"><?= $d['email'] ?></td>
                    <td class="p-3"><?= $d['no_wa'] ?></td>
                    <td class="p-3"><?= $d['minat_prodi'] ?></td>

                    <td class="p-3 text-center">
                        <?php if ($d['status_pendaftaran']=='approved'): ?>
                            <span class="badge bg-emerald-600">Approved</span>
                        <?php elseif ($d['status_pendaftaran']=='pending'): ?>
                            <span class="badge bg-yellow-500 text-black">Pending</span>
                        <?php else: ?>
                            <span class="badge bg-red-600">Rejected</span>
                        <?php endif; ?>
                    </td>

                    <td class="p-3">
                        <div class="flex justify-center gap-2">

                            <!-- Approve -->
                            <button onclick="confirmAction('approved', <?= $d['id'] ?>)"
                                class="action-btn bg-emerald-500 hover:bg-emerald-600">
                                <i data-feather="check"></i>
                            </button>

                            <!-- Pending -->
                            <button onclick="confirmAction('pending', <?= $d['id'] ?>)"
                                class="action-btn bg-yellow-500 hover:bg-yellow-600 text-black">
                                <i data-feather="clock"></i>
                            </button>

                            <!-- Reject -->
                            <button onclick="confirmAction('rejected', <?= $d['id'] ?>)"
                                class="action-btn bg-red-500 hover:bg-red-600">
                                <i data-feather="x"></i>
                            </button>

                            <!-- Detail -->
                            <a href="detail_pendaftar.php?id=<?= $d['id'] ?>"
                                class="action-btn bg-blue-600 hover:bg-blue-700">
                                <i data-feather="eye"></i>
                            </a>

                            <!-- Delete -->
                            <button onclick="deleteAction(<?= $d['id'] ?>)"
                                class="action-btn bg-red-700 hover:bg-red-800">
                                <i data-feather="trash"></i>
                            </button>

                        </div>
                    </td>

                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

        </div>

        <!-- Pagination -->
        <div class="mt-6 flex gap-2">
            <?php for ($i=1; $i <= $totalPage; $i++): ?>
                <a href="?page=<?= $i ?>"
                    class="px-3 py-1 rounded bg-gray-800 hover:bg-gray-700 
                    <?= $i==$page ? 'text-emerald-400 font-bold border border-emerald-500' : 'text-gray-300' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>

    </main>

    <?php include __DIR__ . "/footer_admin.php"; ?>

</div>


<script>
feather.replace();

function confirmAction(type, id){
    Swal.fire({
        title: 'Yakin?',
        text: 'Ubah status pendaftaran?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, lakukan'
    }).then((result)=>{
        if (result.isConfirmed){
            window.location.href = "data_pendaftar.php?action="+type+"&id="+id;
        }
    });
}

function deleteAction(id){
    Swal.fire({
        title: 'Hapus Data?',
        text: "Data tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Hapus'
    }).then((result)=>{
        if (result.isConfirmed){
            window.location.href = "data_pendaftar.php?action=delete&id="+id;
        }
    });
}
</script>

</body>
</html>
