<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$nama_admin = $nama_admin ?? ($_SESSION['user']['nama_lengkap'] ?? 'Admin');
$role       = $role       ?? ucfirst($_SESSION['user']['role'] ?? 'admin');
?>
<header class="fixed left-64 top-0 right-0 bg-gray-800/80 backdrop-blur-md 
    border-b border-emerald-600/30 px-6 py-4 flex justify-between items-center z-20">

    <!-- LEFT -->
    <div class="flex items-center space-x-3">
        <div class="text-3xl animate-bounce">👋</div>
        <div>
            <h1 class="text-xl font-semibold text-emerald-400">Hai, <?= htmlspecialchars($nama_admin) ?>!</h1>
            <p class="text-sm text-gray-400">Role: <?= htmlspecialchars($role) ?></p>
        </div>
    </div>

    <!-- RIGHT AREA -->
    <div class="flex items-center gap-4 relative">

        <!-- BUTTON NOTIF -->
        <button id="notifBtn" class="relative bg-gray-900 p-2 rounded-lg border border-emerald-500 hover:bg-gray-700 transition">
            <i data-feather="bell" class="text-emerald-400"></i>

            <!-- BADGE -->
            <span id="notifBadge" class="absolute -top-1 -right-1 text-xs rounded-full px-1.5 font-semibold"
                style="background:linear-gradient(135deg,#06b6d4,#10b981); 
                       color:#001218;
                       box-shadow:0 6px 18px rgba(16,185,129,0.28);
                       display:none;">
                0
            </span>
        </button>

        <!-- JAM -->
        <div id="jam" class="text-emerald-400 font-mono text-lg bg-gray-900 px-5 py-2 rounded-lg
            border border-emerald-500 shadow-inner"></div>
    </div>

    <!-- DROPDOWN -->
    <div id="notifDropdown"
        class="hidden absolute right-6 top-20 w-80 bg-gray-900 border border-emerald-600/40 
               rounded-xl p-3 shadow-xl z-50">

        <p class="font-semibold text-emerald-400 mb-2 flex items-center gap-2">
            <i data-feather="bell"></i> Notifikasi
            <span id="markAllBtn" class="ml-auto text-xs underline cursor-pointer text-gray-300">
                Tandai sudah dibaca
            </span>
        </p>

        <div id="notifList" class="space-y-2 text-gray-300 text-sm max-h-60 overflow-auto">
            <div class="text-gray-400 text-sm italic">Memuat notifikasi...</div>
        </div>
    </div>
</header>

<script>
// ===============================
// JAM DIGITAL
// ===============================
function updateClock(){
    const now = new Date();
    document.getElementById("jam").textContent =
        now.toLocaleTimeString("id-ID", { hour12:false });
}
setInterval(updateClock, 1000);
updateClock();


// ===============================
// API URL
// ===============================
const apiUrl = '/pmb_itsnu/dashboard/admin/api_notifikasi.php';
let latestNotifs = [];

// ===============================
// ESCAPER
// ===============================
function escapeHtml(s){
    return (s+'').replace(/[&<>"']/g, c => ({
        '&':'&amp;',
        '<':'&lt;',
        '>':'&gt;',
        '"':'&quot;',
        "'":'&#39;'
    }[c]));
}


// ===============================
// RENDER NOTIF
// ===============================
function renderNotifs(data){
    const list = document.getElementById('notifList');
    const badge = document.getElementById('notifBadge');

    if (!data || !data.length){
        list.innerHTML = `<div class="text-gray-400 text-sm italic">Tidak ada notifikasi.</div>`;
        badge.style.display = "none";
        return;
    }

    latestNotifs = data;
    const unread = data.filter(n => n.status_baca === 'belum_dibaca').length;

    if (unread > 0){
        badge.textContent = unread;
        badge.style.display = "inline-block";
        badge.style.boxShadow = "0 6px 22px rgba(16,185,129,0.32), 0 0 28px rgba(6,182,212,0.12)";
    } else {
        badge.style.display = "none";
    }

    list.innerHTML = "";
    data.forEach(n => {
        const time = new Date(n.created_at)
            .toLocaleString('id-ID',{ hour12:false, hour:'2-digit', minute:'2-digit' });

        const item = document.createElement("div");
        item.className = "p-2 rounded-md hover:bg-gray-800/60 transition flex gap-2 items-start";
        item.innerHTML = `
            <div class="w-2 h-8 rounded-full"
                 style="background:${n.status_baca==='belum_dibaca' ? '#10b981' : '#374151'}"></div>
            <div class="flex-1">
                <div class="text-sm">${escapeHtml(n.pesan)}</div>
                <div class="text-xs text-gray-400 mt-1">${time}</div>
            </div>
        `;
        list.appendChild(item);
    });
}


// ===============================
// FETCH NOTIF + SESSION FIX
// ===============================
async function fetchNotifs(){
    try {
        const res = await fetch(apiUrl, {
            method: "GET",
            credentials: "include" // ===== 💥 FIX PENTING
        });

        const json = await res.json();
        renderNotifs(json.data || []);

    } catch (error){
        console.error("Notif fetch error:", error);
    }
}


// ===============================
// MARK ALL READ
// ===============================
async function markAllRead(){
    try {
        await fetch(apiUrl, {
            method: "POST",
            headers: { "Content-Type":"application/x-www-form-urlencoded" },
            body: "action=mark_all",
            credentials: "include"   // ===== 💥 FIX PENTING
        });
        fetchNotifs();
    } catch (error){
        console.error("markAll error:", error);
    }
}


// ===============================
// DROPDOWN
// ===============================
document.getElementById('notifBtn').addEventListener('click', ()=>{
    const dd = document.getElementById('notifDropdown');
    dd.classList.toggle('hidden');

    if (!dd.classList.contains("hidden")){
        markAllRead();
    }
});

document.getElementById('markAllBtn').addEventListener('click', (e)=>{
    e.stopPropagation();
    markAllRead();
});


// ===============================
// POLLING PER 5 DETIK
// ===============================
fetchNotifs();
setInterval(fetchNotifs, 5000);

</script>
