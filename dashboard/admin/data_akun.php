<?php
session_start();
require_once("../../config/database.php");

// Cek Akses Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../public/login.php");
    exit;
}

// Ambil Data Admin
$nama_admin = $_SESSION['user']['nama_lengkap'];
$role = ucfirst($_SESSION['user']['role']);

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Searching
$search = "";
$where = "";
if (isset($_GET['search']) && $_GET['search'] !== "") {
    $search = escape($_GET['search']);
    $where = "WHERE nama_lengkap LIKE '%$search%' OR username LIKE '%$search%' OR role LIKE '%$search%' OR status_akun LIKE '%$search%'";
}

// Total data
$totalQuery = query("SELECT COUNT(*) AS total FROM users $where");
$totalData = $totalQuery->fetch_assoc()['total'];
$totalPage = ceil($totalData / $limit);

// Ambil data user
$users = query("SELECT * FROM users $where ORDER BY id DESC LIMIT $start, $limit");

// Action
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == "approve") {
        query("UPDATE users SET status_akun='approved' WHERE id=$id");
    } elseif ($action == "reject") {
        query("UPDATE users SET status_akun='rejected' WHERE id=$id");
    } elseif ($action == "pending") {
        query("UPDATE users SET status_akun='pending' WHERE id=$id");
    } elseif ($action == "delete") {
        query("DELETE FROM users WHERE id=$id");
    }

    header("Location: data_akun.php?success=1");
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Data Akun | Admin PMB ITS NU</title>
<link rel="icon" type="image/jpeg" href="../../assets/img/ITS.jpeg">

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/feather-icons"></script>

