<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนโรงเรียน - School Timetable Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl border p-8 space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-slate-900 mb-1">ลงทะเบียนโรงเรียนใหม่</h1>
            <p class="text-slate-500 text-sm">ขอเข้าใช้งานระบบจัดตารางสอนอัจฉริยะ</p>
        </div>
        
        <form id="registerForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2 md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">ชื่อโรงเรียน</label>
                <input type="text" name="school_name" class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" required>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-slate-700">รหัสโรงเรียน 10 หลัก</label>
                <input type="text" name="school_code" class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" required>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-slate-700">ชื่อผู้ติดต่อ</label>
                <input type="text" name="name" class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" required>
            </div>
            <div class="space-y-2 md:col-span-2">
                <label class="text-sm font-semibold text-slate-700">ชื่อผู้ใช้ (Login)</label>
                <input type="text" name="username" class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" required>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-slate-700">รหัสผ่าน</label>
                <input type="password" name="password" class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" required>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-slate-700">ยืนยันรหัสผ่าน</label>
                <input type="password" name="confirm_password" class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" required>
            </div>
            <div class="md:col-span-2 pt-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-blue-500/20">
                    ลงทะเบียนขอใช้งาน
                </button>
            </div>
        </form>

        <div class="text-center text-sm text-slate-500 pt-4 border-t">
            มีบัญชีอยู่แล้ว? <a href="index.php" class="text-blue-600 font-semibold hover:underline">เข้าสู่ระบบ</a>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            if (data.password !== data.confirm_password) {
                alert('รหัสผ่านไม่ตรงกัน');
                return;
            }

            try {
                const response = await fetch('api/auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (result.error) {
                    alert(result.error);
                } else {
                    alert('ลงทะเบียนสำเร็จ! กรุณารอการอนุมัติจาก Super Admin');
                    window.location.href = 'index.php';
                }
            } catch (err) {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            }
        });
    </script>
</body>
</html>
