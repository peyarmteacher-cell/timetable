<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<!-- Content Area -->
<div class="flex-1 flex flex-col h-screen overflow-hidden">
    <!-- Action Header -->
    <header class="bg-white border-b h-20 flex items-center justify-between px-8 sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <!-- Main Sidebar Toggle -->
            <button onclick="toggleMainSidebar()" class="p-2 hover:bg-slate-50 rounded-xl border border-slate-100 transition-all text-slate-500 mr-2" title="พับ/แสดงเมนูหลัก">
                <i id="mainSidebarIcon" data-lucide="panel-left-close" size="20"></i>
            </button>
            <h1 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">จัดการตารางสอน</h1>
            <div class="h-8 w-px bg-slate-200"></div>
            
            <div class="flex bg-slate-100 p-1 rounded-xl">
                <button onclick="setViewMode('assign')" id="btn-assign" class="px-4 py-2 rounded-lg text-sm font-bold transition-all bg-white shadow-sm text-blue-600">กำหนดการสอน</button>
                <button onclick="setViewMode('view')" id="btn-view" class="px-4 py-2 rounded-lg text-sm font-bold transition-all text-slate-500 hover:text-slate-700">ดูตารางสอน</button>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <div id="view-filters" class="hidden flex items-center gap-4">
                <select id="viewType" onchange="updateViewFilter()" class="bg-slate-50 border rounded-lg px-3 py-2 text-sm font-bold outline-none">
                    <option value="teacher">รายครู</option>
                    <option value="classroom">รายชั้นเรียน</option>
                    <option value="room">รายห้องเรียน</option>
                </select>
                <select id="filterSelect" onchange="loadViewData()" class="bg-slate-50 border rounded-lg px-3 py-2 text-sm font-bold outline-none min-w-[200px]">
                    <option value="">เลือกการกรอง...</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="autoGenerate()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl flex items-center gap-2 font-bold transition-all shadow-lg hover:shadow-blue-500/20">
                    <i data-lucide="wand-2" size="18"></i> จัดตารางสอนอัตโนมัติ
                </button>
                <button onclick="autoGenerate()" class="bg-rose-50 text-rose-600 px-4 py-2 rounded-xl flex items-center gap-2 font-bold transition-all hover:bg-rose-100 border border-rose-100 text-xs">
                    <i data-lucide="refresh-cw" size="14"></i> ล้างและจัดใหม่
                </button>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-auto p-8 bg-slate-50/50">
        <!-- Assign Mode -->
        <div id="section-assign" class="space-y-6">
            <!-- 1. Teacher Selection (Horizontal Bar) -->
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i data-lucide="users" size="20"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-800 text-lg">เลือกคุณครูเพื่อจัดการข้อมูล</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">จัดการภาระงานและตารางสอนรายบุคคล</p>
                        </div>
                    </div>
                    <div class="relative w-72">
                        <i data-lucide="search" size="16" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" id="teacherSearch" oninput="filterTeachers()" placeholder="ค้นหาชื่อคุณครู..." class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-100 bg-slate-50 focus:bg-white transition-all outline-none font-bold text-sm shadow-inner">
                    </div>
                </div>
                
                <div id="teacherList" class="flex gap-4 overflow-x-auto pb-4 custom-scrollbar-h">
                    <!-- Teachers list rendered horizontally -->
                </div>
            </div>

            <!-- Workspace Area -->
            <div id="assignArea" class="hidden space-y-8">
                <!-- 2. Teaching Load Management (Middle) -->
                <div class="bg-white rounded-[2.5rem] shadow-md border border-slate-100 overflow-hidden">
                    <div class="p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i data-lucide="layers" size="20"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-900 text-lg">รายงานภาระงานครู</h3>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">กำหนดวิชา คาบเรียน และห้องเรียนที่ใช้</p>
                            </div>
                        </div>
                        <button onclick="openAddLoadModal()" class="bg-emerald-600 text-white px-8 py-3 rounded-xl font-black text-xs hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-500/10 flex items-center gap-2 uppercase tracking-widest">
                            <i data-lucide="plus-circle" size="16"></i> เพิ่มภาระงานใหม่
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50/50 border-b border-slate-100">
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-left">
                                    <th class="px-8 py-5">ระดับชั้น/ห้อง</th>
                                    <th class="px-8 py-5">รหัสวิชา - ชื่อวิชา</th>
                                    <th class="px-8 py-5">ห้องเรียนที่ใช้</th>
                                    <th class="px-8 py-5 text-center">คาบ/สัปดาห์</th>
                                    <th class="px-8 py-5 text-right">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="teachingLoadTable" class="divide-y divide-slate-50 text-sm">
                                <!-- Load items -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Individual Teacher Timetable (Bottom) -->
                <div class="bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 overflow-hidden">
                    <div class="bg-indigo-950 p-8 flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-xl shadow-blue-500/20">
                                <i data-lucide="calendar" size="28"></i>
                            </div>
                            <div>
                                <h3 id="selectedTeacherName" class="font-black text-2xl text-white leading-none">รายชื่อครู</h3>
                                <p class="text-indigo-300 text-xs font-bold mt-2 uppercase tracking-widest flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    แสดงผลตารางสอนปัจจุบัน (Full View)
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex bg-white/10 p-1 rounded-xl border border-white/10 mr-2">
                                <button onclick="adjustZoom(-0.1)" class="w-10 h-10 flex items-center justify-center text-white hover:bg-white/10 rounded-lg transition-all" title="ซูมออก">
                                    <i data-lucide="minus" size="18"></i>
                                </button>
                                <div class="w-12 flex items-center justify-center text-white font-black text-xs" id="zoomDisplay">100%</div>
                                <button onclick="adjustZoom(0.1)" class="w-10 h-10 flex items-center justify-center text-white hover:bg-white/10 rounded-lg transition-all" title="ซูมเข้า">
                                    <i data-lucide="plus" size="18"></i>
                                </button>
                            </div>
                            <button onclick="autoGenerate()" class="group bg-orange-500 hover:bg-orange-600 text-white px-8 py-4 rounded-2xl flex items-center gap-3 font-black transition-all shadow-xl shadow-orange-500/20 active:scale-95 text-sm uppercase tracking-widest">
                                <i data-lucide="shuffle" size="20" class="group-hover:rotate-180 transition-transform duration-700"></i>
                                สุ่มจัดตารางใหม่
                            </button>
                        </div>
                    </div>
                    <div class="p-8 bg-slate-50 min-h-[600px]">
                        <div id="miniTimetable" class="grid gap-px border border-indigo-900 bg-indigo-950 rounded-[2rem] overflow-hidden shadow-2xl">
                            <!-- Timetable Data -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Selection State -->
            <div id="noTeacherSelected" class="bg-white rounded-[2.5rem] shadow-sm border border-dashed border-slate-200 p-24 text-center flex flex-col items-center gap-6">
                <div class="w-24 h-24 bg-blue-50 text-blue-300 rounded-[2rem] flex items-center justify-center animate-bounce">
                    <i data-lucide="user-check" size="48"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-400">ยังไม่ได้เลือกคุณครู</h3>
                    <p class="text-slate-300 font-bold text-sm mt-2">กรุณาเลือกคุณครูจากแถบด้านบนเพื่อเริ่มจัดการตารางสอน</p>
                </div>
            </div>
        </div>

        <!-- View Mode -->
        <div id="section-view" class="hidden space-y-6">
            <div id="fullTimetable" class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <!-- Large Timetable -->
            </div>
        </div>
    </div>
