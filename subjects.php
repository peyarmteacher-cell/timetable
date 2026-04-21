<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<main class="flex-1 p-8 overflow-y-auto">
    <header class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-slate-900">จัดการรายวิชา</h2>
            <p class="text-slate-500">เพิ่ม แก้ไข และลบข้อมูลรายวิชาของโรงเรียน</p>
        </div>
        <div class="flex gap-3">
            <button onclick="downloadTemplate('subjects')" class="bg-white border text-slate-700 px-4 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-slate-50 transition-all text-sm">
                <i data-lucide="download" size="18"></i> เทมเพลต Excel
            </button>
            <input type="file" id="excelInput" class="hidden" accept=".xlsx, .xls, .csv" onchange="handleExcelImport(event, 'subjects')">
            <button onclick="document.getElementById('excelInput').click()" class="bg-emerald-50 text-emerald-700 border border-emerald-100 px-4 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-emerald-100 transition-all text-sm">
                <i data-lucide="file-up" size="18"></i> นำเข้า Excel
            </button>
            <button onclick="openModal()" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg hover:shadow-blue-500/20">
                <i data-lucide="plus" size="18"></i> เพิ่มรายวิชา
            </button>
        </div>
    </header>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b">
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs">รหัสวิชา</th>
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs">ชื่อวิชา</th>
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs text-center">ชม./สัปดาห์</th>
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs text-center">คาบคู่</th>
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs text-right">จัดการ</th>
                </tr>
            </thead>
            <tbody id="subjectList" class="divide-y text-slate-700">
                <!-- Data will push here -->
            </tbody>
        </table>
    </div>

    <!-- Add/Edit Modal -->
    <div id="subjectModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="p-6 border-b flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-bold text-slate-900" id="modalTitle">เพิ่มรายวิชา</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i data-lucide="x"></i></button>
            </div>
            <form id="subjectForm" class="p-6 space-y-4">
                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-700">รหัสรายวิชา</label>
                    <input type="text" name="code" class="w-full px-4 py-2 rounded-xl border focus:ring-2 focus:ring-blue-500 outline-none" placeholder="เช่น ท11101" required>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-700">ชื่อรายวิชาเต็ม</label>
                    <input type="text" name="name" class="w-full px-4 py-2 rounded-xl border focus:ring-2 focus:ring-blue-500 outline-none" placeholder="เช่น ภาษาไทย" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-bold text-slate-700">ชั่วโมงต่อสัปดาห์</label>
                        <input type="number" name="hours" class="w-full px-4 py-2 rounded-xl border focus:ring-2 focus:ring-blue-500 outline-none" value="1" min="1" required>
                    </div>
                    <div class="flex items-center gap-3 pt-6">
                        <input type="checkbox" id="is_double" name="is_double" class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <label for="is_double" class="text-sm font-bold text-slate-700 cursor-pointer">มีคาบคู่</label>
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
    
    async function fetchSubjects() {
        const res = await fetch('api/manage.php?action=subjects_list');
        const data = await res.json();
        const list = document.getElementById('subjectList');
        list.innerHTML = data.map(item => `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4 font-bold text-blue-600">${item.code}</td>
                <td class="p-4 font-medium">${item.name}</td>
                <td class="p-4 text-center">${item.hours_per_week}</td>
                <td class="p-4 text-center">
                    ${item.is_double ? '<span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold rounded-full">คาบคู่</span>' : '-'}
                </td>
                <td class="p-4 text-right">
                    <button onclick="deleteSubject(${item.id})" class="text-red-400 hover:text-red-600 transition-colors p-2"><i data-lucide="trash-2" size="18"></i></button>
                </td>
            </tr>
        `).join('');
        lucide.createIcons();
    }

    function openModal() {
        document.getElementById('subjectModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('subjectModal').children[0].classList.add('scale-100'), 10);
    }

    function closeModal() {
        document.getElementById('subjectModal').classList.add('hidden');
        document.getElementById('subjectForm').reset();
    }

    document.getElementById('subjectForm').onsubmit = async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const data = {
            code: fd.get('code'),
            name: fd.get('name'),
            hours: fd.get('hours'),
            is_double: e.target.is_double.checked ? 1 : 0
        };
        
        try {
            const res = await fetch('api/manage.php?action=subject_add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            if (result.success) {
                closeModal();
                fetchSubjects();
            } else {
                alert('ไม่สามารถบันทึกได้: ' + (result.error || 'เกิดข้อผิดพลาด'));
            }
        } catch (error) {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        }
    };

    async function deleteSubject(id) {
        if (confirm('คุณต้องการลบรายวิชานี้ใช่หรือไม่?')) {
            await fetch(`api/manage.php?action=subject_delete&id=${id}`);
            fetchSubjects();
        }
    }

    // Excel Utilities
    function downloadTemplate(type) {
        let headers = [];
        let filename = "";
        if (type === 'subjects') {
            headers = [["รหัสวิชา", "ชื่อวิชา", "ชั่วโมงต่อสัปดาห์", "คาบคู่(1=มี,0=ไม่มี)"]];
            filename = "template_subjects.xlsx";
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
                if (type === 'subjects') {
                    return {
                        code: row["รหัสวิชา"],
                        name: row["ชื่อวิชา"],
                        hours: row["ชั่วโมงต่อสัปดาห์"],
                        is_double: parseInt(row["คาบคู่(1=มี,0=ไม่มี)"]) === 1
                    };
                }
            });

            if (items.length > 0) {
                const res = await fetch(`api/manage.php?action=bulk_import&type=${type}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items })
                });
                const result = await res.json();
                if (result.success) {
                    alert(`นำเข้าสำเร็จ ${result.count} รายการ`);
                    fetchSubjects();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + result.error);
                }
            }
            event.target.value = ""; // Reset
        };
        reader.readAsArrayBuffer(file);
    }

    fetchSubjects();
</script>
</body>
</html>
