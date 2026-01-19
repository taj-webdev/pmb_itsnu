<?php
session_start();
require_once("../../config/database.php");

// Middleware Mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'calon_mahasiswa') {
    header("Location: ../../public/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Ambil data pendaftaran milik user
$pendaftaran = query("
    SELECT * FROM pendaftar 
    WHERE user_id = $user_id 
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Ajukan Pendaftaran | PMB ITS NU</title>
<link rel="icon" type="image/jpeg" href="../../assets/img/ITS.jpeg">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Poppins',sans-serif;
    background:radial-gradient(circle at bottom,#0f172a,#000);
    color:white;
}
.fade-in{
    opacity:0;
    transform:translateY(10px);
    animation:fadeIn .55s ease forwards;
}
@keyframes fadeIn{
    to{opacity:1;transform:translateY(0);}
}
.dropdown-menu{ display:none; }
.dropdown:hover .dropdown-menu{ display:block; }
</style>
</head>

<body class="flex">

<!-- SIDEBAR -->
<?php include __DIR__ . "/sidebar_mahasiswa.php"; ?>

<!-- CONTENT WRAPPER -->
<div class="flex-1 flex flex-col min-h-screen ml-64">

    <!-- HEADER -->
    <?php include __DIR__ . "/header_mahasiswa.php"; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-6 pt-[95px] fade-in">

        <!-- TITLE + BUTTON -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-emerald-400 flex items-center gap-2">
                <i data-feather="file-text"></i> Ajukan Pendaftaran
            </h1>

            <a href="form_pendaftaran.php"
                class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 transition 
                       font-semibold flex items-center gap-2 shadow-lg">
                <i data-feather="plus"></i> Ajukan Pendaftaran
            </a>
        </div>

        <!-- TABLE WRAPPER -->
        <div class="overflow-x-auto bg-gray-900/60 border border-emerald-600/40 rounded-xl shadow-lg p-4">

            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-emerald-400 border-b border-gray-700">
                        <th class="p-3 text-left">Tanggal Daftar</th>
                        <th class="p-3 text-left">NISN</th>
                        <th class="p-3 text-left">Prodi</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center w-44">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($pendaftaran->num_rows == 0): ?>
                        <tr>
                            <td colspan="5" class="text-center p-8 text-gray-400">
                                Belum ada pengajuan pendaftaran 📝
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while($p = $pendaftaran->fetch_assoc()): ?>
                        <tr class="border-b border-gray-800 hover:bg-gray-800/40 transition">

                            <td class="p-3"><?= date("d-m-Y", strtotime($p['tanggal_daftar'])) ?></td>
                            <td class="p-3"><?= $p['nisn'] ?></td>
                            <td class="p-3"><?= $p['minat_prodi'] ?></td>

                            <td class="p-3 text-center">
                                <span class="px-3 py-1 rounded-lg font-semibold
                                    <?php
                                        if ($p['status_pendaftaran']=='approved') echo 'bg-emerald-600';
                                        elseif ($p['status_pendaftaran']=='pending') echo 'bg-yellow-500 text-black';
                                        else echo 'bg-red-600';
                                    ?>">
                                    <?= ucfirst($p['status_pendaftaran']) ?>
                                </span>
                            </td>

                            <td class="p-3">
                                <div class="flex justify-center gap-2">

                                    <!-- DETAIL -->
                                    <a href="detail_pendaftaran.php?id=<?= $p['id'] ?>"
                                        class="bg-blue-600 px-3 py-1 rounded-lg hover:bg-blue-700">
                                        <i data-feather="eye"></i>
                                    </a>

                                    <!-- EDIT DROPDOWN -->
                                    <div class="relative dropdown">
                                        <button class="bg-yellow-500 px-3 py-1 rounded-lg hover:bg-yellow-600">
                                            <i data-feather="edit"></i>
                                        </button>

                                        <div class="dropdown-menu absolute bg-gray-800 border border-gray-700 
                                            rounded-lg p-2 right-0 mt-2 w-44 z-20 shadow-xl">
                                            
                                            <a href="edit_pendaftaran.php?id=<?= $p['id'] ?>"
                                                class="block px-3 py-2 hover:bg-gray-700 rounded-md text-sm">
                                                Edit Pendaftaran
                                            </a>

                                            <a href="edit_dokumen.php?id=<?= $p['id'] ?>"
                                                class="block px-3 py-2 hover:bg-gray-700 rounded-md text-sm">
                                                Edit Dokumen
                                            </a>
                                        </div>
                                    </div>

                                    <!-- CETAK -->
                                    <a href="cetak_pendaftar.php?id=<?= $p['id'] ?>" target="_blank"
                                        class="bg-emerald-600 px-3 py-1 rounded-lg hover:bg-emerald-700">
                                        <i data-feather="printer"></i>
                                    </a>

                                    <!-- DELETE -->
                                    <button onclick="hapusPendaftaran(<?= $p['id'] ?>)"
                                        class="bg-red-600 px-3 py-1 rounded-lg hover:bg-red-700">
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
    </main>

    <!-- FOOTER -->
    <?php include __DIR__ . "/footer_mahasiswa.php"; ?>

</div>

<script>
feather.replace();

// SweetAlert Delete
function hapusPendaftaran(id){
    Swal.fire({
        title: "Hapus Pendaftaran?",
        text: "Data tidak dapat dikembalikan.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Hapus",
        cancelButtonText: "Batal",
        confirmButtonColor: "#e11d48"
    }).then((res)=>{
        if(res.isConfirmed){
            window.location.href = "hapus_pendaftaran.php?id="+id;
        }
    });
}
</script>

</body>
</html>
