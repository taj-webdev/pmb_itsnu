<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<aside class="fixed left-0 top-0 h-screen w-64 bg-gray-900/90 backdrop-blur-xl
border-r border-emerald-600/40 p-5 flex flex-col justify-between z-40">

    <div>
        <div class="flex items-center space-x-3 mb-10">
            <img src="../../assets/img/ITS.jpeg"
                 class="w-10 h-10 rounded-full border-2 border-emerald-400 object-cover">
            <h2 class="text-emerald-400 font-bold text-lg">PMB ITS NU</h2>
        </div>

        <nav class="space-y-4">
            <a href="index.php"
               class="flex items-center space-x-2 text-emerald-400 hover:text-white <?= (basename($_SERVER['PHP_SELF'])=='index.php') ? 'font-semibold' : '' ?>">
                <i data-feather="home"></i><span>Dashboard</span>
            </a>

            <a href="akun.php"
               class="flex items-center space-x-2 text-gray-300 hover:text-emerald-400 <?= (basename($_SERVER['PHP_SELF'])=='akun.php') ? 'text-emerald-400 font-semibold' : '' ?>">
                <i data-feather="user"></i><span>Data Akun</span>
            </a>

            <a href="pendaftaran.php"
               class="flex items-center space-x-2 text-gray-300 hover:text-emerald-400 <?= (basename($_SERVER['PHP_SELF'])=='pendaftaran.php') ? 'text-emerald-400 font-semibold' : '' ?>">
                <i data-feather="edit-3"></i><span>Ajukan Pendaftaran</span>
            </a>
        </nav>
    </div>

    <!-- LOGOUT FIXED + CLICKABLE -->
    <button onclick="logoutConfirm()"
        class="flex items-center space-x-2 text-gray-300 hover:text-red-400 mt-6 
               relative z-50 cursor-pointer select-none">
        <i data-feather='log-out'></i><span>Logout</span>
    </button>

</aside>

<script>
function logoutConfirm(){
    Swal.fire({
        title: 'Yakin mau Logout bro? 😎',
        text: 'Sesi kamu akan berakhir.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#ef4444',
        cancelButtonText: 'Batal',
        confirmButtonText: 'Ya, Logout!',
        background: '#0f172a',
        color: '#d1fae5'
    }).then((result)=>{
        if(result.isConfirmed){
            Swal.fire({
                title: 'Logout Berhasil!',
                icon: 'success',
                confirmButtonColor: '#10b981',
                timer: 1100,
                showConfirmButton: false
            });

            setTimeout(()=>{ 
                window.location.href='../../public/logout.php';
            }, 1100);
        }
    });
}

feather.replace();
</script>