</div>

<!-- Add Teaching Load Modal -->
<div id="addLoadModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden transform transition-all flex flex-col">
        <div class="p-8 border-b bg-slate-50/50 flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-black text-slate-900">มอบหมายงานสอน</h3>
                <p class="text-sm font-medium text-slate-500">เลือกชั้นเรียนและรายวิชาที่คุณครูรับผิดชอบ</p>
            </div>
            <button onclick="closeAddLoadModal()" class="w-10 h-10 rounded-full hover:bg-slate-200 transition-all flex items-center justify-center text-slate-400"><i data-lucide="x"></i></button>
        </div>
        <form id="teachingLoadForm" class="p-8 space-y-6">
            <input type="hidden" name="teacher_id" id="modalTeacherId">
            
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5 col-span-2">
                    <label class="text-sm font-bold text-slate-700">1. เลือกระดับชั้น</label>
                    <select name="classroom_id" id="classroomSelect" onchange="onClassroomChange()" class="w-full bg-slate-50 border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-600 font-bold transition-all" required>
                        <option value="">เลือกชั้นเรียน (เช่น ป.1/1)</option>
                    </select>
                </div>

                <div class="space-y-1.5 col-span-2">
                    <label class="text-sm font-bold text-slate-700">2. เลือกรายวิชา</label>
                    <select name="subject_id" id="subjectSelect" class="w-full bg-slate-50 border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-600 font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed" required disabled>
                        <option value="">กรุณาเลือกชั้นเรียนก่อน</option>
                    </select>
                </div>

                <div class="space-y-1.5 pt-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">ชั่วโมงสอนต่อสัปดาห์</label>
                    <input type="number" name="hours_per_week" id="hoursInput" min="1" max="20" value="2" class="w-full bg-slate-100 border-none rounded-xl px-4 py-3 font-black text-blue-600 focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="space-y-1.5 pt-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">รูปแบบคาบสอน</label>
                    <select name="period_type" class="w-full bg-slate-100 border-none rounded-xl px-4 py-3 font-bold text-slate-700">
                        <option value="single">คาบเดียว</option>
                        <option value="double">คาบคู่ (2 คาบติด)</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-sm font-bold text-slate-700">3. กำหนดห้องเรียนที่ใช้</label>
                <select name="room_id" id="roomSelect" class="w-full bg-slate-50 border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-600 font-bold transition-all" required>
                    <option value="">เลือกห้องเรียนสม่ำเสมอ</option>
                </select>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-black text-lg hover:bg-blue-700 transition-all shadow-xl hover:shadow-blue-500/20 flex items-center justify-center gap-3">
                <i data-lucide="save"></i> เพิ่มข้อมูลการสอน
            </button>
        </form>
    </div>
</div>

