<?php
session_start();
require_once("../../config/database.php");

// Middleware: hanya calon mahasiswa
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'calon_mahasiswa') {
    header("Location: ../../public/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Cek apakah user sudah ajukan pendaftaran
$cek = query("SELECT * FROM pendaftar WHERE user_id = $user_id");
$sudah_daftar = $cek->num_rows > 0;

// =======================
// SUBMIT FORM PMB
// =======================
if (isset($_POST['submit_pmb'])) {

    $nisn = escape($_POST['nisn']);
    $email = escape($_POST['email']);
    $wa = escape($_POST['no_wa']);
    $asal_sekolah = escape($_POST['asal_sekolah']);
    $alamat = escape($_POST['alamat']);
    $tempat_lahir = escape($_POST['tempat_lahir']);
    $tanggal_lahir = escape($_POST['tanggal_lahir']);
    $hobby = escape($_POST['hobby']);
    $minat_bakat = escape($_POST['minat_bakat']);
    $kompetisi = escape($_POST['kompetisi']);
    $prestasi_akademik = escape($_POST['prestasi_akademik']);
    $prestasi_non_akademik = escape($_POST['prestasi_non_akademik']);
    $minat_prodi = escape($_POST['minat_prodi']);
    $pesantren_status = escape($_POST['pesantren_status']);
    $info_pendaftaran = escape($_POST['info_pendaftaran']);

    query("INSERT INTO pendaftar (
        user_id, nisn, email, no_wa, asal_sekolah, alamat,
        tempat_lahir, tanggal_lahir, hobby, minat_bakat, kompetisi,
        prestasi_akademik, prestasi_non_akademik, minat_prodi,
        pesantren_status, info_pendaftaran
    ) VALUES (
        '$user_id', '$nisn', '$email', '$wa', '$asal_sekolah', '$alamat',
        '$tempat_lahir', '$tanggal_lahir', '$hobby', '$minat_bakat', '$kompetisi',
        '$prestasi_akademik', '$prestasi_non_akademik', '$minat_prodi',
        '$pesantren_status', '$info_pendaftaran'
    )");

    $last_id = mysqli_insert_id($conn);

    // bersihkan draft localStorage via flag (ditangani JS saat load)
    $_SESSION['clear_pmb_draft'] = true;

    header("Location: form_pendaftaran.php?upload=1&id=$last_id");
    exit;
}

// =======================
// UPLOAD DOKUMEN
// =======================
if (isset($_POST['upload_dokumen'])) {

    $pendaftar_id = intval($_POST['pendaftar_id']);

    function uploadFile($field)
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;

        $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $_FILES[$field]['name']);
        $name = time() . "_" . $safeName;

        $uploadDir = "../../uploads/pendaftar";
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }

        $path = $uploadDir . $name;
        move_uploaded_file($_FILES[$field]['tmp_name'], $path);

        return $name;
    }

    $pasfoto = uploadFile('pasfoto');
    $kk = uploadFile('kk');
    $ktp = uploadFile('ktp');
    $ijazah = uploadFile('ijazah');
    $raport = uploadFile('raport');
    $bukti = uploadFile('bukti');

    query("INSERT INTO dokumen_pendaftar (
        pendaftar_id, pasfoto, kartu_keluarga, ktp, ijazah, raport, bukti_pembayaran
    ) VALUES ('$pendaftar_id', '$pasfoto', '$kk', '$ktp', '$ijazah', '$raport', '$bukti')");

    // bersihkan draft dokumen via flag
    $_SESSION['clear_doc_draft'] = true;

    header("Location: pendaftaran.php?success=1");
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Formulir Pendaftaran | PMB ITS NU</title>
    <link rel="icon" href="../../assets/img/ITS.jpeg">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at bottom, #0f172a, #000);
            color: white;
        }

        /* Fade-in mount */
        .fade-in {
            opacity: 0;
            transform: translateY(10px);
            animation: fadeIn .5s ease forwards;
        }
        @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }

        /* Card futuristik */
        .card-global {
            background: rgba(15, 23, 42, .55);
            border: 1px solid rgba(16, 185, 129, .35);
            border-radius: 16px;
            padding: 20px;
            backdrop-filter: blur(12px);
            box-shadow: 0 0 30px rgba(16, 185, 129, .15);
        }

        /* Input */
        .input {
            padding: 12px;
            background: rgba(30, 41, 59, .7);
            border: 1px solid rgba(51, 65, 85, .7);
            border-radius: 10px;
            width: 100%;
            color: white;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 10px #10b98180;
        }

        /* Wizard steps anim */
        .step {
            opacity: 0;
            transform: translateX(40px);
            transition: all .45s ease;
        }
        .step.active {
            opacity: 1;
            transform: translateX(0);
        }

        /* Progress bar A (glow emerald) */
        .bar {
            height: 10px;
            background: rgba(16, 185, 129, .22);
            border-radius: 10px;
            overflow: hidden;
        }
        .bar-fill {
            height: 100%;
            background: #10b981;
            width: 0%;
            transition: width .5s ease;
            box-shadow: 0 0 12px #10b981, 0 0 24px #10b98170, inset 0 0 6px #10b98180;
        }

        /* File preview */
        .preview-box {
            margin-top: 8px;
            padding: 8px;
            background: rgba(15,23,42,.7);
            border: 1px solid rgba(16,185,129,.35);
            border-radius: 10px;
            display: inline-block;
        }
        .preview-box img {
            max-width: 180px;
            border-radius: 8px;
            display: block;
        }

        /* Icon breathing on active */
        .breath {
            animation: breath 1.8s ease-in-out infinite;
        }
        @keyframes breath {
            0% { text-shadow: 0 0 0px #10b981; }
            50% { text-shadow: 0 0 10px #10b981; }
            100% { text-shadow: 0 0 0px #10b981; }
        }

        /* Perbaikan khusus tampilan Upload Dokumen */
            #step2 .input {
                background: rgba(30, 41, 59, .8);
                border: 1px solid rgba(51, 65, 85, .7);
                border-radius: 8px;
                padding: 10px;
                width: 100%;
                transition: border-color .25s ease, box-shadow .25s ease;
        }
            #step2 .input:hover {
                border-color: #10b981;
        }
            #step2 img {
                width: 100%;
                height: 100%;
                object-fit: cover;
        }

    </style>
