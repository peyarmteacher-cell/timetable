<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<main class="flex-1 p-8 overflow-y-auto">
    <header class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-slate-900">จัดการข้อมูลชั้นเรียน</h2>
            <p class="text-slate-500">กำหนดกลุ่มมนักเรียนและระดับชั้น (เช่น ป.1/1)</p>
        </div>
        <div class="flex gap-3">
            <button onclick="downloadTemplate('classrooms')" class="bg-white border text-slate-700 px-4 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-slate-50 transition-all text-sm">
                <i data-lucide="download" size="18"></i> เทมเพลต Excel
            </button>
            <input type="file" id="excelInput" class="hidden" accept=".xlsx, .xls, .csv" onchange="handleExcelImport(event, 'classrooms')">
            <button onclick="document.getElementById('excelInput').click()" class="bg-emerald-50 text-emerald-700 border border-emerald-100 px-4 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-emerald-100 transition-all text-sm">
                <i data-lucide="file-up" size="18"></i> นำเข้า Excel
            </button>
            <button onclick="openModal()" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg hover:shadow-blue-500/20">
                <i data-lucide="graduation-cap" size="18"></i> เพิ่มชั้นเรียน
            </button>
        </div>
    </header>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden max-w-4xl">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b">
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs">ระดับชั้น</th>
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs">ห้อง/กลุ่ม</th>
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs text-right">จัดการ</th>
                </tr>
            </thead>
            <tbody id="classroomList" class="divide-y text-slate-700"></tbody>
        </table>
    </div>

    <!-- Add Modal -->
    <div id="classroomModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="p-6 border-b flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-bold text-slate-900">เพิ่มชั้นเรียนใหม่</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i data-lucide="x"></i></button>
            </div>
            <form id="classroomForm" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-bold text-slate-700">ระดับชั้น (Level)</label>
                        <select name="level" class="w-full px-4 py-2 rounded-xl border focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="ป.1">ประถมศึกษาปีที่ 1</option>
                            <option value="ป.2">ประถมศึกษาปีที่ 2</option>
                            <option value="ป.3">ประถมศึกษาปีที่ 3</option>
                            <option value="ป.4">ประถมศึกษาปีที่ 4</option>
                            <option value="ป.5">ประถมศึกษาปีที่ 5</option>
                            <option value="ป.6">ประถมศึกษาปีที่ 6</option>
                            <option value="ม.1">มัธยมศึกษาปีที่ 1</option>
                            <option value="ม.2">มัธยมศึกษาปีที่ 2</option>
                            <option value="ม.3">มัธยมศึกษาปีที่ 3</option>
                            <option value="ม.4">มัธยมศึกษาปีที่ 4</option>
                            <option value="ม.5">มัธยมศึกษาปีที่ 5</option>
                            <option value="ม.6">มัธยมศึกษาปีที่ 6</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-bold text-slate-700">เลขห้อง/ทับ</label>
                        <input type="text" name="name" class="w-full px-4 py-2 rounded-xl border focus:ring-2 focus:ring-blue-500 outline-none" placeholder="เช่น 1, 2, ก" required>
                    </div>
                </div>
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2 rounded-xl border font-bold text-slate-600 hover:bg-slate-50 transition-all">ยกเลิก</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-all shadow-lg hover:shadow-blue-500/20">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    lucide.createIcons();
    async function fetchClassrooms() {
        const res = await fetch('api/manage.php?action=classrooms_list');
        const data = await res.json();
        const list = document.getElementById('classroomList');
        list.innerHTML = data.map(item => `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4 font-bold text-blue-600">${item.level}</td>
                <td class="p-4 font-bold text-slate-900">${item.name}</td>
                <td class="p-4 text-right">
                    <button onclick="deleteClassroom(${item.id})" class="text-red-400 hover:text-red-600 transition-colors p-2"><i data-lucide="trash-2" size="18"></i></button>
                </td>
            </tr>
        `).join('');
        lucide.createIcons();
    }
    function openModal() { document.getElementById('classroomModal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('classroomModal').classList.add('hidden'); document.getElementById('classroomForm').reset(); }
    document.getElementById('classroomForm').onsubmit = async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        await fetch('api/manage.php?action=classroom_add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: fd.get('name'), level: fd.get('level') })
        });
        closeModal();
        fetchClassrooms();
    };
    async function deleteClassroom(id) {
        if (confirm('ยืนยันการลบชั้นเรียน? การลบชั้นเรียนอาจจะส่งผลต่อตารางที่จัดไว้แล้ว')) {
            await fetch(`api/manage.php?action=classroom_delete&id=${id}`);
            fetchClassrooms();
        }
    }

    // Excel Utilities
    function downloadTemplate(type) {
        let headers = [];
        let filename = "";
        if (type === 'classrooms') {
            headers = [["ระดับชั้น", "เลขห้อง/ทับ"]];
            filename = "template_classrooms.xlsx";
        }
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(headers);
        XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
        XLSX.writeFile(wb, filename);
    }

    function handleExcelImport(event, type) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = async (e) => {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
            const jsonData = XLSX.utils.sheet_to_json(firstSheet);

            const items = jsonData.map(row => ({ 
                level: row["ระดับชั้น"],
                name: row["เลขห้อง/ทับ"] 
            }));

            if (items.length > 0) {
                const res = await fetch(`api/manage.php?action=bulk_import&type=${type}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items })
                });
                const result = await res.json();
                if (result.success) {
                    alert(`นำเข้าสำเร็จ ${result.count} รายการ`);
                    fetchClassrooms();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + result.error);
                }
            }
            event.target.value = ""; 
        };
        reader.readAsArrayBuffer(file);
    }

    fetchClassrooms();
</script>
</body>
</html>
