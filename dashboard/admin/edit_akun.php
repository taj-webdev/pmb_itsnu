<?php
session_start();
require_once("../../config/database.php");

// Cek admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../public/login.php");
    exit;
}

// Ambil ID
if (!isset($_GET['id'])) {
    header("Location: data_akun.php");
    exit;
}

$id = intval($_GET['id']);

// Ambil data user
$user = query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
if (!$user) {
    header("Location: data_akun.php?notfound=1");
    exit;
}

$success = false;
$error = "";

// PROSES UPDATE
if (isset($_POST['update'])) {

    $nama  = escape($_POST['nama']);
    $username = escape($_POST['username']);
    $role = escape($_POST['role']);
    $status = escape($_POST['status']);

    $check = query("SELECT * FROM users WHERE username='$username' AND id!=$id");
    if ($check->num_rows > 0) {
        $error = "Username sudah digunakan!";
    } else {
        query("UPDATE users SET 
                nama_lengkap='$nama',
                username='$username',
                role='$role',
                status_akun='$status'
               WHERE id=$id");

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Edit Akun | PMB ITS NU</title>
<link rel="icon" type="image/jpeg" href="../../assets/img/ITS.jpeg" />
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">

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
@keyframes fadeIn{
    to{ opacity:1; transform:translateY(0); }
}

/* Glass Neon Card */
.neon-card{
    background: rgba(15,23,42,0.55);
    backdrop-filter: blur(14px);
    border: 1px solid rgba(16,185,129,0.28);
    border-radius: 18px;
    padding: 24px;
    box-shadow: 0 0 25px rgba(16,185,129,0.12), inset 0 0 18px rgba(16,185,129,0.08);
    transition: 0.3s;
}
.neon-card:hover{
    transform: translateY(-4px);
    box-shadow: 0 0 35px rgba(16,185,129,0.25), inset 0 0 20px rgba(16,185,129,0.15);
}

/* Input */
.input-box{
    width:100%;
    padding:.75rem;
    border-radius:10px;
    background:rgba(15,23,42,0.8);
    border:1px solid rgba(255,255,255,0.15);
    color:white;
    transition:.25s;
}
.input-box:focus{
    border-color:#10b981;
    box-shadow:0 0 10px rgba(16,185,129,0.35);
    outline:none;
}

/* Label */
.label{
    color:#cbd5e1;
    font-weight:600;
}

/* Button */
.btn-save{
    width:100%;
    padding:.85rem;
    border-radius:12px;
    background:#10b981;
    font-weight:700;
    transition:.25s;
}
.btn-save:hover{
    background:#059669;
    box-shadow:0 0 18px rgba(16,185,129,0.45);
}

</style>
</head>

<body class="bg-[#0f172a]">

<!-- Sidebar -->
<?php include __DIR__ . "/sidebar_admin.php"; ?>

<!-- Main -->
<div class="flex-1 flex flex-col">

    <div class="ml-64">
    <?php include __DIR__ . "/header_admin.php"; ?>

    <main class="mt-28 px-6 fade-in mb-20">

        <h1 class="text-2xl font-bold text-emerald-400 mb-6 flex items-center gap-2">
            <i data-feather="edit"></i> Edit Akun Pengguna
        </h1>

        <!-- CARD FORM -->
        <div class="max-w-xl mx-auto neon-card">

            <!-- SweetAlert ERROR -->
            <?php if ($error): ?>
            <script>
                Swal.fire({
                    icon:'error',
                    title:'Gagal!',
                    text:'<?= $error ?>',
                    background:'#0f172a',
                    color:'#fff',
                    confirmButtonColor:'#ef4444'
                });
            </script>
            <?php endif; ?>

            <!-- SweetAlert SUCCESS -->
            <?php if ($success): ?>
            <script>
                Swal.fire({
                    icon:'success',
                    title:'Berhasil!',
                    text:'Data akun berhasil diperbarui.',
                    background:'#0f172a',
                    color:'#d1fae5',
                    confirmButtonColor:'#10b981'
                }).then(()=>{ window.location='data_akun.php'; });
            </script>
            <?php endif; ?>

            <form method="POST" autocomplete="off">

                <!-- NAMA -->
                <label class="label mb-1">Nama Lengkap</label>
                <input type="text" name="nama" required
                    value="<?= $user['nama_lengkap'] ?>"
                    class="input-box mb-4">

                <!-- USERNAME -->
                <label class="label mb-1">Username</label>
                <input type="text" name="username" required
                    value="<?= $user['username'] ?>"
                    class="input-box mb-4">

                <!-- ROLE -->
                <label class="label mb-1">Role</label>
                <select name="role" class="input-box mb-4">
                    <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                    <option value="calon_mahasiswa" <?= $user['role']=='calon_mahasiswa'?'selected':'' ?>>Calon Mahasiswa</option>
                </select>

                <!-- STATUS -->
                <label class="label mb-1">Status Akun</label>
                <select name="status" class="input-box mb-6">
                    <option value="approved" <?= $user['status_akun']=='approved'?'selected':'' ?>>Approved</option>
                    <option value="pending" <?= $user['status_akun']=='pending'?'selected':'' ?>>Pending</option>
                    <option value="rejected" <?= $user['status_akun']=='rejected'?'selected':'' ?>>Rejected</option>
                </select>

                <button type="submit" name="update" class="btn-save">
                    Simpan Perubahan
                </button>

            </form>

        </div>
    </main>

    <?php include __DIR__ . "/footer_admin.php"; ?>

    </div>
</div>

<script> feather.replace(); </script>

</body>
</html>
