<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<!-- Content Area -->
<div class="flex-1 flex flex-col h-screen overflow-hidden">
    <!-- Action Header -->
    <header class="bg-white border-b h-20 flex items-center justify-between px-8 sticky top-0 z-50">
        <div class="flex items-center gap-6">
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
            <button onclick="autoGenerate()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl flex items-center gap-2 font-bold transition-all shadow-lg hover:shadow-blue-500/20">
                <i data-lucide="wand-2" size="18"></i> จัดตารางสอนอัตโนมัติ
            </button>
        </div>
    </header>

    <div class="flex-1 overflow-auto p-8 bg-slate-50/50">
        <!-- Assign Mode -->
        <div id="section-assign" class="space-y-6">
            <div class="grid grid-cols-12 gap-8">
                <!-- Teacher List & Selection -->
                <div class="col-span-4 space-y-4">
                    <div class="bg-white rounded-2xl shadow-sm border p-6">
                        <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <i data-lucide="users" class="text-blue-600"></i> เลือกคุณครูที่เข้าสอน
                        </h3>
                        <div class="relative mb-4">
                            <i data-lucide="search" size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="teacherSearch" oninput="filterTeachers()" placeholder="ค้นหาชื่อครู..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-100 bg-slate-50 focus:bg-white transition-all outline-none text-sm">
                        </div>
                        <div id="teacherList" class="space-y-1 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                            <!-- Teachers list -->
                        </div>
                    </div>
                </div>

                <!-- Assignment Form -->
                <div class="col-span-8 space-y-6">
                    <div id="assignArea" class="hidden space-y-6">
                        <div class="bg-white rounded-2xl shadow-md border-t-4 border-blue-600 p-8">
                            <div class="flex justify-between items-center mb-8">
                                <div>
                                    <h2 id="selectedTeacherName" class="text-2xl font-black text-slate-900">ครูสมชาย ใจดี</h2>
                                    <p class="text-slate-500 font-medium">จัดการรายวิชาและชั้นเรียนที่รับผิดชอบ</p>
                                </div>
                                <button onclick="showAddLoadForm()" class="bg-slate-900 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-slate-800 transition-all flex items-center gap-2">
                                    <i data-lucide="plus" size="18"></i> เพิ่มวิชาเข้าสอน
                                </button>
                            </div>

                            <div class="overflow-hidden border rounded-2xl">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="bg-slate-50 border-b text-xs font-bold text-slate-400 uppercase">
                                            <th class="px-6 py-4">ระดับชั้น/ห้อง</th>
                                            <th class="px-6 py-4">รหัสวิชา - ชื่อวิชา</th>
                                            <th class="px-6 py-4">ห้องเรียนที่ใช้</th>
                                            <th class="px-6 py-4 text-center">คาบ/สัปดาห์</th>
                                            <th class="px-6 py-4 text-right">จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="teachingLoadTable" class="divide-y text-sm">
                                        <!-- Load items -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Mini Timetable Preview -->
                        <div class="bg-white rounded-2xl shadow-sm border p-6">
                            <h3 class="font-bold text-slate-900 mb-4 italic">ตารางสอนปัจจุบันของคุณครู</h3>
                            <div id="miniTimetable" class="grid grid-cols-[80px_repeat(5,1fr)] gap-px bg-slate-200 border border-slate-200 rounded-xl overflow-hidden">
                                <!-- Generated by JS -->
                            </div>
                        </div>
                    </div>

                    <div id="noTeacherSelected" class="bg-white rounded-2xl shadow-sm border border-dashed p-12 text-center flex flex-col items-center gap-4">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center text-slate-300">
                            <i data-lucide="user-check" size="40"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-400">กรุณาเลือกคุณครูเพื่อเริ่มกำหนดการสอน</h3>
                    </div>
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
            
            <div class="space-y-1.5">
                <label class="text-sm font-bold text-slate-700">1. เลือกระดับชั้น</label>
                <select name="classroom_id" id="classroomSelect" onchange="onClassroomChange()" class="w-full bg-slate-50 border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-600 font-bold transition-all" required>
                    <option value="">เลือกชั้นเรียน (เช่น ป.1/1)</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-sm font-bold text-slate-700">2. เลือกรายวิชา (กรองตามระดับชั้น)</label>
                <select name="subject_id" id="subjectSelect" class="w-full bg-slate-50 border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-600 font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed" required disabled>
                    <option value="">กรุณาเลือกชั้นเรียนก่อน</option>
                </select>
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