</head>

<body class="flex">

    <?php include "sidebar_mahasiswa.php"; ?>

    <!-- IMPORTANT: ml-64 agar tidak tertutup sidebar -->
    <div class="flex-1 flex flex-col min-h-screen ml-64">

        <?php include "header_mahasiswa.php"; ?>

        <!-- Centered width; ganti max-w-3xl kalau mau lebih sempit -->
        <main class="p-6 pt-[100px] fade-in max-w-4xl mx-auto w-full">

            <h1 class="text-3xl font-bold text-emerald-400 mb-10 flex items-center gap-2 justify-center">
                <i data-feather="edit-3"></i> Formulir Pendaftaran
            </h1>

            <!-- ======================= -->
            <!-- WIZARD STEP PREMIUM -->
            <!-- ======================= -->
            <div class="mb-8 text-center">
                <div class="flex justify-around items-center mb-4">

                    <div class="text-center">
                        <div id="icon1" class="text-3xl text-emerald-400 breath">
                            <i data-feather="user"></i>
                        </div>
                        <p id="label1" class="mt-1 font-semibold text-emerald-300">Biodata</p>
                    </div>

                    <div class="flex-1 mx-4 h-[2px] bg-emerald-800/50"></div>

                    <div class="text-center">
                        <div id="icon2" class="text-3xl text-gray-500">
                            <i data-feather="file-text"></i>
                        </div>
                        <p id="label2" class="mt-1 text-gray-400">Upload Dokumen</p>
                    </div>

                    <div class="flex-1 mx-4 h-[2px] bg-emerald-800/50"></div>

                    <div class="text-center">
                        <div id="icon3" class="text-3xl text-gray-500">
                            <i data-feather="check-circle"></i>
                        </div>
                        <p id="label3" class="mt-1 text-gray-400">Selesai</p>
                    </div>

                </div>

                <!-- PROGRESS BAR -->
                <div class="bar">
                    <div id="barfill" class="bar-fill"></div>
                </div>
            </div>

            <!-- ======================= -->
            <!-- STEP 1: FORM BIODATA -->
            <!-- ======================= -->
            <?php if (!$sudah_daftar && !isset($_GET['upload'])): ?>
                <div id="step1" class="card-global step active">

                    <h2 class="text-xl font-semibold text-emerald-300 mb-4">📝 Biodata & PMB</h2>

                    <form id="form_biodata" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <input required name="nisn" class="input" placeholder="NISN">
                        <input required name="email" type="email" class="input" placeholder="Email Aktif">

                        <input required name="no_wa" class="input" placeholder="No WhatsApp">
                        <input name="asal_sekolah" class="input" placeholder="Asal Sekolah">

                        <input name="tempat_lahir" class="input" placeholder="Tempat Lahir">
                        <input type="date" name="tanggal_lahir" class="input">

                        <input name="hobby" class="input" placeholder="Hobby">
                        <input name="minat_bakat" class="input" placeholder="Minat & Bakat">

                        <input name="kompetisi" class="input col-span-2" placeholder="Kompetisi Pernah Diikuti">

                        <textarea name="prestasi_akademik" class="input col-span-2"
                            placeholder="Prestasi Akademik"></textarea>

                        <textarea name="prestasi_non_akademik" class="input col-span-2"
                            placeholder="Prestasi Non Akademik"></textarea>

                        <select name="minat_prodi" class="input">
                            <option disabled selected>Pilih Program Studi</option>
                            <option>S-1 Teknik Industri</option>
                            <option>S-1 Teknik Komputer</option>
                            <option>S-1 Teknik Lingkungan</option>
                        </select>

                        <select name="pesantren_status" class="input">
                            <option disabled selected>Status Pesantren</option>
                            <option>Pernah</option>
                            <option>Tidak Pernah</option>
                            <option>Minat</option>
                            <option>Belum Minat</option>
                        </select>

                        <textarea name="alamat" class="input col-span-2"
                            placeholder="Alamat Lengkap"></textarea>

                        <textarea name="info_pendaftaran" class="input col-span-2"
                            placeholder="Dapat Info Pendaftaran Dari?"></textarea>

                        <button name="submit_pmb"
                            class="col-span-2 bg-emerald-600 py-3 rounded-lg font-bold hover:bg-emerald-700">
                            Simpan & Lanjutkan Upload Dokumen
                        </button>

                    </form>

                </div>
            <?php endif; ?>

            <!-- ======================= -->
            <!-- STEP 2: UPLOAD DOKUMEN -->
            <!-- ======================= -->
            <?php if (isset($_GET['upload'])): ?>
                <div id="step2" class="card-global step active">

                    <h2 class="text-xl font-semibold text-emerald-300 mb-6 text-center flex items-center justify-center gap-2">📄 Upload Dokumen</h2>

                    <form id="form_dokumen" method="POST" enctype="multipart/form-data"
                          class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <input type="hidden" name="pendaftar_id" value="<?= $_GET['id'] ?>">

                        <?php  
                        // fungsi komponen upload dengan preview rapi
                        function uploadCard($label, $name) {
                            echo "
                            <div class='bg-gray-900/40 border border-emerald-600/30 rounded-xl p-4 hover:border-emerald-400/70 transition-all duration-200'>
                            <p class='text-emerald-300 font-semibold mb-2'>$label</p>
                            <input type='file' name='$name' required class='input' onchange='previewFile(this, \"$name-preview\")'>
                            <div id='$name-preview' class='mt-3 flex justify-center items-center h-[180px] bg-gray-800/60 border border-emerald-500/20 rounded-lg overflow-hidden'>
                                <p class='text-gray-500 text-sm italic'>Belum ada file</p>
                            </div>
                        </div>
                        ";
                    }

                    uploadCard("Pas Foto", "pasfoto");
                    uploadCard("Kartu Keluarga", "kk");
                    uploadCard("KTP / Kartu Pelajar", "ktp");
                    uploadCard("Ijazah", "ijazah");
                    uploadCard("Raport", "raport");
                    uploadCard("Bukti Pembayaran", "bukti");
                    ?>

                    <div class="col-span-2 flex justify-center mt-4">
                        <button name="upload_dokumen"
                            class="bg-emerald-600 py-3 px-8 rounded-lg font-bold text-lg hover:bg-emerald-700 transition-all">
                            <i data-feather="check-circle"></i> Selesaikan Pendaftaran
                        </button>
                    </div>
                </form>

            </div>
            <?php endif; ?>

        </main>

        <?php include "footer_mahasiswa.php"; ?>

    </div>

    <script>
        feather.replace();

        // -------- Wizard Progress + Icon States --------
        const bar = document.getElementById("barfill");
        const icon1 = document.getElementById("icon1");
        const icon2 = document.getElementById("icon2");
        const icon3 = document.getElementById("icon3");
        const label1 = document.getElementById("label1");
        const label2 = document.getElementById("label2");
        const label3 = document.getElementById("label3");

        const onUploadStep = <?= isset($_GET['upload']) ? 'true' : 'false' ?>;

        function setStep(step) {
            if (step === 1) {
                bar.style.width = "33%";
                icon1.classList.add("text-emerald-400","breath");
                label1.classList.add("text-emerald-300");
                icon2.classList.remove("text-emerald-400","breath");
                icon2.classList.add("text-gray-500");
                label2.classList.remove("text-emerald-300");
                label2.classList.add("text-gray-400");
                icon3.classList.remove("text-emerald-400","breath");
                icon3.classList.add("text-gray-500");
                label3.classList.remove("text-emerald-300");
                label3.classList.add("text-gray-400");
            } else if (step === 2) {
                bar.style.width = "66%";
                icon1.classList.add("text-emerald-400");
                label1.classList.add("text-emerald-300");
                icon2.classList.remove("text-gray-500");
                icon2.classList.add("text-emerald-400","breath");
                label2.classList.remove("text-gray-400");
                label2.classList.add("text-emerald-300");
                icon3.classList.remove("text-emerald-400","breath");
                icon3.classList.add("text-gray-500");
                label3.classList.remove("text-emerald-300");
                label3.classList.add("text-gray-400");
            } else if (step === 3) {
                bar.style.width = "100%";
                icon1.classList.add("text-emerald-400");
                label1.classList.add("text-emerald-300");
                icon2.classList.add("text-emerald-400");
                label2.classList.add("text-emerald-300");
                icon3.classList.remove("text-gray-500");
                icon3.classList.add("text-emerald-400","breath");
                label3.classList.remove("text-gray-400");
                label3.classList.add("text-emerald-300");
            }
        }

        setStep(onUploadStep ? 2 : 1);

        // -------- Auto-save per field (localStorage) --------
        // namespace kunci supaya aman
        const NS = "pmb_";
        function autosaveBind(scopeSelector) {
            document.querySelectorAll(scopeSelector + " input, " + scopeSelector + " textarea, " + scopeSelector + " select").forEach(field => {
                if (!field.name) return;

                // Restore
                const saved = localStorage.getItem(NS + field.name);
                if (saved && field.type !== "file") field.value = saved;

                // Save on input
                field.addEventListener("input", () => {
                    if (field.type !== "file") {
                        localStorage.setItem(NS + field.name, field.value);
                    }
                });
            });
        }

        // Step 1 autosave
        if (!onUploadStep && document.getElementById("form_biodata")) {
            autosaveBind("#form_biodata");
        }

        // Step 2 autosave (simpan nama file yang dipilih saja)
        if (onUploadStep && document.getElementById("form_dokumen")) {
            document.querySelectorAll("#form_dokumen input[type='file']").forEach(input => {
                input.addEventListener("change", () => {
                    localStorage.setItem(NS + input.name, input.files.length ? input.files[0].name : "");
                });
            });
        }

        // Bersihkan draft saat sukses submit (flag dari PHP)
        <?php if (!empty($_SESSION['clear_pmb_draft'])): ?>
            Object.keys(localStorage).forEach(k => { if (k.startsWith("pmb_")) localStorage.removeItem(k); });
            <?php unset($_SESSION['clear_pmb_draft']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['clear_doc_draft'])): ?>
            Object.keys(localStorage).forEach(k => { if (k.startsWith("pmb_")) localStorage.removeItem(k); });
            <?php unset($_SESSION['clear_doc_draft']); ?>
        <?php endif; ?>

        // --- FUNGSI PREVIEW FILE BARU ---
        function previewFile(input, previewId) {
            const file = input.files[0];
            const box = document.getElementById(previewId);
            box.innerHTML = ''; // bersihkan preview lama

        if (!file) {
            box.innerHTML = '<p class="text-gray-500 text-sm italic">Belum ada file</p>';
            return;
        }

        const ext = file.name.split('.').pop().toLowerCase();
        const reader = new FileReader();

        reader.onload = e => {
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                // preview image
                box.innerHTML = `<img src="${e.target.result}" alt="Preview" class="rounded-lg shadow-md w-full h-auto">`;
            } else if (ext === 'pdf') {
                // preview PDF
                box.innerHTML = `<iframe src="${e.target.result}" class="w-full h-[320px] border border-emerald-500/20 rounded-lg"></iframe>`;
            } else {
                // file lain, tampilkan nama saja
                box.innerHTML = `<p class="text-gray-300 text-xs">${file.name}</p>`;
            }
        };

        reader.readAsDataURL(file);
    }

        // -------- Smooth step mount (if both existed) --------
        const s1 = document.getElementById("step1");
        const s2 = document.getElementById("step2");
        if (s1 && !onUploadStep) {
            setTimeout(() => s1.classList.add("active"), 60);
        }
        if (s2 && onUploadStep) {
            setTimeout(() => s2.classList.add("active"), 60);
        }
    </script>

</body>
</html>
