<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ด - School Timetable Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r h-screen sticky top-0 p-6 flex flex-col gap-8 shadow-sm">
        <div class="flex items-center gap-2">
            <div class="h-10 w-10 bg-blue-600 rounded-xl flex items-center justify-center text-white">
                <i data-lucide="calendar"></i>
            </div>
            <div>
                <h1 class="font-bold text-slate-900 leading-none">Timetable</h1>
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Pro v1.0</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1">
            <a href="#" class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 text-blue-700 font-semibold group transition-all">
                <i data-lucide="layout-dashboard" size="20"></i> แดชบอร์ด
            </a>
            <a href="timetable.php" class="flex items-center gap-3 p-3 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all font-medium">
                <i data-lucide="calendar-range" size="20"></i> จัดการตารางสอน
            </a>
            <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all font-medium">
                <i data-lucide="book-open" size="20"></i> ข้อมูลรายวิชา
            </a>
            <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-all font-medium">
                <i data-lucide="users" size="20"></i> ข้อมูลครู
            </a>
        </nav>

        <div class="pt-6 border-t">
            <button onclick="logout()" class="flex items-center gap-3 p-3 rounded-xl text-red-500 hover:bg-red-50 w-full transition-all font-medium">
                <i data-lucide="log-out" size="20"></i> ออกจากระบบ
            </button>
        </div>
    </aside>

    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-900">ภาพรวมโรงเรียน</h2>
                <p class="text-slate-500">จัดการทรัพยากรและการเรียนการสอนในที่เดียว</p>
            </div>
            <div class="flex gap-4">
                <button onclick="updateDb()" class="bg-white border text-slate-700 px-4 py-2 rounded-xl font-medium flex items-center gap-2 hover:bg-slate-50 transition-all">
                    <i data-lucide="refresh-cw" size="18"></i> อัปเดตฐานข้อมูล
                </button>
                <a href="timetable.php" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg hover:shadow-blue-500/20">
                    <i data-lucide="plus" size="18"></i> เริ่มจัดตารางสอน
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl border shadow-sm flex flex-col gap-4">
                <div class="h-10 w-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center"><i data-lucide="book"></i></div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-tighter">วิชาทั้งหมด</p>
                    <p class="text-2xl font-bold text-slate-900">48</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl border shadow-sm flex flex-col gap-4">
                <div class="h-10 w-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center"><i data-lucide="users-2"></i></div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-tighter">ครูผู้สอน</p>
                    <p class="text-2xl font-bold text-slate-900">22</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl border shadow-sm flex flex-col gap-4">
                <div class="h-10 w-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center"><i data-lucide="door-open"></i></div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-tighter">ห้องเรียน</p>
                    <p class="text-2xl font-bold text-slate-900">18</p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl border shadow-sm bg-gradient-to-br from-indigo-600 to-blue-700 text-white">
                <div class="h-10 w-10 bg-white/20 rounded-lg flex items-center justify-center"><i data-lucide="check-circle"></i></div>
                <div>
                    <p class="text-xs font-bold text-white/60 uppercase tracking-tighter">ความสมบูรณ์</p>
                    <p class="text-2xl font-bold">92%</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white rounded-2xl border shadow-sm p-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-xl">ชั้นเรียนล่าสุด</h3>
                    <button class="text-blue-600 text-sm font-semibold hover:underline">ดูทั้งหมด</button>
                </div>
                <div class="space-y-4">
                    <div class="p-4 border rounded-xl flex items-center justify-between hover:bg-slate-50 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 bg-slate-100 rounded-xl flex items-center justify-center font-bold text-slate-600 group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">ป.1</div>
                            <div>
                                <p class="font-bold">ข้อมูลตารางสอน ป.1/1</p>
                                <p class="text-xs text-slate-400">อัปเดตเมื่อ: 2 ชั่วโมงที่แล้ว</p>
                            </div>
                        </div>
                        <button class="text-slate-400 hover:text-blue-600"><i data-lucide="external-link"></i></button>
                    </div>
                    <div class="p-4 border rounded-xl flex items-center justify-between hover:bg-slate-50 transition-all group border-dashed">
                        <div class="flex items-center gap-4 opacity-50">
                            <div class="h-12 w-12 bg-slate-100 rounded-xl flex items-center justify-center font-bold text-slate-600">ป.1</div>
                            <div>
                                <p class="font-bold">ข้อมูลตารางสอน ป.1/2</p>
                                <p class="text-xs text-slate-400">รอการคำนวณอัตโนมัติ...</p>
                            </div>
                        </div>
                        <button class="text-slate-400 hover:text-blue-600"><i data-lucide="wand-2"></i></button>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border shadow-sm p-8 flex flex-col gap-6">
                <h3 class="font-bold text-xl">สถานะฐานข้อมูล</h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-3 text-sm text-slate-600">
                        <div class="h-2 w-2 bg-green-500 rounded-full shadow-[0_0_8px_rgba(34,197,94,0.6)]"></div>
                        MySQL Connected: Live
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        ขณะนี้เซิร์ฟเวอร์ฐานข้อมูลทำงานปกติ คุณสามารถอัปเดตโครงสร้างตารางได้โดยคลิกปุ่ม "อัปเดตฐานข้อมูลอัตโนมัติ" ในกรณีที่มีการเพิ่มวิชาหรือครูใหม่ๆ
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
