<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<main class="flex-1 p-8 overflow-y-auto">
    <header class="flex justify-between items-end mb-8 border-b pb-6">
        <div>
            <h2 class="text-4xl font-black text-slate-900 tracking-tight">ภาพรวมระบบ</h2>
            <p class="text-slate-500 font-medium">ยินดีต้อนรับสู่ระบบจัดการตารางสอนอัจฉริยะ</p>
        </div>
        <div class="flex gap-4">
            <button onclick="updateDb()" class="bg-white border-2 border-slate-100 text-slate-700 px-5 py-2.5 rounded-2xl font-bold flex items-center gap-2 hover:bg-slate-50 hover:border-slate-200 transition-all shadow-sm">
                <i data-lucide="database" size="18" class="text-blue-500"></i> ซิงค์ MySQL
            </button>
            <a href="timetable.php" class="bg-blue-600 text-white px-6 py-2.5 rounded-2xl font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30">
                <i data-lucide="calendar-plus" size="18"></i> จัดตารางส่วนประกอบใหม่
            </a>
        </div>
    </header>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div class="bg-white p-7 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-6 group hover:shadow-xl hover:shadow-blue-500/5 transition-all">
            <div class="h-16 w-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform"><i data-lucide="book-open" size="28"></i></div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">รายวิชาทั้งหมด</p>
                <p id="stat-subjects" class="text-3xl font-black text-slate-900">0</p>
            </div>
        </div>
        <div class="bg-white p-7 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-6 group hover:shadow-xl hover:shadow-indigo-500/5 transition-all">
            <div class="h-16 w-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform"><i data-lucide="users" size="28"></i></div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">บุคลากรครู</p>
                <p id="stat-teachers" class="text-3xl font-black text-slate-900">0</p>
            </div>
        </div>
        <div class="bg-white p-7 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-6 group hover:shadow-xl hover:shadow-cyan-500/5 transition-all">
            <div class="h-16 w-16 bg-cyan-50 text-cyan-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform"><i data-lucide="door-open" size="28"></i></div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">ห้องเรียนรวม</p>
                <p id="stat-rooms" class="text-3xl font-black text-slate-900">0</p>
            </div>
        </div>
        <div class="bg-white p-7 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-6 group hover:shadow-xl hover:shadow-emerald-500/5 transition-all">
            <div class="h-16 w-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform"><i data-lucide="graduation-cap" size="28"></i></div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">ชั้นเรียนทั้งหมด</p>
                <p id="stat-classrooms" class="text-3xl font-black text-slate-900">0</p>
            </div>
        </div>
    </div>

    <!-- Super Admin Section -->
    <?php if (hasRole('super_admin')): ?>
    <section class="mb-12">
        <div class="flex items-center gap-3 mb-6">
            <div class="h-8 w-8 bg-amber-500 text-white rounded-lg flex items-center justify-center shadow-lg shadow-amber-500/20"><i data-lucide="shield-check" size="18"></i></div>
            <h3 class="text-2xl font-bold text-slate-900">การอนุมัติการใช้งาน (Super Admin)</h3>
            <div id="pendingBadge" class="hidden bg-red-500 text-white text-[10px] font-black px-2 py-1 rounded-full animate-pulse uppercase tracking-tight">รอดำเนินการ</div>
        </div>

        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b">
                            <th class="p-5 font-black text-slate-400 uppercase text-[10px] tracking-widest">ข้อมูลสถานศึกษา</th>
                            <th class="p-5 font-black text-slate-400 uppercase text-[10px] tracking-widest">รหัสสถานศึกษา</th>
                            <th class="p-5 font-black text-slate-400 uppercase text-[10px] tracking-widest">วันที่สมัคร</th>
                            <th class="p-5 font-black text-slate-400 uppercase text-[10px] tracking-widest">สถานะ</th>
                            <th class="p-5 font-black text-slate-400 uppercase text-[10px] tracking-widest text-right">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody id="schoolApprovalList" class="divide-y divide-slate-50 italic text-slate-400 transition-all">
                        <tr><td colspan="5" class="p-10 text-center">กำลังดึงข้อมูลโรงเรียน...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm">
            <h3 class="text-xl font-bold text-slate-900 mb-2">อัปเดตระบบล่าสุด</h3>
            <p class="text-slate-500 text-sm mb-6">ตรวจสอบความเคลื่อนไหวและการซิงค์ฐานข้อมูล</p>
            <div class="space-y-4">
                <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100/50">
                    <div class="h-10 w-10 bg-white shadow-sm rounded-xl flex items-center justify-center text-blue-500"><i data-lucide="database" size="20"></i></div>
                    <div class="flex-1">
                        <p class="font-bold text-slate-900">Database Synchronized</p>
                        <p class="text-xs text-slate-400 font-medium">เชื่อมต่อกับ MySQL schoolos_timetable สมบูรณ์</p>
                    </div>
                    <span class="text-[10px] font-black text-green-500 uppercase">Live</span>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-8 rounded-[3rem] text-white shadow-2xl shadow-blue-500/20 flex flex-col justify-between overflow-hidden relative group">
            <div class="absolute -top-12 -right-12 h-64 w-64 bg-white/10 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-700"></div>
            <div class="relative z-10">
                <h3 class="text-2xl font-black mb-2 leading-tight">เริ่มจัดตารางสอนเทอมนี้ ของโรงเรียนคุณ</h3>
                <p class="text-blue-100 font-medium text-sm leading-relaxed max-w-[280px]">ระบบ AI จะช่วยคำนวณคาบเรียนไม่ให้ชนกัน พร้อมระบบลากวางที่แม่นยำ</p>
            </div>
            <a href="timetable.php" class="relative z-10 w-fit bg-white text-blue-700 px-8 py-3 rounded-2xl font-black hover:bg-blue-50 hover:scale-105 transition-all shadow-xl">จัดการทันที</a>
        </div>
    </div>
