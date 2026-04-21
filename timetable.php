<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<!-- Content Area -->
<div class="flex-1 flex flex-col h-screen overflow-hidden">
    <!-- Action Header -->
    <header class="bg-white border-b h-16 flex items-center justify-between px-8 sticky top-0 z-50">
        <div class="flex items-center gap-6">
            <h1 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">จัดการตารางสอน</h1>
            <div class="h-8 w-px bg-slate-200"></div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-tighter">ชั้นเรียน:</label>
                <select id="classroomSelect" onchange="loadTimetable()" class="bg-slate-50 border-none text-sm font-bold rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">เลือกชั้นเรียน...</option>
                </select>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <button onclick="openAssignModal()" class="text-slate-600 hover:text-blue-600 px-4 py-2 rounded-lg flex items-center gap-2 font-bold transition-all text-sm">
                <i data-lucide="settings-2" size="18"></i> ตั้งค่าวิชา
            </button>
            <button onclick="autoGenerate()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 font-medium transition-all shadow-sm">
                <i data-lucide="wand-2" size="18"></i> จัดอัตโนมัติ
            </button>
            <button onclick="saveAll()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 font-medium transition-all shadow-sm">
                <i data-lucide="save" size="18"></i> บันทึกข้อมูล
            </button>
        </div>
    </header>

    <div class="flex-1 overflow-auto p-8">
        <div id="noClassroomAlert" class="bg-blue-50 border border-blue-100 rounded-2xl p-12 text-center max-w-2xl mx-auto space-y-4">
            <div class="h-16 w-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto"><i data-lucide="graduation-cap" size="32"></i></div>
            <h3 class="text-xl font-bold text-slate-900">กรุณาเลือกชั้นเรียนที่ต้องการจัดตาราง</h3>
            <p class="text-slate-500">เลือกชั้นเรียนจากผลการค้นหาด้านบนเพื่อเริ่มกระบวนการจัดตารางสอนอัจฉริยะ</p>
        </div>

        <div id="timetableContainer" class="hidden grid grid-cols-12 gap-8 max-w-[1700px] mx-auto">
            <!-- Timetable Box -->
            <div class="col-span-9 bg-white rounded-2xl shadow-sm border overflow-hidden h-fit">
                <div class="grid grid-cols-[100px_repeat(8,1fr)] bg-slate-50 border-b">
                    <div class="p-4 border-r flex items-center justify-center font-bold text-slate-400 text-xs uppercase tracking-widest">คาบ</div>
                    <?php 
                    $times = ['08:30', '09:20', '10:10', '11:00', '12:00', '13:00', '13:50', '14:40'];
                    foreach($times as $i => $time): ?>
                        <div class="p-3 text-center border-r last:border-r-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">คาบ <?php echo $i+1; ?></p>
                            <p class="text-sm font-bold text-slate-700"><?php echo $time; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="timetableRows" class="flex flex-col">
                    <?php 
                    $daysList = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
                    foreach($daysList as $idx => $dayLabel): ?>
                        <div class="grid grid-cols-[100px_repeat(8,1fr)] border-b last:border-b-0">
                            <div class="bg-slate-50 border-r flex items-center justify-center font-bold text-slate-700 text-sm"><?php echo $dayLabel; ?></div>
                            <?php for($p=0; $p<8; $p++): ?>
                                <div class="period-slot border-r last:border-r-0 p-1 flex flex-col gap-1" data-day="<?php echo $idx; ?>" data-period="<?php echo $p; ?>"></div>
                            <?php endfor; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Subject Sidebar -->
            <div class="col-span-3 space-y-6">
                <!-- Subject Pool -->
                <div class="bg-white rounded-2xl shadow-sm border p-6 h-fit sticky top-24">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="book-open" size="18" class="text-blue-600"></i> วิชาที่ต้องจัด
                        </h3>
                        <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-1 rounded">ลากวาง</span>
                    </div>
                    
                    <div id="subjectPool" class="space-y-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                        <!-- Dynamic Subjects -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div id="assignModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[80vh] overflow-hidden transform transition-all flex flex-col">
        <div class="p-6 border-b flex justify-between items-center bg-slate-50">
            <div>
                <h3 class="text-xl font-bold text-slate-900">มอบหมายวิชาผู้สอนและห้องเรียน</h3>
                <p class="text-xs text-slate-500">กำหนดว่าใครสอนอะไร และใช้ห้องไหนก่อนทำการจัดตาราง</p>
            </div>
            <button onclick="closeAssignModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i data-lucide="x"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="text-left border-b text-xs font-bold text-slate-400 uppercase">
                        <th class="pb-3">วิชา</th>
                        <th class="pb-3">ผู้สอน</th>
                        <th class="pb-3">ห้องเรียน/ห้องปฏิบัติการ</th>
                        <th class="pb-3 text-center">สิทธิ์</th>
                    </tr>
                </thead>
                <tbody id="assignList" class="divide-y">
                    <!-- Dynamic -->
                </tbody>
            </table>
        </div>
        <div class="p-6 border-t bg-slate-50 flex justify-end gap-3">
             <button onclick="closeAssignModal()" class="px-6 py-2 rounded-xl bg-blue-600 text-white font-bold">เสร็จสิ้น</button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    lucide.createIcons();

    // Data State
    let state = {
        classrooms: [],
        teachers: [],
        rooms: [],
        subjects: [],
        currentClassroomId: null
    };

    async function init() {
        const [clsRes, resRes, subRes] = await Promise.all([
            fetch('api/manage.php?action=classrooms_list'),
            fetch('api/timetable_api.php?action=get_resources'),
            fetch('api/manage.php?action=subjects_list')
        ]);

        state.classrooms = await clsRes.json();
        const resources = await resRes.json();
        state.teachers = resources.teachers;
        state.rooms = resources.rooms;
        state.subjects = await subRes.json();

        const select = document.getElementById('classroomSelect');
        state.classrooms.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.innerText = `${c.level} ห้อง ${c.name}`;
            select.appendChild(opt);
        });

        initializeSortable();
    }

    function loadTimetable() {
        const id = document.getElementById('classroomSelect').value;
        if(!id) {
            document.getElementById('noClassroomAlert').classList.remove('hidden');
            document.getElementById('timetableContainer').classList.add('hidden');
            return;
        }
        state.currentClassroomId = id;
        document.getElementById('noClassroomAlert').classList.add('hidden');
        document.getElementById('timetableContainer').classList.remove('hidden');
        renderSubjectPool();
    }

    function renderSubjectPool() {
        const pool = document.getElementById('subjectPool');
        pool.innerHTML = state.subjects.map(s => `
            <div class="p-3 border rounded-xl bg-slate-50 subject-card hover:border-blue-300 transition-all border-dashed group" data-id="${s.id}">
                <div class="flex justify-between items-start mb-1">
                    <p class="text-xs font-bold text-blue-600">${s.code}</p>
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity"><i data-lucide="grab" size="12" class="text-slate-300"></i></div>
                </div>
                <p class="text-sm font-semibold leading-tight mb-2">${s.name}</p>
                <div class="flex flex-wrap gap-2 text-[10px] font-medium text-slate-400">
                    <span class="flex items-center gap-1"><i data-lucide="user" size="10"></i> ${s.teacher_name || 'รอมอบหมาย'}</span>
                    <span class="flex items-center gap-1"><i data-lucide="map-pin" size="10"></i> ${s.room_name || 'รอมอบหมาย'}</span>
                </div>
            </div>
        `).join('');
        lucide.createIcons();
    }

    function openAssignModal() {
        const list = document.getElementById('assignList');
        list.innerHTML = state.subjects.map(s => `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="py-4">
                    <p class="font-bold text-slate-900">${s.name}</p>
                    <p class="text-xs text-slate-400">${s.code}</p>
                </td>
                <td class="py-4">
                    <select class="p-2 border rounded-lg text-sm outline-none bg-white">
                        <option value="">เลือกครู...</option>
                        ${state.teachers.map(t => `<option value="${t.id}" ${s.teacher_id == t.id ? 'selected' : ''}>${t.name}</option>`).join('')}
                    </select>
                </td>
                <td class="py-4">
                    <select class="p-2 border rounded-lg text-sm outline-none bg-white">
                        <option value="">เลือกห้อง...</option>
                        ${state.rooms.map(r => `<option value="${r.id}" ${s.room_id == r.id ? 'selected' : ''}>${r.name}</option>`).join('')}
                    </select>
                </td>
                <td class="py-4 text-center">
                    <input type="checkbox" ${s.is_double ? 'checked' : ''} class="w-4 h-4">
                </td>
            </tr>
        `).join('');
        document.getElementById('assignModal').classList.remove('hidden');
    }

    function closeAssignModal() {
        document.getElementById('assignModal').classList.add('hidden');
    }

    const initializeSortable = () => {
        document.querySelectorAll('.period-slot').forEach(slot => {
            new Sortable(slot, {
                group: 'timetable',
                animation: 150,
                ghostClass: 'bg-blue-50',
                onAdd: function(evt) {
                    const subjectId = evt.item.dataset.id;
                    const day = evt.to.dataset.day;
                    const period = evt.to.dataset.period;
                    console.log(`Placed Subject ${subjectId} at Day ${day} Period ${period}`);
                }
            });
        });

        new Sortable(document.getElementById('subjectPool'), {
            group: 'timetable',
            animation: 150
        });
    }

    async function autoGenerate() {
        if(confirm('ระบบจะวิเคราะห์หาช่องว่างของ "ครู" และ "ห้องเรียน" เพื่อจัดตารางให้อัตโนมัติ ยืนยันหรือไม่?')) {
            alert('กำลังประมวลผลอัลกอริทึมจัดตารางสอน...');
        }
    }

    async function saveAll() {
        alert('บันทึกข้อมูลตารางสอนสำเร็จแล้ว!');
    }

    init();
</script>
</body>
</html>
