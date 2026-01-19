<?php
require_once("../config/database.php");
session_start();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = escape($_POST['nama_lengkap']);
    $username = escape($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = escape($_POST['role']);

    // Admin langsung approved, mahasiswa pending
    $status = ($role === 'admin') ? 'approved' : 'pending';

    // Cek username duplikat
    $check = query("SELECT * FROM users WHERE username='$username'");
    if ($check->num_rows > 0) {
        $msg = "❌ Username sudah digunakan!";
    } else {

        // INSERT USER BARU
        $insert = query("INSERT INTO users (nama_lengkap, username, password, role, status_akun)
                         VALUES ('$nama', '$username', '$password', '$role', '$status')");

        if ($insert) {

            // 🔥 AMBIL ID USER BARU
            $newUserId = $conn->insert_id;

            // ============================================
            // 🔔 NOTIFIKASI KE ADMIN (REAL & VALID)
            // ============================================
            $pesan_admin = "Akun baru terdaftar: $nama ($username)";

            query("
                INSERT INTO notifikasi (user_id, pesan, tipe)
                SELECT id, '$pesan_admin', 'akun'
                FROM users
                WHERE role = 'admin'
            ");

            // ============================================
            // 🔔 NOTIFIKASI KE CALON MAHASISWA
            // ============================================
            if ($role === 'calon_mahasiswa') {
                $pesan_user = "Akun kamu berhasil dibuat dan sedang menunggu persetujuan admin.";

                query("
                    INSERT INTO notifikasi (user_id, pesan, tipe)
                    VALUES ($newUserId, '$pesan_user', 'akun')
                ");
            }

            // ============================================
            // END NOTIFIKASI
            // ============================================

            $msg = "✅ Registrasi berhasil! Silakan login.";
        } else {
            $msg = "❌ Gagal registrasi, coba lagi.";
        }
    }
}
?>

<?php include_once("../includes/header_front.php"); ?>

<main class="relative z-10 flex justify-center items-center min-h-[85vh] px-4">

    <!-- Spinner Overlay -->
    <div id="loadingSpinner" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="flex flex-col items-center">
            <div class="w-16 h-16 border-4 border-cyan-400 border-t-transparent rounded-full animate-spin mb-4"></div>
            <p class="text-cyan-400 font-semibold animate-pulse">Memproses Registrasi...</p>
        </div>
    </div>

    <!-- Card Register -->
    <div class="glass rounded-2xl shadow-2xl p-8 md:p-10 w-full max-w-md border border-cyan-500/40 
        transition-all duration-500 hover:shadow-[0_0_25px_#06b6d470] hover:scale-[1.02]">

        <div class="flex flex-col items-center mb-6">
            <img src="../assets/img/ITS.jpeg" alt="Logo ITS NU" class="w-20 h-20 rounded-full border-4 border-cyan-400 mb-3 shadow-[0_0_20px_#06b6d470]">
            <h2 class="text-2xl font-bold text-cyan-400 neon">Registrasi Akun PMB ITS NU</h2>
        </div>

        <?php if ($msg): ?>
            <div class="bg-gray-800/60 border border-cyan-400 text-cyan-300 text-sm p-3 mb-4 rounded-lg">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="registerForm" autocomplete="off">
            <div class="mb-4 text-left">
                <label class="block text-sm mb-1 text-gray-300">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required
                    class="w-full p-3 rounded-lg bg-gray-900/60 border border-gray-700 text-white 
                    focus:outline-none focus:ring-2 focus:ring-cyan-400 placeholder-gray-500"
                    placeholder="Masukkan Nama Lengkap">
            </div>

            <div class="mb-4 text-left">
                <label class="block text-sm mb-1 text-gray-300">Username</label>
                <input type="text" name="username" required
                    class="w-full p-3 rounded-lg bg-gray-900/60 border border-gray-700 text-white 
                    focus:outline-none focus:ring-2 focus:ring-cyan-400 placeholder-gray-500"
                    placeholder="Masukkan Username">
            </div>

            <div class="mb-4 text-left">
                <label class="block text-sm mb-1 text-gray-300">Password</label>
                <input type="password" name="password" required
                    class="w-full p-3 rounded-lg bg-gray-900/60 border border-gray-700 text-white 
                    focus:outline-none focus:ring-2 focus:ring-cyan-400 placeholder-gray-500"
                    placeholder="Masukkan Password">
            </div>

            <div class="mb-6 text-left">
                <label class="block text-sm mb-1 text-gray-300">Role</label>
                <select name="role" required
                    class="w-full p-3 rounded-lg bg-gray-900/60 border border-gray-700 text-white 
                    focus:outline-none focus:ring-2 focus:ring-cyan-400">
                    <option value="calon_mahasiswa">Calon Mahasiswa</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit" 
                class="w-full bg-cyan-500 hover:bg-cyan-600 text-white font-semibold py-3 rounded-lg 
                shadow-lg transition transform hover:-translate-y-1 hover:shadow-[0_0_25px_#06b6d470]">
                Daftar Sekarang
            </button>
        </form>

        <p class="mt-6 text-gray-400 text-sm text-center">
            Sudah punya akun? <a href="login.php" class="text-cyan-400 hover:underline">Login Sekarang</a>
        </p>
    </div>
</main>

<script>
    // Spinner aktif ketika tombol daftar diklik
    document.getElementById('registerForm').addEventListener('submit', function() {
        document.getElementById('loadingSpinner').classList.remove('hidden');
    });
</script>

<?php include_once("../includes/footer_front.php"); ?>
