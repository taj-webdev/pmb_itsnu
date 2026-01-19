<?php
require_once("../config/database.php");
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = escape($_POST['username']);
    $password = escape($_POST['password']);

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['status_akun'] === 'rejected') {
                $error = "Akun Anda ditolak. Hubungi admin untuk informasi lebih lanjut.";
            } elseif ($user['status_akun'] === 'pending' && $user['role'] === 'calon_mahasiswa') {
                $error = "Akun Anda masih menunggu verifikasi admin.";
            } else {
                $_SESSION['user'] = $user;
                if ($user['role'] === 'admin') {
                    header("Location: ../dashboard/admin/index.php");
                } else {
                    header("Location: ../dashboard/mahasiswa/index.php");
                }
                exit;
            }
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<?php include_once("../includes/header_front.php"); ?>

<main class="relative z-10 flex justify-center items-center min-h-[85vh] px-4">
    <!-- Spinner Overlay -->
    <div id="loadingSpinner" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="flex flex-col items-center">
            <div class="w-16 h-16 border-4 border-emerald-400 border-t-transparent rounded-full animate-spin mb-4"></div>
            <p class="text-emerald-400 font-semibold animate-pulse">Memproses Login...</p>
        </div>
    </div>

    <!-- Card Login -->
    <div class="glass rounded-2xl shadow-2xl p-8 md:p-10 w-full max-w-md border border-emerald-500/40 
        transition-all duration-500 hover:shadow-[0_0_25px_#22c55e70] hover:scale-[1.02]">
        
        <div class="flex flex-col items-center mb-6">
            <img src="../assets/img/ITS.jpeg" alt="Logo ITS NU" class="w-20 h-20 rounded-full border-4 border-emerald-400 mb-3 shadow-[0_0_20px_#22c55e90]">
            <h2 class="text-2xl font-bold text-emerald-400 neon">Login Sistem PMB ITS NU</h2>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-600/20 border border-red-500 text-red-400 text-sm p-3 mb-4 rounded-lg">
                ⚠️ <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm" autocomplete="off">
            <div class="mb-4 text-left">
                <label class="block text-sm mb-1 text-gray-300">Username</label>
                <input type="text" name="username" required
                    class="w-full p-3 rounded-lg bg-gray-900/60 border border-gray-700 text-white 
                    focus:outline-none focus:ring-2 focus:ring-emerald-400 placeholder-gray-500"
                    placeholder="Masukkan Username">
            </div>

            <div class="mb-6 text-left">
                <label class="block text-sm mb-1 text-gray-300">Password</label>
                <input type="password" name="password" required
                    class="w-full p-3 rounded-lg bg-gray-900/60 border border-gray-700 text-white 
                    focus:outline-none focus:ring-2 focus:ring-emerald-400 placeholder-gray-500"
                    placeholder="Masukkan Password">
            </div>

            <button type="submit" 
                class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-3 rounded-lg 
                shadow-lg transition transform hover:-translate-y-1 hover:shadow-[0_0_25px_#22c55e70]">
                Login Sekarang
            </button>
        </form>

        <p class="mt-6 text-gray-400 text-sm text-center">
            Belum punya akun? <a href="register.php" class="text-emerald-400 hover:underline">Daftar Sekarang</a>
        </p>
    </div>
</main>

<script>
    // Spinner aktif ketika tombol login diklik
    document.getElementById('loginForm').addEventListener('submit', function() {
        document.getElementById('loadingSpinner').classList.remove('hidden');
    });
</script>

<?php include_once("../includes/footer_front.php"); ?>
