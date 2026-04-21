<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<main class="flex-1 p-8">
    <header class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-slate-900">ภาพรวมระบบ</h2>
            <p class="text-slate-500">ยินดีต้อนรับสู่ระบบจัดการตารางสอนอัจฉริยะ</p>
        </div>
        <div class="flex gap-4">
            <button onclick="updateDb()" class="bg-white border text-slate-700 px-4 py-2 rounded-xl font-medium flex items-center gap-2 hover:bg-slate-50 transition-all">
                <i data-lucide="refresh-cw" size="18"></i> ซิงค์ MySQL
            </button>
            <a href="timetable.php" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg hover:shadow-blue-500/20">
                <i data-lucide="plus" size="18"></i> สร้างตารางใหม่
            </a>
        </div>
    </header>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl border shadow-sm flex flex-col gap-4">
            <div class="h-10 w-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center"><i data-lucide="book"></i></div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-tighter">วิชาทั้งหมด</p>
                <p class="text-2xl font-bold text-slate-900">0</p>
            </div>
        </div>
        <!-- เพิ่มการ์ดอื่นๆ ตามต้องการ -->
    </div>
</main>

<script>
    lucide.createIcons();
    function updateDb() {
        alert('กำลังเชื่อมต่อกับฐานข้อมูล schoolos_timetable และตรวจสอบความถูกต้องของตาราง...');
    }
</script>
</body>
</html>�สามารถอัปเดตโครงสร้างตารางได้โดยคลิกปุ่ม "อัปเดตฐานข้อมูลอัตโนมัติ" ในกรณีที่มีการเพิ่มวิชาหรือครูใหม่ๆ
                    </p>
                </div>
                <img src="https://picsum.photos/seed/server/300/200" alt="Status" class="rounded-xl object-cover h-32 w-full mt-auto opacity-50">
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
        function logout() {
            localStorage.removeItem('user');
            window.location.href = 'index.php';
        }
        function updateDb() {
            alert('กำลังตรวจสอบและอัปเดตตารางฐานข้อมูล MySQL ให้เป็นเวอร์ชันปัจจุบัน...');
        }
    </script>
</body>
</html>
