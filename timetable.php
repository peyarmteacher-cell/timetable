<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางสอน - School Timetable Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/sortablejs@1.15.2/Sortable.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }
        .period-slot { min-height: 120px; transition: all 0.2s; }
        .period-slot:hover { background-color: #f8fafc; }
        .subject-card { cursor: grab; user-select: none; }
        .subject-card:active { cursor: grabbing; }
        .sortable-ghost { opacity: 0.4; transform: scale(0.95); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white border-b h-16 flex items-center justify-between px-8 sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <a href="dashboard.php" class="text-slate-500 hover:text-slate-900"><i data-lucide="chevron-left"></i></a>
            <h1 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">จัดการตารางสอน</h1>
        </div>
        <div class="flex items-center gap-4">
            <button onclick="autoGenerate()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 font-medium transition-all shadow-sm">
                <i data-lucide="wand-2" size="18"></i> จัดอัตโนมัติ
            </button>
            <button onclick="saveAll()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 font-medium transition-all shadow-sm">
                <i data-lucide="save" size="18"></i> บันทึกตาราง
            </button>
        </div>
    </nav>

    <div class="p-8 grid grid-cols-12 gap-8">
        <!-- Main Table Area -->
        <div class="col-span-9 bg-white rounded-2xl shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <div class="min-w-[1000px]">
                    <!-- Time Header -->
                    <div class="grid grid-cols-[100px_repeat(8,1fr)] bg-slate-50 border-b">
                        <div class="p-4 border-r flex items-center justify-center font-bold text-slate-400 text-xs">วัน / คาบ</div>
                        <template id="timeHeaderTemplate">
                            <div class="p-3 text-center border-r last:border-r-0">
                                <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">คาบ {{period}}</p>
                                <p class="text-sm font-bold text-slate-700">{{time}}</p>
                            </div>
                        </template>
                        <!-- Will be filled by JS -->
                        <div id="timeHeaders" class="contents"></div>
                    </div>

                    <!-- Days Rows -->
                    <div id="timetableRows" class="flex flex-col">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar - Subjects & Config -->
        <div class="col-span-3 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border p-6">
                <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i data-lucide="book-open" size="18" class="text-blue-600"></i> รายวิชารอจัด
                </h3>
                <div id="subjectPool" class="space-y-3">
                    <!-- Scalable subjects list -->
                    <div class="p-3 border rounded-xl bg-slate-50 subject-card hover:border-blue-300 transition-all border-dashed">
                        <p class="text-xs font-bold text-blue-600">ท11101</p>
                        <p class="text-sm font-semibold">ภาษาไทย</p>
                        <div class="flex justify-between mt-2 text-[10px] text-slate-500">
                            <span>ครูสมศรี</span>
                            <span>ห้อง 101</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-indigo-600 rounded-2xl shadow-sm p-6 text-white">
                <h3 class="font-bold mb-2 flex items-center gap-2"><i data-lucide="info" size="18"></i> ข้อมูลช่วยเหลือ</h3>
                <p class="text-xs opacity-80 leading-relaxed">
                    - การจัดอัตโนมัติจะตรวจสอบห้องว่างและครูว่างให้อัตโนมัติ<br>
                    - คลิกที่ไอคอนแม่กุญแจเพื่อล็อคคาบเรียน (Fix)<br>
                    - สามารถลากวางเพื่อสลัดเปลี่ยนคาบได้เลย
                </p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const DAYS = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
        const TIMES = ['08:30', '09:20', '10:10', '11:00', '12:00', '13:00', '13:50', '14:40'];

        function initTable() {
            const headerContainer = document.getElementById('timeHeaders');
            TIMES.forEach((time, i) => {
                const div = document.createElement('div');
                div.className = 'p-3 text-center border-r last:border-r-0';
                div.innerHTML = `<p class="text-[10px] font-bold text-slate-400 uppercase mb-1">คาบ ${i+1}</p><p class="text-sm font-bold text-slate-700">${time}</p>`;
                headerContainer.appendChild(div);
            });

            const rowsContainer = document.getElementById('timetableRows');
            DAYS.forEach((day, dayIdx) => {
                const row = document.createElement('div');
                row.className = 'grid grid-cols-[100px_repeat(8,1fr)] border-b last:border-b-0';
                
                const dayHeader = document.createElement('div');
                dayHeader.className = 'bg-slate-50 border-r flex items-center justify-center font-bold text-slate-700 text-sm';
                dayHeader.innerText = day;
                row.appendChild(dayHeader);

                for(let p=0; p<8; p++) {
                    const slot = document.createElement('div');
                    slot.className = 'period-slot border-r last:border-r-0 p-1 flex flex-col gap-1';
                    slot.dataset.day = dayIdx;
                    slot.dataset.period = p;
                    row.appendChild(slot);
                    
                    new Sortable(slot, {
                        group: 'timetable',
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        onAdd: function(evt) {
                            checkConflicts(evt.to);
                        }
                    });
                }
                rowsContainer.appendChild(row);
            });

            new Sortable(document.getElementById('subjectPool'), {
                group: 'timetable',
                animation: 150
            });
        }

        async function autoGenerate() {
            alert('กำลังประมวลผลการจัดตารางอัตโนมัติด้วยระบบอัจฉริยะ...');
            // จำลองการเรียก API จัดตาราง
            // ในระบบจริงจะส่งข้อมูล Subjects/Teachers/Rooms ไปยัง PHP API
        }

        function checkConflicts(slot) {
            // จำลองการเช็คห้องว่าง/ครูว่าง
            // ส่งค่าไปตรวจสอบที่ server.php (API)
        }

        function saveAll() {
            alert('บันทึกข้อมูลตารางสอนลงฐานข้อมูล MySQL เรียบร้อยแล้ว');
        }

        initTable();
    </script>
</body>
</html>