<!-- Slot Selector Modal -->
<div id="slotSelectorModal" class="fixed inset-0 bg-indigo-950/80 backdrop-blur-md z-[110] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-[3rem] w-full max-w-7xl h-[90vh] flex flex-col overflow-hidden shadow-2xl border border-white/20">
        <!-- Modal Header -->
        <div class="bg-indigo-950 p-8 flex justify-between items-center shrink-0">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 rounded-3xl bg-blue-600 flex items-center justify-center text-white shadow-xl shadow-blue-500/30">
                    <i data-lucide="calendar-check" size="32"></i>
                </div>
                <div>
                    <h2 id="slotModalTitle" class="text-3xl font-black text-white leading-none mb-2">กำหนดเงื่อนไขตาราง</h2>
                    <p id="slotModalDesc" class="text-indigo-300 font-bold text-sm tracking-wide">จัดการพื้นที่และคาบเวลาที่ต้องการล็อกข้อมูล</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="bg-white/10 px-6 py-3 rounded-2xl border border-white/10 flex items-center gap-4">
                    <span class="text-white/40 font-bold text-[10px] uppercase tracking-widest">เลือกแล้ว</span>
                    <span id="selectedCount" class="text-white text-2xl font-black">0</span>
                    <span class="text-white/40 font-bold text-[10px] uppercase tracking-widest">คาบ</span>
                </div>
                <button onclick="closeSlotModal()" class="w-12 h-12 rounded-full bg-white/10 text-white hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center border border-white/10">
                    <i data-lucide="x"></i>
                </button>
            </div>
        </div>

        <!-- Modal Content -->
        <div class="flex-1 overflow-auto bg-slate-50">
            <div id="slotGrid" class="grid p-8 gap-px bg-slate-200">
                <!-- Dynamic Grid -->
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-8 border-t bg-white flex justify-between items-center shrink-0">
            <div class="flex items-center gap-3 text-slate-400">
                <i data-lucide="info" size="18"></i>
                <p class="text-sm font-medium">การล็อกคาบ (FIX) จะทำให้ระบบจัดวิชานี้ลงในคาบที่คุณเลือกเสมอ</p>
            </div>
            <div class="flex gap-4">
                <button onclick="closeSlotModal()" class="px-8 py-4 rounded-2xl font-black text-slate-500 hover:bg-slate-100 transition-all uppercase tracking-widest text-sm">ยกเลิก</button>
                <button onclick="saveSelectedSlots()" class="px-10 py-4 bg-blue-600 text-white rounded-2xl font-black shadow-xl shadow-blue-500/20 hover:bg-blue-700 transition-all flex items-center gap-3 uppercase tracking-widest">
                    <i data-lucide="save"></i> บันทึกเงื่อนไข
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();
    
    let state = {
        teachers: [],
        classrooms: [],
        rooms: [],
        subjects: [],
        currentTeacherId: null,
        viewMode: 'assign',
        viewType: 'teacher',
        timetableZoom: 1.0
    };

    function adjustZoom(delta) {
        state.timetableZoom = Math.max(0.5, Math.min(2.0, state.timetableZoom + delta));
        document.getElementById('zoomDisplay').innerText = Math.round(state.timetableZoom * 100) + '%';
        if (state.currentTeacherId) renderMiniTimetable(state.currentTeacherId);
    }

    async function init() {
        await loadPeriods();
        const [tRes, cRes, rRes, sRes] = await Promise.all([
            fetch('api/manage.php?action=teachers_list'),
            fetch('api/manage.php?action=classrooms_list'),
            fetch('api/manage.php?action=rooms_list'),
            fetch('api/manage.php?action=subjects_list')
        ]);

        state.teachers = await tRes.json();
        state.classrooms = await cRes.json();
        state.rooms = await rRes.json();
        state.subjects = await sRes.json();

        renderTeacherList();
        populateClassrooms();
        populateRooms();
    }

    function renderTeacherList() {
        const list = document.getElementById('teacherList');
        list.innerHTML = state.teachers.map(t => {
            const isActive = state.currentTeacherId == t.id;
            return `
                <button onclick="selectTeacher(${t.id})" 
                        id="teacher-btn-${t.id}"
                        class="teacher-btn shrink-0 flex items-center gap-4 p-4 pr-6 rounded-2xl transition-all border text-left group min-w-[220px]
                        ${isActive ? 'bg-blue-600 text-white border-blue-600 shadow-xl scale-[1.03] ring-4 ring-blue-600/10' : 'bg-white border-slate-100 hover:bg-blue-50 text-slate-700 hover:border-blue-100'}" 
                        data-id="${t.id}">
                    <div class="icon-box w-12 h-12 rounded-xl flex items-center justify-center transition-all ${isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-blue-100 group-hover:text-blue-600'}">
                        <i data-lucide="user" size="24"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-black text-sm ${isActive ? 'text-white' : 'text-slate-800'} teacher-name tracking-tight">${t.name}</h4>
                        <p class="text-[10px] font-black ${isActive ? 'text-white/70' : 'text-slate-400'} uppercase tracking-widest mt-0.5">${t.position || 'คุณครู'}</p>
                    </div>
                </button>
            `;
        }).join('');
        lucide.createIcons();
    }

    function filterTeachers() {
        const query = document.getElementById('teacherSearch').value.toLowerCase();
        const btns = document.querySelectorAll('.teacher-btn');
        btns.forEach(btn => {
            const name = btn.querySelector('.teacher-name').innerText.toLowerCase();
            btn.classList.toggle('hidden', !name.includes(query));
        });
    }

    async function selectTeacher(id) {
        state.currentTeacherId = id;
        
        // Reset all buttons
        document.querySelectorAll('.teacher-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-lg', 'scale-[1.02]');
            btn.classList.add('bg-white', 'border-slate-100', 'text-slate-700');
            
            const icon = btn.querySelector('.icon-box');
            if (icon) {
                icon.classList.remove('bg-white/20', 'text-white');
                icon.classList.add('bg-slate-100', 'text-slate-400');
            }
            const name = btn.querySelector('.teacher-name');
            if (name) name.classList.replace('text-white', 'text-slate-700');
        });

        // Set active button
        const activeBtn = document.getElementById(`teacher-btn-${id}`);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'border-slate-100', 'text-slate-700');
            activeBtn.classList.add('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-lg', 'scale-[1.02]');
            
            const icon = activeBtn.querySelector('.icon-box');
            if (icon) {
                icon.classList.remove('bg-slate-100', 'text-slate-400');
                icon.classList.add('bg-white/20', 'text-white');
            }
            const name = activeBtn.querySelector('.teacher-name');
            if (name) name.classList.replace('text-slate-700', 'text-white');
        }
        
        document.getElementById('noTeacherSelected').classList.add('hidden');
        document.getElementById('assignArea').classList.remove('hidden');
        
        const teacher = state.teachers.find(t => t.id == id);
        document.getElementById('selectedTeacherName').innerText = teacher.name;
        
        loadTeachingLoad();
        renderMiniTimetable(id);
    }

    async function loadTeachingLoad() {
        const res = await fetch('api/manage.php?action=teaching_load_list&teacher_id=' + state.currentTeacherId);
        const data = await res.json();
        state.teachingLoad = data; // Store in state for later use
        const table = document.getElementById('teachingLoadTable');
        
        if (data.length === 0) {
            table.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-slate-400 font-bold italic">ไม่พบวิชาที่สอน</td></tr>`;
        } else {
            table.innerHTML = data.map(item => `
                <tr class="hover:bg-slate-50 transition-colors group">
                    <td class="px-6 py-4 font-bold text-slate-700">${item.classroom_level}/${item.classroom_name}</td>
                    <td class="px-6 py-4 font-medium">
                        <span class="text-blue-600 font-bold">[${item.subject_code}]</span> ${item.subject_name}
                        <div class="text-[10px] text-slate-400 mt-1 flex gap-2">
                            <span class="bg-slate-100 px-2 py-0.5 rounded uppercase font-bold text-slate-500">${item.period_type === 'double' ? 'คาบคู่' : 'คาบเดียว'}</span>
                            ${item.fixed_slots && JSON.parse(item.fixed_slots).length > 0 ? '<span class="bg-slate-900 text-white px-2 py-0.5 rounded uppercase font-bold shadow-sm">Fix แล้ว</span>' : ''}
                            ${item.allowed_slots && JSON.parse(item.allowed_slots).length > 0 ? '<span class="bg-blue-600 text-white px-2 py-0.5 rounded uppercase font-bold shadow-sm">สุ่มจำกัดพื้นที่</span>' : ''}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-500">${item.room_name || '-'}</td>
                    <td class="px-6 py-4 text-center font-black text-blue-600">${item.hours_per_week || 2}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button onclick="openSlotSelector(${item.id}, 'fixed')" class="px-3 py-1.5 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-all shadow-sm flex items-center gap-1 text-[10px] font-bold">
                                <i data-lucide="lock" size="12"></i> FIX
                            </button>
                            <button onclick="openSlotSelector(${item.id}, 'allowed')" class="px-3 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-400 transition-all shadow-sm flex items-center gap-1 text-[10px] font-bold">
                                <i data-lucide="shuffle" size="12"></i> สุ่ม
                            </button>
                            <button onclick="deleteLoad(${item.id})" class="p-2 text-rose-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                                <i data-lucide="trash-2" size="16"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
        lucide.createIcons();
        renderMiniTimetable(state.currentTeacherId);
    }

    function populateClassrooms() {
        const select = document.getElementById('classroomSelect');
        select.innerHTML = '<option value="">เลือกชั้นเรียน...</option>' + 
            state.classrooms.map(c => `<option value="${c.id}" data-level="${c.level}">${c.level} ห้อง ${c.name}</option>`).join('');
    }

    function populateRooms() {
        const select = document.getElementById('roomSelect');
        select.innerHTML = '<option value="">เลือกห้องเรียนที่ใช้สอน...</option>' + 
            state.rooms.map(r => `<option value="${r.id}">${r.name}</option>`).join('');
    }

    async function onClassroomChange() {
        const select = document.getElementById('classroomSelect');
        const subjectSelect = document.getElementById('subjectSelect');
        const selectedOpt = select.options[select.selectedIndex];
        
        if (!selectedOpt.value) {
            subjectSelect.disabled = true;
            return;
        }

        const level = selectedOpt.dataset.level;
        const res = await fetch(`api/manage.php?action=subjects_by_level&level=${level}`);
        const subjects = await res.json();
        
        subjectSelect.disabled = false;
        if (subjects.length === 0) {
            subjectSelect.innerHTML = '<option value="">ไม่พบวิชาในระดับชั้นนี้</option>';
        } else {
            subjectSelect.innerHTML = '<option value="">เลือกวิชาที่จะสอน...</option>' + 
                subjects.map(s => `<option value="${s.id}">${s.code} ${s.name}</option>`).join('');
        }
    }

    function showAddLoadForm() {
        document.getElementById('modalTeacherId').value = state.currentTeacherId;
        document.getElementById('addLoadModal').classList.remove('hidden');
    }

    function closeAddLoadModal() {
        document.getElementById('addLoadModal').classList.add('hidden');
    }

    document.getElementById('teachingLoadForm').onsubmit = async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const data = {
            teacher_id: fd.get('teacher_id'),
            classroom_id: fd.get('classroom_id'),
            subject_id: fd.get('subject_id'),
            room_id: fd.get('room_id') || null,
            hours_per_week: fd.get('hours_per_week') || 2,
            period_type: fd.get('period_type') || 'single'
        };

        if (!data.teacher_id || !data.classroom_id || !data.subject_id) {
            Swal.fire('ข้อมูลไม่ครบ', 'กรุณาเลือกชั้นเรียนและวิชาให้ครบถ้วน', 'warning');
            return;
        }

        try {
            Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            
            const res = await fetch('api/manage.php?action=teaching_load_add', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
            
            const result = await res.json();
            if (result.success) {
                await Swal.fire({
                    title: 'บันทึกเรียบร้อย!',
                    text: 'ข้อมูลการสอนของคุณครูได้รับการอัปเดตแล้ว',
                    icon: 'success',
                    confirmButtonColor: '#2563eb'
                });
                closeAddLoadModal();
                loadTeachingLoad();
            } else {
                Swal.fire('ผิดพลาด', result.error || 'ไม่สามารถบันทึกข้อมูลได้', 'error');
            }
        } catch (error) {
            Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
        }
    };

    // SLOT SELECTOR LOGIC
    let currentLoadId = null;
    let slotSelectorType = 'fixed';
    let selectedSlots = [];
    let teacherFullTimetable = [];

    async function openSlotSelector(loadId, type) {
        currentLoadId = loadId;
        slotSelectorType = type;
        
        const load = state.teachingLoad.find(l => l.id == loadId);
        selectedSlots = JSON.parse((type === 'fixed' ? load.fixed_slots : load.allowed_slots) || '[]');
        
        // Fetch current teacher's full timetable for context
        const res = await fetch(`api/manage.php?action=get_teacher_timetable&teacher_id=${state.currentTeacherId}`);
        teacherFullTimetable = await res.json();

        const modal = document.getElementById('slotSelectorModal');
        const title = document.getElementById('slotModalTitle');
        const desc = document.getElementById('slotModalDesc');
        
        title.innerText = type === 'fixed' ? 'ระบุคาบสอนคงที่ (Fixed Slots)' : 'กำหนดพื้นที่สุ่ม (Allowed Area)';
        desc.innerText = type === 'fixed' 
            ? 'เลือกคาบที่ต้องการให้ระบบล็อกไว้ถาวร (สีส้ม)' 
            : 'ระบบจะสุ่มลงวิชานี้เฉพาะในคาบที่คุณระบุไว้เท่านั้น';
            
        renderSlotGrid();
        modal.classList.remove('hidden');
    }

    function renderSlotGrid() {
        const grid = document.getElementById('slotGrid');
        const days = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
        const currentLoad = state.teachingLoad.find(l => l.id == currentLoadId);
        
        let html = `
            <div class="bg-indigo-950 p-4 font-black text-[10px] text-indigo-300 text-center flex items-center justify-center border-b border-indigo-900 border-r uppercase tracking-tighter">วัน/เวลา</div>
        `;
        
        timetablePeriods.forEach(p => {
            html += `
                <div class="bg-indigo-950 p-3 text-center border-b border-indigo-900 border-r last:border-r-0">
                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-tighter">คาบ ${p.period_number}</p>
                    <p class="text-[10px] font-black text-white">${p.start_time.substring(0, 5)}-${p.end_time.substring(0, 5)}</p>
                </div>
            `;
        });

        days.forEach((dayName, dayIdx) => {
            const dayNum = dayIdx + 1;
            html += `<div class="bg-blue-600 p-4 text-center text-[13px] font-black text-white flex items-center justify-center border-b border-white/10 border-r uppercase">${dayName}</div>`;
            
            timetablePeriods.forEach(p => {
                const isSelected = selectedSlots.some(s => s.day == dayNum && s.period == p.period_number);
                const isBreak = p.type !== 'normal';
                
                // Find if there's already something scheduled here for THIS teacher
                const existing = teacherFullTimetable.find(t => t.day == dayNum && t.period == p.period_number);
                
                let cellClass = "border-b border-r last:border-r-0 border-slate-100 min-h-[90px] relative transition-all group cursor-pointer";
                let innerContent = "";

                if (isBreak) {
                    cellClass += " bg-slate-50/50 cursor-not-allowed";
                    innerContent = `<div class="absolute inset-0 flex items-center justify-center opacity-20"><i data-lucide="coffee" size="14"></i></div>`;
                } else if (isSelected) {
                    cellClass += " bg-orange-100 ring-2 ring-orange-500 ring-inset z-10 scale-[0.98]";
                    innerContent = `
                        <div class="p-2 h-full flex flex-col items-center justify-center text-center">
                            <p class="text-[11px] font-black text-orange-700 leading-tight">${currentLoad.subject_code}</p>
                            <p class="text-[9px] font-bold text-orange-600/80 leading-tight">${currentLoad.classroom_level}/${currentLoad.classroom_name}</p>
                            <p class="text-[9px] font-bold text-orange-500/60 leading-none mt-1">
                                <i data-lucide="map-pin" size="8" class="inline"></i> ${currentLoad.room_name || '-'}
                            </p>
                            <div class="absolute top-1 right-1"><i data-lucide="check-circle-2" size="10" class="text-orange-500"></i></div>
                        </div>
                    `;
                } else if (existing) {
                    cellClass += " bg-blue-50/30 opacity-40 grayscale";
                    innerContent = `
                        <div class="p-2 h-full flex flex-col items-center justify-center text-center">
                            <p class="text-[10px] font-black text-blue-900 leading-tight">${existing.subject_code}</p>
                            <p class="text-[8px] font-bold text-blue-700/60">${existing.classroom_level}/${existing.classroom_name}</p>
                        </div>
                    `;
                } else {
                    cellClass += " bg-white hover:bg-blue-50/50";
                    innerContent = `<div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"><i data-lucide="plus" size="14" class="text-blue-300"></i></div>`;
                }

                html += `
                    <div onclick="${isBreak ? '' : `toggleSlot(${dayNum}, ${p.period_number})`}" class="${cellClass}">
                        ${innerContent}
                    </div>
                `;
            });
        });
        
        grid.innerHTML = html;
        grid.style.gridTemplateColumns = `120px repeat(${timetablePeriods.length}, 1fr)`;
        document.getElementById('selectedCount').innerText = selectedSlots.length;
        lucide.createIcons();
    }

    function closeSlotModal() { 
        document.getElementById('slotSelectorModal').classList.add('hidden'); 
    }

    function toggleSlot(day, period) {
        const idx = selectedSlots.findIndex(s => s.day == day && s.period == period);
        if (idx > -1) selectedSlots.splice(idx, 1);
        else selectedSlots.push({ day, period });
        renderSlotGrid();
    }

    async function saveSelectedSlots() {
        const fd = new FormData();
        fd.append('load_id', currentLoadId);
        fd.append('type', slotSelectorType);
        fd.append('slots', JSON.stringify(selectedSlots));

        try {
            const res = await fetch('api/manage.php?action=save_teaching_load_slots', { method: 'POST', body: fd });
            const result = await res.json();
            if (result.success) {
                await Swal.fire('สำเร็จ', 'บันทึกเงื่อนไขตารางสอนแล้ว', 'success');
                closeSlotModal();
                loadTeachingLoad();
            }
        } catch (e) {
            Swal.fire('ผิดพลาด', 'ติดต่อเซิร์ฟเวอร์ไม่ได้', 'error');
        }
    }

    async function deleteLoad(id) {
        if (confirm('ลบภาระงานสอนนี้?')) {
            await fetch('api/manage.php?action=teaching_load_delete&id=' + id);
            loadTeachingLoad();
        }
    }

    // VIEW MANAGEMENT
    function setViewMode(mode) {
        state.viewMode = mode;
        document.getElementById('section-assign').classList.toggle('hidden', mode !== 'assign');
        document.getElementById('section-view').classList.toggle('hidden', mode !== 'view');
        document.getElementById('view-filters').classList.toggle('hidden', mode !== 'view');
        
        document.getElementById('btn-assign').className = mode === 'assign' 
            ? 'px-4 py-2 rounded-lg text-sm font-bold transition-all bg-white shadow-sm text-blue-600' 
            : 'px-4 py-2 rounded-lg text-sm font-bold transition-all text-slate-500 hover:text-slate-700';
        
        document.getElementById('btn-view').className = mode === 'view' 
            ? 'px-4 py-2 rounded-lg text-sm font-bold transition-all bg-white shadow-sm text-blue-600' 
            : 'px-4 py-2 rounded-lg text-sm font-bold transition-all text-slate-500 hover:text-slate-700';

        if (mode === 'view') {
            updateViewFilter();
        }
    }

    function updateViewFilter() {
        const type = document.getElementById('viewType').value;
        const select = document.getElementById('filterSelect');
        state.viewType = type;
        
        select.innerHTML = '<option value="">เลือกหน่วยกรอง...</option>';
        if (type === 'teacher') {
            state.teachers.forEach(t => select.innerHTML += `<option value="${t.id}">${t.name}</option>`);
        } else if (type === 'classroom') {
            state.classrooms.forEach(c => select.innerHTML += `<option value="${c.id}">${c.level} ห้อง ${c.name}</option>`);
        } else if (type === 'room') {
            state.rooms.forEach(r => select.innerHTML += `<option value="${r.id}">${r.name}</option>`);
        }
    }

    async function loadViewData() {
        const id = document.getElementById('filterSelect').value;
        if (!id) return;

        let action = '';
        let param = '';
        if (state.viewType === 'teacher') { action = 'get_teacher_timetable'; param = 'teacher_id'; }
        else if (state.viewType === 'classroom') { action = 'get_classroom_timetable'; param = 'classroom_id'; }
        else if (state.viewType === 'room') { action = 'get_room_timetable'; param = 'room_id'; }

        const res = await fetch(`api/manage.php?action=${action}&${param}=${id}`);
        const data = await res.json();
        renderFullTimetable(data);
    }

    // UI UTILS
    function toggleMainSidebar() {
        const sidebar = document.querySelector('aside.w-64') || document.querySelector('nav + aside') || document.querySelector('aside');
        const content = document.querySelector('.flex-1.flex.flex-col');
        const icon = document.getElementById('mainSidebarIcon');
        
        if (sidebar) {
            const isHidden = sidebar.classList.toggle('hidden');
            if (isHidden) {
                content.classList.remove('h-screen');
                content.classList.add('min-h-screen', 'w-full');
                if (icon) icon.setAttribute('data-lucide', 'panel-left-open');
            } else {
                content.classList.add('h-screen');
                if (icon) icon.setAttribute('data-lucide', 'panel-left-close');
            }
            lucide.createIcons();
        }
    }

    function toggleTeacherList() {
        // We will keep teachers visible but let user collapse the MAIN sidebar for more space
        toggleMainSidebar();
    }

    function getColorForSubject(code) {
        if (!code) return 'bg-slate-100 border-slate-200 text-slate-600';
        const colors = [
            'bg-blue-600 text-white border-blue-700 shadow-[inset_0_2px_4px_rgba(255,255,255,0.2)]',
            'bg-emerald-600 text-white border-emerald-700 shadow-[inset_0_2px_4px_rgba(255,255,255,0.2)]',
            'bg-orange-600 text-white border-orange-700 shadow-[inset_0_2px_4px_rgba(255,255,255,0.2)]',
            'bg-rose-600 text-white border-rose-700 shadow-[inset_0_2px_4px_rgba(255,255,255,0.2)]',
            'bg-indigo-600 text-white border-indigo-700 shadow-[inset_0_2px_4px_rgba(255,255,255,0.2)]',
            'bg-violet-600 text-white border-violet-700 shadow-[inset_0_2px_4px_rgba(255,255,255,0.2)]',
            'bg-sky-600 text-white border-sky-700 shadow-[inset_0_2px_4px_rgba(255,255,255,0.2)]',
            'bg-fuchsia-600 text-white border-fuchsia-700 shadow-[inset_0_2px_4px_rgba(255,255,255,0.2)]'
        ];
        let hash = 0;
        for (let i = 0; i < code.length; i++) hash = code.charCodeAt(i) + ((hash << 5) - hash);
        return colors[Math.abs(hash) % colors.length];
    }

    async function autoGenerate() {
        const confirm = await Swal.fire({
            title: 'ยืนยันจัดตารางอัตโนมัติ?',
            text: 'ระบบจะนำข้อมูลที่คุณครูกำหนดไว้ลงตารางสอนให้โดยอัตโนมัติ (ข้อมูลเดิมจะถูกแทนที่)',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ยืนยันเริ่มจัดตาราง',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#2563eb'
        });

        if (!confirm.isConfirmed) return;

        try {
            Swal.fire({
                title: 'กำลังจัดตารางสอน...',
                html: 'ระบบกำลังวิเคราะห์ภาระงานและหาพื้นที่ว่างที่เหมาะสมที่สุด',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const res = await fetch('api/manage.php?action=auto_generate_timetable');
            const result = await res.json();

            if (result.success) {
                await Swal.fire('สำเร็จ!', `จัดตารางสอนเรียบร้อยแล้ว จำนวน ${result.count} คาบ`, 'success');
                if (state.currentTeacherId) selectTeacher(state.currentTeacherId);
                if (state.viewMode === 'view') loadViewData();
            } else {
                Swal.fire('เกิดข้อผิดพลาด', result.error, 'error');
            }
        } catch (e) {
            Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
        }
    }

    let timetablePeriods = [];

    async function loadPeriods() {
        const res = await fetch('api/manage.php?action=periods_list');
        const data = await res.json();
        timetablePeriods = data.length > 0 ? data : [
            {period_number: 1, start_time: '08:30'}, {period_number: 2, start_time: '09:20'},
            {period_number: 3, start_time: '10:10'}, {period_number: 4, start_time: '11:00'},
            {period_number: 5, start_time: '12:00'}, {period_number: 6, start_time: '13:00'},
            {period_number: 7, start_time: '13:50'}, {period_number: 8, start_time: '14:40'}
        ];
    }

    function renderFullTimetable(data) {
        const container = document.getElementById('fullTimetable');
        const days = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
        
        let html = `
            <div class="overflow-x-auto rounded-[2.5rem] border border-indigo-900 shadow-2xl bg-indigo-950 px-px pb-px">
                <table class="w-full border-collapse min-w-[1200px]">
                    <thead>
                        <tr class="bg-indigo-950 border-b border-indigo-900">
                            <th class="p-8 border-r border-indigo-900 w-40 font-black text-indigo-300 text-[11px] uppercase text-center sticky left-0 z-20 bg-indigo-950 backdrop-blur-sm">วัน / คาบ</th>
                            ${timetablePeriods.map(p => `
                                <th class="p-6 border-r border-indigo-900 text-center">
                                    <p class="text-[10px] font-black text-indigo-400 uppercase mb-1 tracking-widest">คาบ ${p.period_number}</p>
                                    <p class="text-sm font-black text-white">${p.start_time.substring(0, 5)} - ${p.end_time.substring(0, 5)}</p>
                                    ${p.type !== 'normal' ? `<span class="inline-block mt-2 px-3 py-1 rounded-full bg-white/10 text-[9px] font-black text-white/50 uppercase tracking-widest border border-white/5">${p.type === 'break' ? 'พักกลางวัน' : 'กิจกรรม'}</span>` : ''}
                                </th>
                            `).join('')}
                        </tr>
                    </thead>
                    <tbody class="bg-white">
        `;

        days.forEach((dayName, dayIdx) => {
            const currentDayNumber = dayIdx + 1;
            html += `<tr class="border-b last:border-b-0 group">
                <td class="bg-blue-600 border-r border-white/10 p-8 font-black text-white text-center text-lg sticky left-0 z-20 shadow-[8px_0_20px_-8px_rgba(0,0,0,0.3)] transition-colors group-hover:bg-blue-700">${dayName}</td>`;
            
            timetablePeriods.forEach(p => {
                const entry = data.find(d => d.day == currentDayNumber && d.period == p.period_number);
                let content = '';
                
                if (p.type === 'break' || p.type === 'activity') {
                    content = `
                        <div class="h-full w-full flex flex-col items-center justify-center bg-slate-950/5 flex flex-col items-center justify-center gap-2 opacity-30 grayscale group-hover:opacity-100 transition-all">
                            <i data-lucide="${p.type === 'break' ? 'coffee' : 'star'}" size="24" class="text-slate-400 mb-1"></i>
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">${p.type === 'break' ? 'พักเที่ยง' : 'กิจกรรม'}</span>
                        </div>
                    `;
                } else if (entry) {
                    const colorClasses = getColorForSubject(entry.subject_code);
                    const teacherFirstName = entry.teacher_name ? 'ครู' + entry.teacher_name.split(' ')[0] : '-';
                    content = `
                        <div class="${colorClasses} rounded-2xl p-4 h-full shadow-lg border transition-all hover:scale-[1.05] hover:z-30 cursor-default flex flex-col justify-center items-center text-center">
                            <p class="text-[13px] font-black mb-1 uppercase tracking-wider drop-shadow-sm">${entry.subject_code}</p>
                            <p class="text-[11px] font-bold opacity-90 mb-1 leading-tight">${teacherFirstName}</p>
                            <p class="text-[10px] font-bold opacity-80 leading-none mb-2">ห้อง ${entry.room_name || '-'}</p>
                            <span class="text-[9px] font-black bg-white/20 border border-white/10 px-3 py-1 rounded-full shadow-sm text-white">${entry.classroom_level}/${entry.classroom_name}</span>
                        </div>
                    `;
                }

                html += `
                    <td class="border-r border-slate-50 last:border-r-0 p-4 min-w-[170px] h-[155px] align-top bg-white transition-colors group-hover:bg-blue-50/20">
                        ${content}
                    </td>
                `;
            });
            html += `</tr>`;
        });

        html += `</tbody></table></div>`;
        container.innerHTML = html;
        lucide.createIcons();
    }

    async function renderMiniTimetable(teacherId) {
        const mini = document.getElementById('miniTimetable');
        const res = await fetch(`api/manage.php?action=get_teacher_timetable&teacher_id=${teacherId}`);
        const data = await res.json();
        
        const zoom = state.timetableZoom;
        const baseHeight = 55 * zoom;
        const baseFontSize = 10 * zoom;
        const subFontSize = 7 * zoom;

        const days = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
        let html = `
            <div class="bg-indigo-950 p-3 font-black text-indigo-300 text-center flex items-center justify-center border-b border-indigo-900 border-r uppercase tracking-tighter" style="font-size: ${baseFontSize * 0.9}px">วัน/เวลา</div>
        `;
        
        timetablePeriods.forEach(p => {
            html += `
                <div class="bg-indigo-950 p-2 text-center border-b border-indigo-900 border-r last:border-r-0">
                    <p class="font-black text-indigo-400 uppercase tracking-tighter" style="font-size: ${baseFontSize * 0.8}px">คาบ ${p.period_number}</p>
                    <p class="font-black text-white" style="font-size: ${baseFontSize * 0.9}px">${p.start_time.substring(0, 5)}</p>
                </div>
            `;
        });
        
        days.forEach((dayName, dayIdx) => {
            const currentDayNumber = dayIdx + 1;
            html += `<div class="bg-blue-600 p-3 text-center font-black text-white flex items-center justify-center border-b border-white/10 border-r uppercase" style="font-size: ${baseFontSize * 1.1}px">${dayName}</div>`;
            timetablePeriods.forEach(p => {
                const entry = data.find(d => d.day == currentDayNumber && d.period == p.period_number);
                
                let cellContent = '';
                if (p.type === 'break' || p.type === 'activity') {
                    cellContent = `<div class="w-full h-full bg-slate-900/40 flex flex-col items-center justify-center gap-0.5">
                        <i data-lucide="${p.type === 'break' ? 'coffee' : 'star'}" size="${10 * zoom}" class="text-indigo-400 opacity-50"></i>
                        <span class="font-black text-indigo-400 uppercase tracking-tighter opacity-50" style="font-size: ${baseFontSize * 0.6}px">${p.type === 'break' ? 'พัก' : 'กิจกรรม'}</span>
                    </div>`;
                } else if (entry) {
                    const colorClasses = getColorForSubject(entry.subject_code);
                    cellContent = `<div class="${colorClasses} w-full h-full flex flex-col items-center justify-center rounded-lg border font-black shadow-lg p-1 transition-transform hover:scale-110 hover:z-20">
                        <span class="leading-none mb-0.5 text-white drop-shadow-sm" style="font-size: ${baseFontSize}px">${entry.subject_code}</span>
                        <div class="flex flex-col items-center gap-0.5 mt-0.5">
                            <span class="text-white/80 leading-none font-bold" style="font-size: ${subFontSize * 0.8}px">ห้อง ${entry.room_name || '-'}</span>
                            <span class="text-white leading-none bg-white/20 px-1.5 py-0.5 rounded-full border border-white/10" style="font-size: ${subFontSize}px">${entry.classroom_level}/${entry.classroom_name}</span>
                        </div>
                    </div>`;
                }

                html += `<div class="bg-white p-1.5 border-b border-r last:border-r-0 border-slate-100 transition-colors hover:bg-indigo-50/50" style="min-height: ${baseHeight}px">
                    ${cellContent}
                </div>`;
            });
        });
        mini.innerHTML = html;
        mini.style.gridTemplateColumns = `100px repeat(${timetablePeriods.length}, 1fr)`;
        lucide.createIcons();
    }

    init();
</script>
</body>
</html>