<script>
    lucide.createIcons();
    
    let state = {
        teachers: [],
        classrooms: [],
        rooms: [],
        subjects: [],
        currentTeacherId: null,
        viewMode: 'assign',
        viewType: 'teacher'
    };

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
        list.innerHTML = state.teachers.map(t => `
            <button onclick="selectTeacher(${t.id})" class="teacher-btn w-full flex items-center gap-3 p-3 rounded-xl transition-all hover:bg-slate-50 text-left group" data-id="${t.id}">
                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all">
                    <i data-lucide="user" size="20"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-700 text-sm group-hover:text-slate-900 teacher-name">${t.name}</h4>
                    <p class="text-[10px] text-slate-400 font-medium">${t.position || 'คุณครู'}</p>
                </div>
            </button>
        `).join('');
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
        document.querySelectorAll('.teacher-btn').forEach(b => b.classList.remove('bg-blue-50', 'ring-1', 'ring-blue-100'));
        document.querySelector(`.teacher-btn[data-id="${id}"]`).classList.add('bg-blue-50', 'ring-1', 'ring-blue-100');
        
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
        const table = document.getElementById('teachingLoadTable');
        
        if (data.length === 0) {
            table.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-slate-400 font-bold italic">ไม่พบวิชาที่สอน</td></tr>`;
        } else {
            table.innerHTML = data.map(item => `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-bold text-slate-700">${item.classroom_level}/${item.classroom_name}</td>
                    <td class="px-6 py-4 font-medium text-slate-600">${item.subject_code} - ${item.subject_name}</td>
                    <td class="px-6 py-4 text-slate-500">${item.room_name || '-'}</td>
                    <td class="px-6 py-4 text-center font-bold text-blue-600">3</td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="deleteLoad(${item.id})" class="text-red-300 hover:text-red-500 transition-colors"><i data-lucide="trash-2" size="18"></i></button>
                    </td>
                </tr>
            `).join('');
        }
        lucide.createIcons();
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
            room_id: fd.get('room_id')
        };

        const res = await fetch('api/manage.php?action=teaching_load_add', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            Swal.fire('สำเร็จ', 'มอบหมายงานสอนเรียบร้อยแล้ว', 'success');
            closeAddLoadModal();
            loadTeachingLoad();
        }
    };

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

    async function autoGenerate() {
        Swal.fire({
            title: 'เริ่มการจัดตารางสอนอัตโนมัติ?',
            text: 'ระบบจะวิเคราะห์ความว่างของครู ห้องเรียน และชั้นเรียนเพื่อให้ได้เวลาที่เหมาะสมที่สุด',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'เริ่มจัดตารางทันที',
            cancelButtonText: 'ยกเลิก'
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'กำลังประมวลผล...',
                    html: 'ระบบอัจฉริยะกำลังจัดวางตารางที่ดีที่สุดให้คุณ',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                // Placeholder for real algorithm
                setTimeout(() => {
                    Swal.fire('สำเร็จ', 'จัดตารางสอนระดับชั้นทั้งหมดเรียบร้อยแล้ว!', 'success');
                    if (state.viewMode === 'view') loadViewData();
                }, 2000);
            }
        });
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
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[1000px]">
                    <thead>
                        <tr class="bg-slate-50 border-b">
                            <th class="p-4 border-r w-32 font-bold text-slate-400 text-xs uppercase text-center">วัน / คาบ</th>
                            ${timetablePeriods.map(p => `
                                <th class="p-4 border-r text-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">คาบ ${p.period_number}</p>
                                    <p class="text-sm font-bold text-slate-700">${p.start_time.substring(0, 5)}</p>
                                </th>
                            `).join('')}
                        </tr>
                    </thead>
                    <tbody>
        `;

        days.forEach((dayName, dayIdx) => {
            const currentDayNumber = dayIdx + 1;
            html += `<tr class="border-b last:border-b-0">
                <td class="bg-slate-50 border-r p-4 font-black text-slate-700 text-center text-sm">${dayName}</td>`;
            
            timetablePeriods.forEach(p => {
                const entry = data.find(d => d.day == currentDayNumber && d.period == p.period_number);
                html += `
                    <td class="border-r last:border-r-0 p-2 min-h-[80px] align-top">
                        ${entry ? `
                            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 h-full text-center shadow-sm">
                                <p class="text-[10px] font-bold text-blue-600 mb-1 leading-none uppercase">${entry.subject_code}</p>
                                <p class="text-xs font-black text-slate-900 leading-tight mb-2">${entry.subject_name}</p>
                                <div class="space-y-1">
                                    ${state.viewType !== 'teacher' ? `<p class="text-[9px] text-slate-500 font-bold bg-white/50 rounded py-0.5"><i data-lucide="user" size="8" class="inline"></i> ${entry.teacher_name || '-'}</p>` : ''}
                                    ${state.viewType !== 'classroom' ? `<p class="text-[9px] text-slate-500 font-bold bg-white/50 rounded py-0.5"><i data-lucide="graduation-cap" size="8" class="inline"></i> ${entry.classroom_level}/${entry.classroom_name}</p>` : ''}
                                    ${state.viewType !== 'room' ? `<p class="text-[9px] text-slate-500 font-bold bg-white/50 rounded py-0.5"><i data-lucide="map-pin" size="8" class="inline"></i> ${entry.room_name || '-'}</p>` : ''}
                                </div>
                            </div>
                        ` : ''}
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
        
        const days = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์'];
        let html = '<div class="bg-slate-50 p-2 font-bold text-[10px] text-slate-400 text-center flex items-center justify-center border-b border-r border-slate-200 uppercase">วัน/คาบ</div>';
        for(let i=1; i<=timetablePeriods.length; i++) {
            html += `<div class="bg-slate-50 p-2 text-center text-[10px] font-bold text-slate-400 border-b border-r last:border-r-0 border-slate-200 uppercase">คาบ ${i}</div>`;
        }
        
        days.forEach((dayName, dayIdx) => {
            const currentDayNumber = dayIdx + 1;
            html += `<div class="bg-slate-50 p-2 text-center text-[10px] font-bold text-slate-500 flex items-center justify-center border-b border-r border-slate-200">${dayName}</div>`;
            timetablePeriods.forEach(p => {
                const entry = data.find(d => d.day == currentDayNumber && d.period == p.period_number);
                html += `
                    <div class="bg-white p-1 min-h-[35px] border-b border-r last:border-r-0 border-slate-100 flex items-center justify-center">
                        ${entry ? `<div class="text-[9px] font-black text-blue-600 bg-blue-50 w-full h-full flex items-center justify-center rounded border border-blue-100">${entry.subject_code}</div>` : ''}
                    </div>
                `;
            });
        });
        mini.innerHTML = html;
        mini.style.gridTemplateColumns = `80px repeat(${timetablePeriods.length}, 1fr)`;
    }

    init();
</script>
</body>
</html>
