<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<main class="flex-1 p-8 overflow-y-auto">
    <header class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-slate-900">จัดการห้องเรียน/ห้องปฏิบัติการ</h2>
            <p class="text-slate-500">ข้อมูลสถานที่ใช้จัดกิจกรรมการเรียนการสอน</p>
        </div>
        <div class="flex gap-3">
            <button onclick="downloadTemplate('rooms')" class="bg-white border text-slate-700 px-4 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-slate-50 transition-all text-sm">
                <i data-lucide="download" size="18"></i> เทมเพลต Excel
            </button>
            <input type="file" id="excelInput" class="hidden" accept=".xlsx, .xls, .csv" onchange="handleExcelImport(event, 'rooms')">
            <button onclick="document.getElementById('excelInput').click()" class="bg-emerald-50 text-emerald-700 border border-emerald-100 px-4 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-emerald-100 transition-all text-sm">
                <i data-lucide="file-up" size="18"></i> นำเข้า Excel
            </button>
            <button onclick="openModal()" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg hover:shadow-blue-500/20">
                <i data-lucide="door-open" size="18"></i> เพิ่มห้อง
            </button>
        </div>
    </header>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden max-w-4xl">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b">
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs">ลำดับ</th>
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs">ชื่อห้อง / อาคาร</th>
                    <th class="p-4 font-bold text-slate-400 uppercase text-xs text-right">จัดการ</th>
                </tr>
            </thead>
            <tbody id="roomList" class="divide-y text-slate-700"></tbody>
        </table>
    </div>

    <!-- Add Modal -->
    <div id="roomModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="p-6 border-b flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-bold text-slate-900">เพิ่มห้องเรียน</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i data-lucide="x"></i></button>
            </div>
            <form id="roomForm" class="p-6 space-y-4">
                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-700">ชื่อห้อง (หรือเลขที่ห้อง)</label>
                    <input type="text" name="name" class="w-full px-4 py-2 rounded-xl border focus:ring-2 focus:ring-blue-500 outline-none" placeholder="เช่น 101, ห้องคอมพิวเตอร์ 1" required>
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
    async function fetchRooms() {
        const res = await fetch('api/manage.php?action=rooms_list');
        const data = await res.json();
        const list = document.getElementById('roomList');
        list.innerHTML = data.map((item, index) => `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="p-4 text-slate-400 font-mono text-sm">${index + 1}</td>
                <td class="p-4 font-bold text-slate-900">${item.name}</td>
                <td class="p-4 text-right">
                    <button onclick="deleteRoom(${item.id})" class="text-red-400 hover:text-red-600 transition-colors p-2"><i data-lucide="trash-2" size="18"></i></button>
                </td>
            </tr>
        `).join('');
        lucide.createIcons();
    }
    function openModal() { document.getElementById('roomModal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('roomModal').classList.add('hidden'); document.getElementById('roomForm').reset(); }
    document.getElementById('roomForm').onsubmit = async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        try {
            const res = await fetch('api/manage.php?action=room_add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: fd.get('name') })
            });
            const result = await res.json();
            if (result.success) {
                closeModal();
                fetchRooms();
            } else {
                alert('ไม่สามารถบันทึกได้: ' + (result.error || 'เกิดข้อผิดพลาด'));
            }
        } catch (error) {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        }
    };
    async function deleteRoom(id) {
        if (confirm('ยืนยันการลบห้อง?')) {
            await fetch(`api/manage.php?action=room_delete&id=${id}`);
            fetchRooms();
        }
    }

    // Excel Utilities
    function downloadTemplate(type) {
        let headers = [];
        let filename = "";
        if (type === 'rooms') {
            headers = [["ชื่อห้อง"]];
            filename = "template_rooms.xlsx";
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
                const name = row["ชื่อห้อง"] || row["Name"] || row["Room"] || "";
                if (!name) return null;
                return { name };
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
                    fetchRooms();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + result.error);
                }
            }
            event.target.value = ""; 
        };
        reader.readAsArrayBuffer(file);
    }

    fetchRooms();
</script>
</body>
</html>
