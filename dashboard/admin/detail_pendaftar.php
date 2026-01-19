<?php
session_start();
require_once("../../config/database.php");

// Cek Akses Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../public/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: data_pendaftar.php");
    exit;
}

$id = intval($_GET['id']);

// Ambil data pendaftar & user
$q = query("
    SELECT p.*, u.nama_lengkap
    FROM pendaftar p
    JOIN users u ON u.id = p.user_id
    WHERE p.id = $id
");

if ($q->num_rows == 0) {
    echo "<script>
        alert('Data pendaftar tidak ditemukan');
        window.location='data_pendaftar.php';
    </script>";
    exit;
}

$data = $q->fetch_assoc();

// Action verifikasi dari halaman detail
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if (in_array($action, ['approved', 'pending', 'rejected'])) {
        query("UPDATE pendaftar SET status_pendaftaran='$action' WHERE id=$id");
        header("Location: detail_pendaftar.php?id=$id&updated=1");
        exit;
    }
}

// Ambil dokumen
$dok = query("
    SELECT * FROM dokumen_pendaftar 
    WHERE pendaftar_id = $id
")->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Pendaftar | PMB ITS NU</title>
<link rel="icon" type="image/jpeg" href="../../assets/img/ITS.jpeg">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root{
        --emerald: #10b981;
        --glass-bg: rgba(15,23,42,0.62);
        --muted: #94a3b8;
    }

    body{
        font-family: 'Poppins', sans-serif;
        background: radial-gradient(circle at bottom, #0f172a 0%, #000 100%);
        color: #fff;
        -webkit-font-smoothing:antialiased;
        -moz-osx-font-smoothing:grayscale;
    }

    /* layout */
    .wrap { display:flex; min-height:100vh; }
    .main { flex:1; margin-left:16rem; } /* leave space for sidebar */
    header.fixed-header{
        position:fixed; left:16rem; right:0; top:0; height:64px; z-index:60;
        background: rgba(17,24,39,0.85); backdrop-filter: blur(6px);
        border-bottom:1px solid rgba(16,185,129,0.08);
    }
    main.content { padding: 100px 28px 48px; max-width:1100px; margin:0 auto; }

    /* card */
    .glass {
        background: var(--glass-bg);
        border: 1px solid rgba(16,185,129,0.12);
        border-radius: 12px;
        padding: 18px;
        box-shadow: 0 6px 24px rgba(2,6,23,0.6);
    }

    .section-title {
        color: var(--emerald);
        font-weight:700;
        display:flex; align-items:center; gap:10px;
        margin-bottom:12px;
    }

    /* 2-column grid for fields */
    .grid-2 {
        display:grid;
        grid-template-columns: repeat(2, 1fr);
        gap:14px;
    }
    @media (max-width: 820px) {
        .grid-2 { grid-template-columns: 1fr; }
        .main { margin-left: 14rem; }
        header.fixed-header { left:14rem; }
    }

    /* field item */
    .field {
        background: rgba(7,10,14,0.35);
        border: 1px solid rgba(255,255,255,0.03);
        padding:12px;
        border-radius:10px;
    }
    .field .label { color: var(--muted); font-size:13px; margin-bottom:6px; display:block; }
    .field .value { color:#e6fdf4; font-weight:600; word-break:break-word; }

    /* status badge */
    .badge {
        display:inline-block;
        padding:8px 14px;
        border-radius:999px;
        font-weight:700;
        box-shadow: 0 6px 18px rgba(16,185,129,0.06), inset 0 -6px 20px rgba(255,255,255,0.02);
        backdrop-filter: blur(4px);
    }

    /* neon pill actions (chosen A) */
    .neon-pill {
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:10px 14px;
        border-radius:999px;
        font-weight:700;
        background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(255,255,255,0.02));
        border:1px solid rgba(16,185,129,0.28);
        color: #d7fff0;
        transition: transform .15s ease, box-shadow .15s ease;
        box-shadow: 0 6px 18px rgba(16,185,129,0.06);
    }
    .neon-pill:hover { transform:translateY(-3px); box-shadow: 0 20px 40px rgba(16,185,129,0.14), 0 0 22px rgba(16,185,129,0.28); }

    /* small helper */
    .muted { color: var(--muted); font-size:13px; }
    .sep { height:1px; background: rgba(255,255,255,0.03); margin:12px 0; border-radius:1px; }

    /* dokumen grid */
    .docs-grid { display:grid; grid-template-columns: repeat(2, 1fr); gap:14px; }
    @media (max-width:820px) { .docs-grid { grid-template-columns: 1fr; } }

    .doc-item {
        display:flex; flex-direction:column; gap:8px;
        background: rgba(7,10,14,0.36);
        border: 1px solid rgba(255,255,255,0.03);
        padding:10px; border-radius:10px; align-items:center;
    }
    .doc-thumb {
        width: 180px; height: 128px; border-radius:8px;
        background:#06121a; display:flex; align-items:center; justify-content:center;
        overflow:hidden; border:1px solid rgba(255,255,255,0.03);
    }
    .doc-thumb img { width:100%; height:100%; object-fit:cover; display:block; }

    .doc-meta { width:100%; text-align:center; }
    .doc-meta .fname { font-weight:600; font-size:13px; color:#e6fff6; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .doc-meta .fsmall { color:var(--muted); font-size:12px; margin-top:6px; }

    /* Download link */
    .download-btn {
        display:inline-flex; align-items:center; gap:8px;
        padding:8px 12px; border-radius:8px; font-weight:700;
        background: linear-gradient(90deg, rgba(16,185,129,0.12), rgba(16,185,129,0.06));
        border:1px solid rgba(16,185,129,0.18); color:#0d1f17;
        text-decoration:none;
    }

    /* tab buttons */
    .tabs { display:flex; gap:8px; margin-bottom:16px; }
    .tabs button {
        padding:10px 14px; border-radius:10px; font-weight:700; border:1px solid rgba(255,255,255,0.03);
        background: rgba(255,255,255,0.02); color: #cdeedd;
    }
    .tabs button.active { background: linear-gradient(90deg, rgba(16,185,129,0.12), rgba(16,185,129,0.04)); color:var(--emerald); border-color: rgba(16,185,129,0.28); box-shadow: 0 8px 26px rgba(16,185,129,0.06); }

    /* footer area */
    .btn-back { display:inline-block; padding:10px 14px; border-radius:10px; background:#0b1220; border:1px solid rgba(255,255,255,0.03); color:#cfeee2; text-decoration:none; }

    /* animations */
    .fade-in { opacity:0; transform:translateY(12px); animation:fadeUp .5s ease forwards; }
    @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
</style>

</head>
<body class="wrap">

<!-- Sidebar include (left) -->
<?php include __DIR__ . "/sidebar_admin.php"; ?>

<!-- Main content -->
<div class="main">

    <!-- header include -->
    <?php include __DIR__ . "/header_admin.php"; ?>

    <main class="content">

        <div class="tabs" role="tablist" aria-label="Sections">
            <button id="btnForm" class="active" onclick="switchTab('form')">Formulir Pendaftaran</button>
            <button id="btnDoc" onclick="switchTab('dokumen')">Dokumen Upload</button>
        </div>

        <!-- FORM SECTION -->
        <section id="formSection" class="glass fade-in" style="margin-bottom:18px;">
            <div class="flex items-start justify-between gap-4" style="align-items:center;">
                <div>
                    <div class="section-title"><i data-feather="file-text"></i> Informasi Formulir</div>
                    <div class="muted">Detail lengkap formulir pendaftar — tampil rapi dalam dua kolom.</div>
                </div>

                <div style="display:flex; gap:10px; align-items:center;">
                    <?php
                        $status = $data['status_pendaftaran'];
                        $badgeText = ucfirst($status);
                    ?>
                    <span class="badge" style="<?php
                        if ($status == 'approved') echo 'background:linear-gradient(90deg, rgba(16,185,129,0.16), rgba(16,185,129,0.06)); color:#042f21;';
                        elseif ($status == 'pending') echo 'background:linear-gradient(90deg, rgba(245,158,11,0.14), rgba(245,158,11,0.04)); color:#4b2f03;';
                        else echo 'background:linear-gradient(90deg, rgba(239,68,68,0.12), rgba(239,68,68,0.04)); color:#3b0b0b;';
                    ?>"><?=
                        $badgeText
                    ?></span>
                </div>
            </div>

            <div class="sep" style="margin-top:12px;"></div>

            <!-- ACTIONS -->
            <div style="display:flex; gap:10px; margin:12px 0 18px;">
                <button class="neon-pill" onclick="verifikasiAction('approved', <?= $data['id'] ?>)">
                    <i data-feather="check" style="color:var(--emerald)"></i> Approve
                </button>

                <button class="neon-pill" onclick="verifikasiAction('pending', <?= $data['id'] ?>)" style="border-color: rgba(245,158,11,0.28); box-shadow: 0 8px 28px rgba(245,158,11,0.06);">
                    <i data-feather="clock" style="color:#f59e0b"></i> Pending
                </button>

                <button class="neon-pill" onclick="verifikasiAction('rejected', <?= $data['id'] ?>)" style="border-color: rgba(239,68,68,0.22); box-shadow: 0 8px 28px rgba(239,68,68,0.05);">
                    <i data-feather="x" style="color:#fb7185"></i> Reject
                </button>
            </div>

            <!-- GRID 2-Column -->
            <div class="grid-2" style="margin-top:6px;">
                <?php
                function fieldItem($label, $value){
                    $val = $value !== null && $value !== '' ? htmlspecialchars($value) : '<span class="muted">-</span>';
                    return "
                    <div class='field'>
                        <span class='label'>{$label}</span>
                        <div class='value'>{$val}</div>
                    </div>";
                }

                echo fieldItem("Nama Lengkap", $data['nama_lengkap']);
                echo fieldItem("NISN", $data['nisn']);
                echo fieldItem("Email", $data['email']);
                echo fieldItem("Nomor WhatsApp", $data['no_wa']);
                echo fieldItem("Asal Sekolah", $data['asal_sekolah']);
                echo fieldItem("Alamat / Domisili", $data['alamat']);
                echo fieldItem("Tempat Lahir", $data['tempat_lahir']);
                echo fieldItem("Tanggal Lahir", $data['tanggal_lahir']);
                echo fieldItem("Hobby", $data['hobby']);
                echo fieldItem("Minat & Bakat", $data['minat_bakat']);
                echo fieldItem("Kompetisi Pernah Diikuti", $data['kompetisi']);
                echo fieldItem("Prestasi Akademik", $data['prestasi_akademik']);
                echo fieldItem("Prestasi Non Akademik", $data['prestasi_non_akademik']);
                echo fieldItem("Minat Program Studi", $data['minat_prodi']);
                echo fieldItem("Status Pesantren", $data['pesantren_status']);
                echo fieldItem("Informasi Pendaftaran", $data['info_pendaftaran']);
                ?>
            </div>

            <div style="margin-top:18px; display:flex; justify-content:space-between; align-items:center;">
                <a href="data_pendaftar.php" class="btn-back">← Kembali</a>
                <div class="muted">Terakhir diupdate: <strong class="text-sm" style="color:#dfffe8;"><?= htmlspecialchars($data['tanggal_daftar'] ?? '') ?></strong></div>
            </div>
        </section>

        <!-- DOKUMEN SECTION -->
        <section id="dokumenSection" class="glass fade-in hidden" aria-hidden="true">
            <div class="section-title"><i data-feather="folder"></i> Dokumen Upload</div>
            <div class="muted" style="margin-bottom:12px;">Preview dokumen yang diupload oleh calon mahasiswa. Klik tombol <em>Download</em> untuk mendapatkan file.</div>

            <div class="docs-grid">
                <?php
                    function dokRowUI($label, $file){
                        // keep original path logic (UI-only change)
                        if (!$file) {
                            return "
                            <div class='doc-item'>
                                <div class='doc-thumb'><div style='color:rgba(255,255,255,0.06);font-size:12px'>No file</div></div>
                                <div class='doc-meta'>
                                    <div class='fname'>{$label}</div>
                                    <div class='fsmall' style='margin-top:6px;color:#fca5a5'>Belum diupload</div>
                                </div>
                            </div>";
                        }

                        $path = "../../uploads/pendaftar" . $file;
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);

                        $thumb = $isImage
                            ? "<img src='{$path}' alt='{$label}'>"
                            : "<div style='color:var(--muted);font-size:13px;padding:6px 8px;'>File: {$ext}</div>";

                        $download = "<a class='download-btn' href='{$path}' download><i data-feather='download'></i><span>Download</span></a>";

                        return "
                        <div class='doc-item'>
                            <div class='doc-thumb'>{$thumb}</div>
                            <div class='doc-meta'>
                                <div class='fname'>{$label}</div>
                                <div class='fsmall'>{$file}</div>
                                <div style='margin-top:8px'>{$download}</div>
                            </div>
                        </div>";
                    }

                    echo dokRowUI("Pas Foto", $dok['pasfoto'] ?? null);
                    echo dokRowUI("Kartu Keluarga", $dok['kartu_keluarga'] ?? null);
                    echo dokRowUI("KTP", $dok['ktp'] ?? null);
                    echo dokRowUI("Ijazah", $dok['ijazah'] ?? null);
                    echo dokRowUI("Raport", $dok['raport'] ?? null);
                    echo dokRowUI("Bukti Pembayaran", $dok['bukti_pembayaran'] ?? null);
                ?>
            </div>

            <div style="margin-top:16px; display:flex; justify-content:flex-start;">
                <button onclick="switchTab('form')" class="btn-back">← Kembali ke Formulir</button>
            </div>
        </section>

    </main>

    <?php include __DIR__ . "/footer_admin.php"; ?>

</div>
</div>

<script>
    feather.replace();

    // Tab switching with smooth transitions
    function switchTab(tab){
        const form = document.getElementById('formSection');
        const dok = document.getElementById('dokumenSection');
        const btnForm = document.getElementById('btnForm');
        const btnDoc = document.getElementById('btnDoc');

        if(tab === 'form'){
            form.classList.remove('hidden');
            dok.classList.add('hidden');
            form.style.opacity = 0;
            setTimeout(()=> form.style.opacity = 1, 40);
            btnForm.classList.add('active');
            btnDoc.classList.remove('active');
        } else {
            dok.classList.remove('hidden');
            form.classList.add('hidden');
            dok.style.opacity = 0;
            setTimeout(()=> dok.style.opacity = 1, 40);
            btnDoc.classList.add('active');
            btnForm.classList.remove('active');
        }
    }

    // confirmation for status change (kept behavior)
    function verifikasiAction(type, id){
        Swal.fire({
            title: 'Yakin?',
            text: 'Ubah status pendaftaran?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, lakukan'
        }).then((result)=>{
            if (result.isConfirmed){
                window.location.href = "detail_pendaftar.php?id="+id+"&action="+type;
            }
        });
    }

    // show success toast when updated
    <?php if (isset($_GET['updated'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Status diperbarui!',
        timer: 1200,
        showConfirmButton: false,
        background: '#0f172a',
        color: '#d1fae5'
    });
    <?php endif; ?>

</script>

</body>
</html>