<style>
body{
    font-family:'Poppins',sans-serif;
    background: radial-gradient(ellipse at bottom, #0f172a 0%, #000 100%);
    color:white;
}

/* Fade-in */
.fade-in{
    opacity:0; transform:translateY(20px);
    animation:fadeIn .7s ease forwards;
}
@keyframes fadeIn{
    to{opacity:1; transform:translateY(0);}
}

/* Table Wrapper Glassmorphism */
.table-wrapper {
    background: rgba(15,23,42,0.55);
    border: 1px solid rgba(16,185,129,0.25);
    backdrop-filter: blur(14px);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 0 25px rgba(0,255,180,0.08);
}

/* Table Header */
.table-header {
    background: rgba(16,185,129,0.12);
    border-bottom: 1px solid rgba(16,185,129,0.3);
}

/* Rows */
.row-hover:hover {
    background: rgba(16,185,129,0.15) !important;
    transition: .2s;
}

/* Badges */
.badge {
    padding: 6px 12px;
    border-radius: 999px;
    font-weight: 600;
}

/* Action Buttons */
.action-btn {
    padding: 8px;
    border-radius: 10px;
    transition: 0.2s;
}
.action-btn:hover {
    transform: scale(1.12);
    box-shadow: 0 0 12px rgba(0,255,200,0.4);
}

/* Search bar */
.search-box {
    background: rgba(15,23,42,0.7);
    border: 1px solid rgba(16,185,129,0.28);
    border-radius: 10px;
    padding-left: 40px;
}

/* Search icon */
.search-icon {
    position:absolute; left:12px; top:50%; transform:translateY(-50%);
}

/* Pagination */
.page-btn {
    background: rgba(15,23,42,.8);
    border: 1px solid rgba(16,185,129,.25);
    padding: 6px 14px;
    border-radius: 8px;
    transition:.25s;
}
.page-btn:hover {
    background: rgba(16,185,129,.2);
}
.page-active {
    border-color:#10b981;
    color:#10b981;
    font-weight:700;
}
</style>
</head>

<body class="flex">

<!-- SIDEBAR -->
<?php include __DIR__ . "/sidebar_admin.php"; ?>

<!-- MAIN -->
<div class="flex-1 flex flex-col ml-64">

    <!-- HEADER -->
    <?php include __DIR__ . "/header_admin.php"; ?>

    <!-- CONTENT -->
    <main class="p-6 fade-in mt-28">

        <h1 class="text-2xl font-bold text-emerald-400 mb-6 flex items-center gap-2">
            <i data-feather="users"></i> Data Akun Pengguna
        </h1>

        <!-- Search -->
        <form method="GET" class="relative w-full max-w-sm mb-6">
            <i data-feather="search" class="search-icon text-emerald-400"></i>
            <input type="text" 
                name="search"
                value="<?= $search ?>"
                class="search-box w-full py-2 pr-3 text-white focus:ring-2 focus:ring-emerald-400 outline-none"
                placeholder="Cari nama / username / role ...">
        </form>

        <!-- TABLE WRAPPER -->
        <div class="table-wrapper overflow-x-auto fade-in">

            <table class="min-w-full">
                <thead>
                    <tr class="table-header text-emerald-400">
                        <th class="p-3 text-left">Nama</th>
                        <th class="p-3 text-left">Username</th>
                        <th class="p-3 text-center">Role</th>
                        <th class="p-3 text-center">Status Akun</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php while ($u = $users->fetch_assoc()): ?>
                    <tr class="border-b border-gray-700 row-hover">

                        <td class="p-3"><?= $u['nama_lengkap'] ?></td>
                        <td class="p-3"><?= $u['username'] ?></td>

                        <!-- ROLE -->
                        <td class="p-3 text-center">
                            <span class="badge <?= $u['role']=='admin' ? 'bg-emerald-600' : 'bg-blue-600' ?>">
                                <?= ucfirst($u['role']) ?>
                            </span>
                        </td>

                        <!-- STATUS -->
                        <td class="p-3 text-center">
                            <span class="badge
                                <?php
                                if ($u['status_akun']=='approved') echo 'bg-emerald-600';
                                elseif ($u['status_akun']=='pending') echo 'bg-yellow-500';
                                else echo 'bg-red-600';
                                ?>">
                                <?= ucfirst($u['status_akun']) ?>
                            </span>
                        </td>

                        <!-- ACTION BUTTONS -->
                        <td class="p-3 text-center">
                            <div class="flex justify-center gap-2">

                                <!-- Approve -->
                                <button onclick="verifyAction('approve', <?= $u['id'] ?>)"
                                    class="action-btn bg-emerald-600">
                                    <i data-feather="check"></i>
                                </button>

                                <!-- Pending -->
                                <button onclick="verifyAction('pending', <?= $u['id'] ?>)"
                                    class="action-btn bg-yellow-500">
                                    <i data-feather="clock"></i>
                                </button>

                                <!-- Reject -->
                                <button onclick="verifyAction('reject', <?= $u['id'] ?>)"
                                    class="action-btn bg-red-500">
                                    <i data-feather="x"></i>
                                </button>

                                <!-- Edit -->
                                <a href="edit_akun.php?id=<?= $u['id'] ?>"
                                    class="action-btn bg-blue-600">
                                    <i data-feather="edit"></i>
                                </a>

                                <!-- Delete -->
                                <button onclick="deleteAction(<?= $u['id'] ?>)"
                                    class="action-btn bg-red-700">
                                    <i data-feather="trash"></i>
                                </button>

                            </div>
                        </td>

                    </tr>
                <?php endwhile; ?>
                </tbody>

            </table>

        </div>

        <!-- PAGINATION -->
        <div class="mt-6 flex gap-2">
            <?php for ($i=1; $i <= $totalPage; $i++): ?>
                <a href="?page=<?= $i ?>" 
                    class="page-btn <?= $i==$page ? 'page-active' : 'text-gray-300' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>

    </main>

    <!-- FOOTER -->
    <?php include __DIR__ . "/footer_admin.php"; ?>

</div>


<script>
feather.replace();

/* Verifikasi */
function verifyAction(type,id){
    Swal.fire({
        title:'Yakin?',
        text:'Ubah status akun?',
        icon:'question',
        showCancelButton:true,
        confirmButtonColor:'#10B981',
        cancelButtonColor:'#d33',
        confirmButtonText:'Lanjutkan'
    }).then((r)=>{
        if(r.isConfirmed){
            window.location="data_akun.php?action="+type+"&id="+id;
        }
    });
}

/* Delete */
function deleteAction(id){
    Swal.fire({
        title:'Hapus Akun?',
        text:'Data tidak bisa dikembalikan!',
        icon:'warning',
        showCancelButton:true,
        confirmButtonColor:'#EF4444',
        cancelButtonColor:'#6B7280',
        confirmButtonText:'Hapus'
    }).then((r)=>{
        if(r.isConfirmed){
            window.location="data_akun.php?action=delete&id="+id;
        }
    });
}
</script>

</body>
</html>
