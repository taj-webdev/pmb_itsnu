<?php
// includes/header_front.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMB ITS NU Kalimantan</title>
    <link rel="icon" type="image/jpeg" href="../assets/img/ITS.jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            background: radial-gradient(ellipse at center, #0f172a 0%, #000000 100%);
            min-height: 100vh;
            color: #fff;
            display: flex;
            flex-direction: column;
            opacity: 0; /* awalnya transparan */
            animation: fadeInSmooth 1.2s ease-out forwards; /* animasi muncul */
        }

        /* Animasi smooth fade in */
        @keyframes fadeInSmooth {
            from {
                opacity: 0;
                transform: translateY(10px);
                filter: blur(5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
                filter: blur(0);
            }
        }

        /* Efek bintang stabil (kompatibel semua browser) */
        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent url('https://www.transparenttextures.com/patterns/stardust.png') repeat;
            animation: moveStars 180s linear infinite;
            opacity: 0.6;
            z-index: 0;
        }

        @keyframes moveStars {
            from { background-position: 0 0; }
            to { background-position: 10000px 10000px; }
        }

        /* Glass effect */
        .glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.1);
        }

        .neon {
            text-shadow:
                0 0 5px #22d3ee,
                0 0 15px #0ea5e9,
                0 0 30px #06b6d4;
        }
    </style>
</head>

<body class="relative overflow-x-hidden">
    <!-- Star background -->
    <div class="stars"></div>

    <!-- Header -->
    <header class="relative z-10 flex justify-between items-center px-6 py-4 bg-gray-900/60 backdrop-blur-lg shadow-lg border-b border-cyan-500/30">
        <div class="flex items-center space-x-3">
            <img src="../assets/img/ITS.jpeg" alt="Logo ITS NU" class="w-10 h-10 rounded-full shadow-md ring-2 ring-cyan-400">
            <h1 class="text-lg md:text-xl font-bold text-cyan-400 drop-shadow-md">PMB ITS NU</h1>
        </div>
        <div id="jam" class="text-sm md:text-base font-mono text-emerald-400 bg-gray-800 px-4 py-1 rounded-lg shadow-inner border border-emerald-500"></div>
    </header>

    <script>
        // Jam Digital
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('jam').textContent = `${h}:${m}:${s}`;
        }
        setInterval(updateClock, 1000);
        updateClock();
        feather.replace();
    </script>
</body>
</html>
