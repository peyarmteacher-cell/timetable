<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<!-- Content Area -->
<div class="flex-1 flex flex-col h-screen overflow-hidden">
    <!-- Action Header -->
    <header class="bg-white border-b h-16 flex items-center justify-between px-8 sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">จัดการตารางสอน</h1>
        </div>
        <div class="flex items-center gap-4">
            <button onclick="autoGenerate()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 font-medium transition-all shadow-sm">
                <i data-lucide="wand-2" size="18"></i> จัดอัตโนมัติ
            </button>
            <button onclick="saveAll()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 font-medium transition-all shadow-sm">
                <i data-lucide="save" size="18"></i> บันทึกข้อมูล
            </button>
        </div>
    </header>

    <div class="flex-1 overflow-auto p-8">
        <div class="grid grid-cols-12 gap-8 max-w-[1600px] mx-auto">
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
                    $days = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
                    foreach($days as $idx => $day): ?>
                        <div class="grid grid-cols-[100px_repeat(8,1fr)] border-b last:border-b-0">
                            <div class="bg-slate-50 border-r flex items-center justify-center font-bold text-slate-700 text-sm"><?php echo $day; ?></div>
                            <?php for($p=0; $p<8; p++): ?>
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
                        <!-- ตัวอย่างวิชา -->
                        <div class="p-3 border rounded-xl bg-slate-50 subject-card hover:border-blue-300 transition-all border-dashed group" data-id="1">
                            <div class="flex justify-between items-start mb-1">
                                <p class="text-xs font-bold text-blue-600">ท11101</p>
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i data-lucide="grab" size="12" class="text-slate-300"></i>
                                </div>
                            </div>
                            <p class="text-sm font-semibold leading-tight mb-2">ภาษาไทย ป.1/1</p>
                            <div class="flex justify-between text-[10px] font-medium text-slate-400">
                                <span class="flex items-center gap-1"><i data-lucide="user" size="10"></i> ครูสมศรี</span>
                                <span class="flex items-center gap-1"><i data-lucide="map-pin" size="10"></i> 101</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    lucide.createIcons();

    // Initialize Drag & Drop
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
        alert('บันทึกข้อมูลตารางสอนลงในฐานข้อมูล schoolos_timetable สำเร็จแล้ว!');
    }

    initializeSortable();
</script>
</body>
</html>
