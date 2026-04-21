<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<?php 
if(!hasRole('super_admin')) {
    header("Location: dashboard.php");
    exit();
}
?>

<main class="flex-1 p-8 overflow-y-auto">
    <header class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-slate-900">จัดการสมาชิกระบบ</h2>
            <p class="text-slate-500">ดูแลและอนุมัติบัญชีผู้ใช้ของโรงเรียนต่างๆ ในระบบ</p>
        </div>
    </header>

    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b">
                        <th class="p-5 font-black text-slate-400 uppercase text-[10px] tracking-widest">ชื่อผู้ใช้ / บทบาท</th>
                        <th class="p-5 font-black text-slate-400 uppercase text-[10px] tracking-widest">ชื่อโรงเรียน</th>
                        <th class="p-5 font-black text-slate-400 uppercase text-[10px] tracking-widest">ชื่อฝ่ายงาน</th>
                        <th class="p-5 font-black text-slate-400 uppercase text-[10px] tracking-widest">สถานะ</th>
                        <th class="p-5 font-black text-slate-400 uppercase text-[10px] tracking-widest text-right">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody id="userList" class="divide-y divide-slate-50 text-slate-700">
                    <tr><td colspan="5" class="p-10 text-center">กำลังดึงข้อมูลสมาชิก...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    lucide.createIcons();

    async function fetchUsers() {
        const res = await fetch('api/manage.php?action=admin_users_list');
        const users = await res.json();
        const list = document.getElementById('userList');
        list.innerHTML = users.map(u => `
            <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="p-5">
                    <p class="font-bold text-slate-900">${u.name}</p>
                    <p class="text-[10px] font-bold text-blue-500 uppercase tracking-tighter italic">@${u.username} (${u.role})</p>
                </td>
                <td class="p-5 font-medium text-slate-500">${u.school_name || '<span class="text-xs text-amber-500 italic">ไม่มีสังกัด</span>'}</td>
                <td class="p-5">
                    ${u.is_academic ? '<span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] font-bold uppercase">ฝ่ายวิชาการ</span>' : '<span class="text-slate-300">-</span>'}
                </td>
                <td class="p-5">
                    ${u.is_approved ? 
                        '<span class="bg-green-100 text-green-700 text-[10px] font-black px-2 py-1 rounded-full uppercase">อนุมัติแล้ว</span>' : 
                        '<span class="bg-amber-100 text-amber-700 text-[10px] font-black px-2 py-1 rounded-full uppercase">รออนุมัติ</span>'
                    }
                </td>
                <td class="p-5 text-right flex justify-end gap-2">
                    ${!u.is_approved ? 
                        `<button onclick="approveUser(${u.id})" class="bg-blue-600 text-white text-[10px] font-black px-3 py-2 rounded-xl hover:bg-blue-700 shadow-md">อนุมัติ</button>` : 
                        `<div class="w-[60px]"></div>`
                    }
                    ${u.role !== 'super_admin' ? 
                        `<button onclick="deleteUser(${u.id})" class="text-red-400 hover:text-red-600 transition-colors p-2"><i data-lucide="trash-2" size="18"></i></button>` : 
                        ''
                    }
                </td>
            </tr>
        `).join('');
        lucide.createIcons();
    }

    async function approveUser(id) {
        if(confirm('ยืนยันอนุมัติบัญชีผู้ใช้เพื่อให้เข้าใช้งานระบบ?')) {
            await fetch(`api/manage.php?action=admin_user_approve&id=${id}`);
            fetchUsers();
        }
    }

    async function deleteUser(id) {
        if(confirm('คุณต้องการลบบัญชีผู้ใช้นี้ออกจากระบบใช่หรือไม่?')) {
            await fetch(`api/manage.php?action=admin_user_delete&id=${id}`);
            fetchUsers();
        }
    }

    fetchUsers();
</script>
</body>
</html>
