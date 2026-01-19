<?php include_once("../includes/header_front.php"); ?>

<!-- Konten Utama -->
<main class="relative z-10 flex flex-col justify-center items-center text-center min-h-[85vh] px-4 select-none">
    <div class="glass parallax fade-in rounded-2xl shadow-2xl p-10 md:p-16 w-full max-w-2xl border border-cyan-500/30 
        transition-all duration-700 ease-in-out hover:shadow-[0_0_30px_#22d3ee80] hover:scale-[1.02]">
        
        <div class="flex justify-center mb-6 animate-fadeInSlow">
            <img src="../assets/img/ITS.jpeg" alt="Logo PMB ITS NU" 
                class="w-28 h-28 rounded-full border-4 border-cyan-400 shadow-[0_0_20px_#22d3ee80] hover:rotate-3 hover:scale-105 transition duration-500">
        </div>

        <h1 class="text-2xl md:text-3xl font-extrabold text-white neon mb-2">
            SELAMAT DATANG DI SISTEM
        </h1>
        <h2 class="text-3xl md:text-4xl font-extrabold text-cyan-400 neon mb-6 tracking-wide animate-pulse">
            PMB ITS NU KALIMANTAN
        </h2>

        <p class="text-gray-300 mb-8 max-w-lg mx-auto leading-relaxed">
            Silakan 
            <a href="login.php" class="text-blue-400 hover:underline font-semibold hover:text-blue-300 transition">Login</a> 
            atau 
            <a href="register.php" class="text-green-400 hover:underline font-semibold hover:text-green-300 transition">Register</a> 
            untuk melanjutkan proses pendaftaran Anda ke kampus inovatif ITS NU Kalimantan 🚀
        </p>

        <div class="flex justify-center space-x-6">
            <a href="login.php" 
                class="group relative px-6 py-2 bg-blue-600 text-white font-semibold rounded-xl 
                shadow-lg transition duration-500 ease-in-out transform hover:-translate-y-1 
                hover:shadow-[0_0_25px_#2563ebaa] hover:bg-blue-700">
                <span class="relative z-10">Login</span>
                <span class="absolute inset-0 rounded-xl bg-blue-400 opacity-0 group-hover:opacity-20 blur-lg transition-all"></span>
            </a>
            <a href="register.php" 
                class="group relative px-6 py-2 bg-green-500 text-white font-semibold rounded-xl 
                shadow-lg transition duration-500 ease-in-out transform hover:-translate-y-1 
                hover:shadow-[0_0_25px_#22c55eaa] hover:bg-green-600">
                <span class="relative z-10">Register</span>
                <span class="absolute inset-0 rounded-xl bg-green-400 opacity-0 group-hover:opacity-20 blur-lg transition-all"></span>
            </a>
        </div>
    </div>
</main>

<!-- Animasi tambahan -->
<style>
    @keyframes fadeInSlow {
        0% {opacity: 0; transform: scale(0.95);}
        100% {opacity: 1; transform: scale(1);}
    }
    .animate-fadeInSlow {
        animation: fadeInSlow 1.5s ease forwards;
    }
</style>

<?php include_once("../includes/footer_front.php"); ?>
