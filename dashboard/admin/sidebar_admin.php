<aside class="fixed left-0 top-0 h-full w-64 bg-gray-900/90 border-r border-emerald-600/40 
    p-5 backdrop-blur-xl flex flex-col justify-between z-30">

    <div>
        <div class="flex items-center space-x-3 mb-10">
            <img src="../../assets/img/ITS.jpeg" class="w-10 h-10 rounded-full border-2 border-emerald-400">
            <h2 class="text-emerald-400 font-bold text-lg">PMB ITS NU</h2>
        </div>

        <nav class="space-y-4">

            <a href="index.php"
               class="flex items-center gap-2 
               <?= $current_page == 'index.php' ? 'text-emerald-400 font-semibold' : 'text-gray-300 hover:text-emerald-400' ?> transition">
                <i data-feather="home"></i> Dashboard
            </a>

            <a href="data_akun.php"
              class="flex items-center gap-2 transition
               <?= $current_page == 'data_akun.php' ? 'text-emerald-400 font-semibold' : 'text-gray-300 hover:text-emerald-400' ?>">
                <i data-feather="users">
                class="<?= $current_page == 'data_akun.php' ? 'stroke-emerald-400' : 'stroke-gray-300' ?>"></i>Data Akun
            </a>

            <a href="data_pendaftar.php"
               class="flex items-center gap-2 transition
               <?= $current_page == 'data_pendaftar.php' ? 'text-emerald-400 font-semibold' : 'text-gray-300 hover:text-emerald-400' ?> transition">
                <i data-feather="file-text">
                class="<?= $current_page == 'data_pendaftar.php' ? 'stroke-emerald-400' : 'stroke-gray-300' ?>"></i>Data Pendaftar
            </a>

            <a href="laporan_pendaftar.php"
               class="flex items-center gap-2 transition
               <?= $current_page == 'laporan_pendaftar.php' ? 'text-emerald-400 font-semibold' : 'text-gray-300 hover:text-emerald-400' ?> transition">
                <i data-feather="printer">
                 class="<?= $current_page == 'laporan_pendaftar.php' ? 'stroke-emerald-400' : 'stroke-gray-300' ?>"></i>Laporan Pendaftar
            </a>

        </nav>
    </div>

    <button onclick="logoutConfirm()"
        class="flex items-center gap-2 text-gray-300 hover:text-red-400 transition cursor-pointer">
        <i data-feather="log-out"></i> Logout
    </button>
</aside>

<script>
function logoutConfirm() {
    Swal.fire({
        title: 'Yakin mau Logout bro? 😎',
        text: 'Sesi kamu akan berakhir dan kembali ke halaman login.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Logout!',
        cancelButtonText: 'Batal',
        background: '#0f172a',
        color: '#d1fae5',
        backdrop: 'rgba(0,0,0,0.7)'
    }).then((result) => {
        if (result.isConfirmed) {

            Swal.fire({
                title: 'Logout Berhasil!',
                text: 'Sampai jumpa lagi bro 🔥',
                icon: 'success',
                confirmButtonColor: '#10b981',
                timer: 1800,
                showConfirmButton: false
            });

            setTimeout(() => {
                window.location.href = '../../public/logout.php';
            }, 1600);

        }
    });
}
</script>
