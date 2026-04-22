<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<main class="flex-1 p-8 overflow-y-auto">
    <header class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-slate-900">จัดการข้อมูลครู</h2>
            <p class="text-slate-500">เพิ่ม แก้ไข และลบรายชื่อบุคลากรครูในโรงเรียน</p>
        </div>
        <div class="flex gap-3">
            <button onclick="downloadTemplate('teachers')" class="bg-white border text-slate-700 px-4 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-slate-50 transition-all text-sm">
                <i data-lucide="download" size="18"></i> เทมเพลต Excel
            </button>
            <input type="file" id="excelInput" class="hidden" accept=".xlsx, .xls, .csv" onchange="handleExcelImport(event, 'teachers')">
            <button onclick="document.getElementById('excelInput').click()" class="bg-emerald-50 text-emerald-700 border border-emerald-100 px-4 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-emerald-100 transition-all text-sm">
                <i data-lucide="file-up" size="18"></i> นำเข้า Excel
            </button>
            <button onclick="openModal()" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20">
                <i data-lucide="user-plus" size="18"></i> เพิ่มชื่อครู
            </button>
        </div>
    </header>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden max-w-4xl">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b">
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs">ลำดับ</th>
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs">ชื่อ-นามสกุล</th>
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs">ตำแหน่ง</th>
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs text-right">จัดการ</th>
                </tr>
            </thead>
            <tbody id="teacherList" class="divide-y text-slate-700"></tbody>
        </table>
    </div>

    <!-- Add Modal -->
    <div id="teacherModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="p-6 border-b flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-bold text-slate-900">เพิ่มชื่อผู้สอน</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i data-lucide="x"></i></button>
            </div>
            <form id="teacherForm" class="p-6 space-y-4">
                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-700">ชื่อ-นามสกุล</label>
                    <input type="text" name="name" class="w-full px-4 py-2 rounded-xl border focus:ring-2 focus:ring-blue-500 outline-none" placeholder="เช่น นายสมชาย สายเสมอ" required>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-700">ตำแหน่ง</label>
                    <select name="position" class="w-full px-4 py-2 rounded-xl border focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="ครูอัตราจ้าง">ครูอัตราจ้าง</option>
                        <option value="พนักงานราชการครู">พนักงานราชการครู</option>
                        <option value="ครู">ครู</option>
                        <option value="ครูชำนาญการ">ครูชำนาญการ</option>
                        <option value="ครูชำนาญการพิเศษ">ครูชำนาญการพิเศษ</option>
                        <option value="ครูเชี่ยวชาญ">ครูเชี่ยวชาญ</option>
                        <option value="ครูเชี่ยวชาญพิเศษ">ครูเชี่ยวชาญพิเศษ</option>
                    </select>
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
    async function fetchTeachers() {
        const res = await fetch('api/manage.php?action=teachers_list');
        const data = await res.json();
        const list = document.getElementById('teacherList');
        list.innerHTML = data.map((item, index) => `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4 text-slate-400 font-mono text-sm">${index + 1}</td>
                <td class="p-4 font-bold text-slate-900">${item.name}</td>
                <td class="p-4 font-medium text-slate-500">${item.position || '-'}</td>
                <td class="p-4 text-right">
                    <button onclick="deleteTeacher(${item.id})" class="text-red-400 hover:text-red-600 transition-colors p-2"><i data-lucide="trash-2" size="18"></i></button>
                </td>
            </tr>
        `).join('');
        lucide.createIcons();
    }
    function openModal() { document.getElementById('teacherModal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('teacherModal').classList.add('hidden'); document.getElementById('teacherForm').reset(); }
    document.getElementById('teacherForm').onsubmit = async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        try {
            const res = await fetch('api/manage.php?action=teacher_add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    name: fd.get('name'),
                    position: fd.get('position')
                })
            });
            const result = await res.json();
            if (result.success) {
                closeModal();
                fetchTeachers();
            } else {
                Swal.fire('ไม่สามารถบันทึกได้', result.error || 'เกิดข้อผิดพลาด', 'error');
            }
        } catch (error) {
            Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ หรือข้อมูลที่ส่งกลับมาไม่ถูกต้อง', 'error');
            console.error(error);
        }
    };
    async function deleteTeacher(id) {
        if (confirm('ยืนยันการลบรายชื่อครู?')) {
            await fetch(`api/manage.php?action=teacher_delete&id=${id}`);
            fetchTeachers();
        }
    }

    // Excel Utilities
    function downloadTemplate(type) {
        let headers = [];
        let filename = "";
        if (type === 'teachers') {
            headers = [["ชื่อ-นามสกุล", "ตำแหน่ง"]];
            filename = "template_teachers.xlsx";
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

            const items = jsonData.map(row => {
                if (type === 'teachers') {
                    const name = row["ชื่อ-นามสกุล"] || row["ชื่อ"] || row["Name"] || "";
                    if (!name) return null;
                    return {
                        name: name,
                        position: row["ตำแหน่ง"] || row["Position"] || ""
                    };
                }
                return null;
            }).filter(item => item !== null);

            if (items.length > 0) {
                const res = await fetch(`api/manage.php?action=bulk_import&type=${type}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items })
                });
                const result = await res.json();
                if (result.success) {
                    alert(`นำเข้าสำเร็จ ${result.count} รายการ`);
                    fetchTeachers();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + result.error);
                }
            }
            event.target.value = ""; 
        };
        reader.readAsArrayBuffer(file);
    }

    fetchTeachers();
</script>
</body>
</html>
