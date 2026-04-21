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
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        <a href="dashboard.php" class="flex items-center gap-3 p-3 rounded-xl <?php echo $current_page == 'dashboard.php' ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium'; ?> transition-all">
            <i data-lucide="layout-dashboard" size="20"></i> แดชบอร์ด
        </a>
        <a href="timetable.php" class="flex items-center gap-3 p-3 rounded-xl <?php echo $current_page == 'timetable.php' ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium'; ?> transition-all">
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

<script>
    function logout() {
        localStorage.removeItem('user');
        window.location.href = 'index.php';
    }
</script>
