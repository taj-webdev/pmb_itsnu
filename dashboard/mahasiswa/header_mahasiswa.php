<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$nama = $_SESSION['user']['nama_lengkap'];
$role = ucfirst($_SESSION['user']['role']);
$user_id = $_SESSION['user']['id'];
// (existing status queries left as-is in original file if needed)
?>
<header class="fixed top-0 left-64 right-0 h-[72px] 
    bg-gray-800/90 backdrop-blur-md border-b border-emerald-600/40 
    px-6 flex justify-between items-center shadow-lg z-30">

    <div class="flex items-center space-x-3">
        <div class="text-3xl animate-bounce">👋</div>
        <div>
            <h1 class="text-xl font-semibold text-emerald-400">Hai, <?= htmlspecialchars($nama) ?>!</h1>
            <p class="text-sm text-gray-400"><?= htmlspecialchars($role) ?></p>
        </div>
    </div>

    <div class="flex items-center gap-6">
        <div class="relative">
            <button id="notifBtn" class="relative text-emerald-400 hover:text-white p-2 rounded-lg">
                <i data-feather="bell"></i>
                <span id="notifBadge" style="display:none; position:absolute; top:-6px; right:-6px; background:linear-gradient(135deg,#06b6d4,#10b981); color:#001218; padding:2px 6px; font-size:11px; border-radius:999px; box-shadow:0 8px 22px rgba(16,185,129,0.22);">0</span>
            </button>

            <div id="notifDropdown" class="hidden absolute right-0 mt-3 w-80 bg-gray-900 border border-gray-700 rounded-xl p-4 shadow-xl z-50">
                <p class="text-emerald-400 font-semibold mb-2 flex items-center gap-2">Notifikasi
                    <span id="markAllBtn" class="ml-auto text-xs underline cursor-pointer text-gray-300">Tandai sudah dibaca</span>
                </p>
                <div id="notifList" class="space-y-2 text-gray-300 max-h-60 overflow-auto">
                    <div class="text-gray-400 text-sm italic">Memuat notifikasi...</div>
                </div>
            </div>
        </div>

        <!-- JAM -->
        <div id="jam" class="text-emerald-400 font-mono text-lg bg-gray-900 px-4 py-2 rounded-lg border border-emerald-500 shadow-inner"></div>
    </div>
</header>

<script>
function updateClock() {
    const now = new Date();
    document.getElementById("jam").innerHTML =
        now.toLocaleTimeString('id-ID', {hour12:false});
}
setInterval(updateClock, 1000);
updateClock();

const apiUrl = '/dashboard/mahasiswa/api_notifikasi.php';
let latestNotifs = [];

function renderNotifs(data){
    const list = document.getElementById('notifList');
    const badge = document.getElementById('notifBadge');

    if (!data || !data.length) {
        list.innerHTML = '<div class="text-gray-400 text-sm italic">Tidak ada notifikasi.</div>';
        badge.style.display = 'none';
        return;
    }

    latestNotifs = data;
    const unread = data.filter(n => n.status_baca === 'belum_dibaca').length;
    if (unread > 0) {
        badge.textContent = unread;
        badge.style.display = 'inline-block';
    } else { badge.style.display = 'none'; }

    list.innerHTML = '';
    data.forEach(n => {
        const time = new Date(n.created_at).toLocaleString('id-ID', { hour12:false, hour:'2-digit', minute:'2-digit' });
        const item = document.createElement('div');
        item.className = 'p-2 rounded-md hover:bg-gray-800/60 transition flex gap-2 items-start';
        item.innerHTML = `
            <div class="w-2 h-8 rounded-full" style="background:${n.status_baca==='belum_dibaca' ? '#10b981' : '#374151'}"></div>
            <div class="flex-1">
                <div class="text-sm">${escapeHtml(n.pesan)}</div>
                <div class="text-xs text-gray-400 mt-1">${time}</div>
            </div>`;
        list.appendChild(item);
    });
}

function escapeHtml(s){ return (s+'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

async function fetchNotifs(){
    try {
        const res = await fetch(apiUrl);
        if (!res.ok) throw new Error('err');
        const json = await res.json();
        renderNotifs(json.data || []);
    } catch (e) { console.error('Notif fetch error', e); }
}

async function markAllRead(){
    try {
        await fetch(apiUrl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=mark_all' });
        fetchNotifs();
    } catch(e){ console.error(e); }
}

document.getElementById('notifBtn').addEventListener('click', ()=> {
    const dd = document.getElementById('notifDropdown');
    dd.classList.toggle('hidden');
    if (!dd.classList.contains('hidden')) markAllRead();
});
document.getElementById('markAllBtn').addEventListener('click', (e)=>{ e.stopPropagation(); markAllRead(); });

fetchNotifs();
setInterval(fetchNotifs, 5000);

feather.replace();
</script>
