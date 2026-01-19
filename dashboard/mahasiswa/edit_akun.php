<?php
session_start();
require_once("../../config/database.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'calon_mahasiswa') {
    header("Location: ../../public/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if ($nama === '' || $username === '') {
        $error = "Nama dan Username wajib diisi!";
    } elseif ($password !== '' && $password !== $confirm) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        $passSQL = $password !== '' ? ", password='" . password_hash($password, PASSWORD_BCRYPT) . "'" : "";
        query("UPDATE users SET nama_lengkap='$nama', username='$username' $passSQL WHERE id=$user_id");
        $success = true;
    }
}

$q = query("SELECT nama_lengkap, username FROM users WHERE id=$user_id");
$u = $q->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Akun | PMB ITS NU</title>
<link rel="icon" type="image/jpeg" href="../../assets/img/ITS.jpeg">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">

<style>
body {
  font-family: 'Poppins', sans-serif;
  background: radial-gradient(circle at bottom, #0f172a, #000);
  color: white;
  overflow-x: hidden;
}

/* Neon Animated Card */
.card {
  position: relative;
  background: rgba(15,23,42,.7);
  border-radius: 16px;
  padding: 1.5rem;
  overflow: hidden;
  backdrop-filter: blur(10px);
  animation: fadeIn 0.8s ease both;
  box-shadow: 0 0 25px rgba(16,185,129,.25);
}

.card::before {
  content: "";
  position: absolute;
  inset: 0;
  padding: 2px;
  border-radius: 16px;
  background: linear-gradient(130deg, #10b981, #22d3ee, #10b981);
  background-size: 200% 200%;
  animation: neonFlow 5s linear infinite;
  mask: 
    linear-gradient(#fff 0 0) content-box, 
    linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  z-index: -1;
}

@keyframes neonFlow {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Input styling */
.input {
  width: 100%;
  padding: 0.7rem;
  border-radius: 10px;
  background: rgba(30,41,59,.8);
  border: 1px solid rgba(16,185,129,.35);
  color: white;
  transition: all .3s ease;
}
.input:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 12px rgba(16,185,129,.5);
}

/* Title */
.title-glow {
  text-shadow: 0 0 8px #10b98180;
  animation: fadeIn 1s ease;
}
</style>
</head>

<body class="flex">

<?php include __DIR__ . "/sidebar_mahasiswa.php"; ?>

<div class="flex-1 flex flex-col min-h-screen ml-64">
  <?php include __DIR__ . "/header_mahasiswa.php"; ?>

  <main class="p-6 max-w-3xl mx-auto mt-28">
    <h1 class="text-2xl font-bold text-emerald-400 mb-6 flex items-center gap-2 title-glow">
      <i data-feather="edit-3"></i> Edit Akun
    </h1>

    <div class="card">
      <form method="POST" class="space-y-5 animate-[fadeIn_1s_ease]">

        <div>
          <label class="block text-gray-300 mb-1">Nama Lengkap</label>
          <input type="text" name="nama_lengkap"
                 value="<?= htmlspecialchars($u['nama_lengkap']) ?>"
                 class="input" required>
        </div>

        <div>
          <label class="block text-gray-300 mb-1">Username</label>
          <input type="text" name="username"
                 value="<?= htmlspecialchars($u['username']) ?>"
                 class="input" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-gray-300 mb-1">Password Baru</label>
            <input type="password" name="password" class="input"
                   minlength="6" placeholder="Biarkan kosong jika tidak diubah">
          </div>
          <div>
            <label class="block text-gray-300 mb-1">Konfirmasi Password</label>
            <input type="password" name="confirm" class="input" minlength="6">
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
          <a href="akun.php" class="px-4 py-2 bg-gray-800 rounded-md border border-gray-700 hover:bg-gray-700 transition">
            Batal
          </a>
          <button class="px-4 py-2 bg-emerald-600 rounded-md hover:bg-emerald-700 font-semibold shadow-lg transition-all">
            Simpan
          </button>
        </div>
      </form>
    </div>
  </main>

  <?php include __DIR__ . "/footer_mahasiswa.php"; ?>
</div>

<script>
feather.replace();

<?php if(isset($error)): ?>
Swal.fire({
  icon: 'error',
  title: 'Oops!',
  text: '<?= $error ?>',
  background: '#0f172a',
  color: '#d1fae5'
});
<?php elseif(isset($success)): ?>
Swal.fire({
  icon: 'success',
  title: 'Berhasil!',
  text: 'Data akun berhasil diperbarui.',
  background: '#0f172a',
  color: '#d1fae5',
  timer: 1800,
  showConfirmButton: false
}).then(()=>window.location='akun.php');
<?php endif; ?>
</script>
</body>
</html>