</main>

<script>
    lucide.createIcons();

    async function loadStats() {
        const res = await fetch('api/manage.php?action=get_stats');
        const stats = await res.json();
        document.getElementById('stat-subjects').innerText = stats.subjects;
        document.getElementById('stat-teachers').innerText = stats.teachers;
        document.getElementById('stat-rooms').innerText = stats.rooms;
        document.getElementById('stat-classrooms').innerText = stats.classrooms;
    }

    async function loadApprovals() {
        if (!document.getElementById('schoolApprovalList')) return;
        const res = await fetch('api/manage.php?action=admin_schools_list');
        const schools = await res.json();
        const list = document.getElementById('schoolApprovalList');
        
        let pendingCount = 0;
        list.innerHTML = schools.map(s => {
            if(!s.is_approved) pendingCount++;
            return `
            <tr class="hover:bg-slate-50/50 transition-colors not-italic text-slate-700">
                <td class="p-5">
                    <p class="font-bold text-slate-900">${s.name}</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">${s.affiliation || 'ไม่ระบุสังกัด'}</p>
                </td>
                <td class="p-5 font-mono text-sm font-bold text-blue-600">${s.code}</td>
                <td class="p-5 text-sm font-medium text-slate-500">${new Date(s.created_at).toLocaleDateString('th-TH')}</td>
                <td class="p-5">
                    ${s.is_approved ? 
                        '<span class="bg-green-100 text-green-700 text-[10px] font-black px-2 py-1 rounded-full uppercase tracking-tighter shadow-sm border border-green-200">อนุมัติแล้ว</span>' : 
                        '<span class="bg-amber-100 text-amber-700 text-[10px] font-black px-2 py-1 rounded-full uppercase tracking-tighter shadow-sm border border-amber-200">ระงับ/รออนุมัติ</span>'
                    }
                </td>
                <td class="p-5 text-right flex justify-end gap-2">
                    ${!s.is_approved ? 
                        `<button onclick="approveSchool(${s.id})" class="bg-blue-600 text-white text-[11px] font-black px-3 py-2 rounded-xl hover:bg-blue-700 shadow-md transition-all active:scale-95">อนุมัติ</button>` : 
                        `<button onclick="toggleSchoolStatus(${s.id})" class="bg-amber-500 text-white text-[11px] font-black px-3 py-2 rounded-xl hover:bg-amber-600 shadow-md transition-all active:scale-95">ระงับใช้งาน</button>`
                    }
                    <button onclick="deleteSchool(${s.id})" class="bg-red-50 text-red-600 text-[11px] font-black px-3 py-2 rounded-xl hover:bg-red-100 shadow-sm transition-all active:scale-95">ลบ</button>
                </td>
            </tr>
            `;
        }).join('');

        if(pendingCount > 0) {
            document.getElementById('pendingBadge').classList.remove('hidden');
            document.getElementById('pendingBadge').innerText = `${pendingCount} รายการใหม่`;
        } else {
            document.getElementById('pendingBadge').classList.add('hidden');
        }
        lucide.createIcons();
    }

    async function approveSchool(id) {
        if(confirm('ยืนยันสมบูรณ์การอนุมัติโรงเรียนเพื่อให้เข้าใช้งานระบบ?')) {
            await fetch(`api/manage.php?action=admin_school_approve&id=${id}`);
            loadApprovals();
        }
    }

    async function toggleSchoolStatus(id) {
        if(confirm('คุณต้องการระงับการใช้งานของโรงเรียนนี้ชั่วคราวใช่หรือไม่?')) {
            await fetch(`api/manage.php?action=admin_school_toggle_status&id=${id}`);
            loadApprovals();
        }
    }

    async function deleteSchool(id) {
        if(confirm('คำเตือน: การลบโรงเรียนจะทำให้ข้อมูลทั้งหมดของโรงเรียนนี้ถูกลบถาวร! ยืนยันข้อมูลหรือไม่?')) {
            const res = await fetch(`api/manage.php?action=admin_school_delete&id=${id}`);
            const result = await res.json();
            if(result.success) loadApprovals();
            else alert('Error: ' + result.error);
        }
    }

    async function updateDb() {
        try {
            const res = await fetch('api/manage.php?action=system_sync');
            const data = await res.json();
            if(data.success) {
                alert(`ซิงค์ข้อมูลสำเร็จ!\nเวลาเซิร์ฟเวอร์: ${data.timestamp}\nเชื่อมต่อฐานข้อมูล: ${data.db}`);
            } else {
                alert('การซิงค์ล้มเหลว: ' + data.error);
            }
        } catch (e) {
            alert('ไม่สามารถเชื่อมต่อ API ได้');
        }
    }

    loadStats();
    loadApprovals();
</script>
</body>
</html>
