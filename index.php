<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - School Timetable Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border p-8 space-y-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">School Timetable Pro</h1>
            <p class="text-muted-foreground text-sm">เข้าสู่ระบบเพื่อจัดการตารางสอนโรงเรียนของคุณ</p>
        </div>
        
        <form id="loginForm" class="space-y-4">
            <div class="space-y-2">
                <label class="text-sm font-semibold text-slate-700">ชื่อผู้ใช้</label>
                <input type="text" name="username" class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" placeholder="admin" required>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-semibold text-slate-700">รหัสผ่าน</label>
                <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all" placeholder="123456" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-blue-500/20">
                เข้าสู่ระบบ
            </button>
        </form>

        <div class="text-center text-sm text-slate-500 pt-4 border-t">
            ยังไม่มีบัญชีโรงเรียน? <a href="register.php" class="text-blue-600 font-semibold hover:underline">ลงทะเบียนขอใช้งาน</a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            // ในระบบจริงจะส่งไปที่ login_process.php
            // สำหรับ Preview เราจะจำลองการเข้าสู่ระบบ
            try {
                const response = await fetch('api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const user = await response.json();
                if (user.error) {
                    alert(user.error);
                } else {
                    localStorage.setItem('user', JSON.stringify(user));
                    window.location.href = 'dashboard.php';
                }
            } catch (err) {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            }
        });
    </script>
</body>
</html>
