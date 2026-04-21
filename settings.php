<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<main class="flex-1 p-8 overflow-y-auto">
    <header class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-slate-900">การตั้งค่าระบบ</h2>
            <p class="text-slate-500">กำหนดปีการศึกษา, คาบเรียน และกิจกรรมพิเศษต่างๆ</p>
        </div>
    </header>

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden max-w-5xl">
        <div class="flex border-b bg-slate-50">
            <button onclick="showTab('general')" class="tab-btn active px-6 py-4 font-bold text-sm text-slate-500 border-b-2 border-transparent transition-all hover:text-blue-600">
                ตั้งค่าทั่วไป
            </button>
            <button onclick="showTab('periods')" class="tab-btn px-6 py-4 font-bold text-sm text-slate-500 border-b-2 border-transparent transition-all hover:text-blue-600">
                กำหนดคาบเรียน
            </button>
            <button onclick="showTab('special')" class="tab-btn px-6 py-4 font-bold text-sm text-slate-500 border-b-2 border-transparent transition-all hover:text-blue-600">
                กิจกรรมพิเศษ & พักกลางวัน
            </button>
        </div>

        <!-- General Settings Tab -->
        <div id="tab-general" class="tab-content p-8 space-y-8">
            <div class="grid md:grid-cols-2 gap-8">
                <form id="generalSettingsForm" class="space-y-4 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                    <h3 class="font-bold text-lg mb-4 text-slate-800">ปรับปรุงข้อมูล</h3>
                    <div class="space-y-1">
                        <label class="text-sm font-bold text-slate-700">ปีการศึกษา (พ.ศ.)</label>
                        <input type="number" name="academic_year" class="w-full px-4 py-2 rounded-xl border focus:ring-2 focus:ring-blue-500 outline-none" placeholder="เช่น 2567" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-bold text-slate-700">ภาคเรียนที่</label>
                        <select name="semester" class="w-full px-4 py-2 rounded-xl border focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="1">1</option>
                            <option value="2">2</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg hover:shadow-blue-500/20 flex items-center justify-center gap-2">
                        <i data-lucide="save" size="18"></i> บันทึกการตั้งค่า
                    </button>

                    <?php if(hasRole('super_admin')): ?>
                    <button type="button" onclick="syncDB()" class="w-full bg-amber-50 text-amber-700 border border-amber-100 px-6 py-3 rounded-xl font-bold hover:bg-amber-100 transition-all flex items-center justify-center gap-2 mt-4">
                        <i data-lucide="database-zap" size="18"></i> ตรวจสอบและซิงค์ฐานข้อมูล
                    </button>
                    <?php endif; ?>
                </form>

                <div class="space-y-4">
                    <h3 class="font-bold text-lg text-slate-800">ข้อมูลปัจจุบัน</h3>
                    <div id="settingsSummary" class="bg-blue-50 border border-blue-100 p-6 rounded-2xl space-y-3 shadow-sm">
                        <div class="flex justify-between items-center text-blue-900">
                            <span class="font-medium text-sm">ปีการศึกษา</span>
                            <span id="display-year" class="font-bold text-xl">-</span>
                        </div>
                        <div class="flex justify-between items-center text-blue-900 border-t border-blue-200 pt-3">
                            <span class="font-medium text-sm">ภาคเรียนที่</span>
                            <span id="display-semester" class="font-bold text-xl">-</span>
                        </div>
                        <div id="db-health" class="pt-2 text-[10px] text-blue-400 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                            <span>ซิงค์ข้อมูลล่าสุด: <span id="display-updated">-</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Periods Settings Tab -->
        <div id="tab-periods" class="tab-content hidden p-8 space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">กำหนดเวลาแต่ละคาบ</h3>
                    <p class="text-xs text-slate-400 font-medium">* รูปแบบเวลา 24 ชั่วโมง (เช่น 08:30)</p>
                </div>
                <button onclick="addPeriodRow()" class="text-blue-600 font-bold text-sm flex items-center gap-1 hover:underline">
                    <i data-lucide="plus" size="16"></i> เพิ่มคาบเรียน
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase border-b">
                            <th class="py-2">คาบที่</th>
                            <th class="py-2">เริ่ม (เช่น 08:30)</th>
                            <th class="py-2">ถึง (เช่น 09:20)</th>
                            <th class="py-2 text-right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="periodRows" class="divide-y">
                        <!-- Rows added by JS -->
                    </tbody>
                </table>
            </div>
            <button onclick="savePeriods()" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 transition-all">
                บันทึกเวลาคาบเรียน
            </button>
        </div>

        <!-- Special Periods Tab -->
        <div id="tab-special" class="tab-content hidden p-8 space-y-8">
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Form to Add -->
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 space-y-4">
                    <h4 class="font-bold text-slate-900">เพิ่มกิจกรรมพิเศษ</h4>
                    <form id="specialPeriodForm" class="space-y-4 text-sm">
                        <div class="space-y-1">
                            <label class="font-bold">ชื่อกิจกรรม</label>
                            <select name="event_name" class="w-full px-3 py-2 rounded-lg border">
                                <option value="พักกลางวัน">พักกลางวัน</option>
                                <option value="กิจกรรมชุมนุม">กิจกรรมชุมนุม</option>
                                <option value="ลูกเสือ-เนตรนารี">ลูกเสือ-เนตรนารี</option>
                                <option value="กิจกรรมโฮมรูม">กิจกรรมโฮมรูม</option>
                                <option value="กิจกรรมอื่นๆ">กิจกรรมอื่นๆ</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="font-bold">วัน</label>
                                <select name="day" class="w-full px-3 py-2 rounded-lg border">
                                    <option value="1">จันทร์</option>
                                    <option value="2">อังคาร</option>
                                    <option value="3">พุธ</option>
                                    <option value="4">พฤหัสบดี</option>
                                    <option value="5">ศุกร์</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="font-bold">คาบที่</label>
                                <input type="number" name="period" class="w-full px-3 py-2 rounded-lg border" placeholder="เช่น 4" required>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="font-bold">ระดับชั้นที่บังคับ</label>
                            <div class="bg-white p-3 rounded-lg border max-h-40 overflow-y-auto space-y-2" id="levelCheckboxes">
                                <!-- Checkboxes by JS -->
                                <p class="text-xs text-slate-400">กรุณาเพิ่มข้อมูลชั้นเรียนก่อน</p>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="checkbox" id="selectAllLevels" onchange="toggleSelectAll(this)">
                                <label for="selectAllLevels" class="text-xs text-slate-600 cursor-pointer italic font-bold">เลือกทั้งหมด</label>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-slate-900 text-white p-2 rounded-xl font-bold hover:bg-slate-800 transition-all">
                            เพิ่มลงในรายการ
                        </button>
                    </form>
                </div>

                <!-- List of Special -->
                <div class="space-y-4">
                    <h4 class="font-bold text-slate-900 italic">รายการที่กำหนดไว้</h4>
                    <div id="specialList" class="space-y-3">
                        <!-- List by JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .tab-btn.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
        background: white;
    }
</style>

<script>
    lucide.createIcons();
    
    // TAB MANAGEMENT
    function showTab(id) {
        document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + id).classList.remove('hidden');
        event.currentTarget.classList.add('active');
    }

    // GENERAL SETTINGS
    async function loadGeneralSettings() {
        try {
            const res = await fetch('api/manage.php?action=get_settings');
            const data = await res.json();
            const form = document.getElementById('generalSettingsForm');
            form.academic_year.value = data.academic_year || '';
            form.semester.value = data.semester || '1';
            
            document.getElementById('display-year').innerText = data.academic_year || '-';
            document.getElementById('display-semester').innerText = data.semester || '-';
            document.getElementById('display-updated').innerText = data.updated_at || 'ไม่มีข้อมูล';
        } catch (error) {
            console.error('Failed to load settings', error);
        }
    }

    document.getElementById('generalSettingsForm').onsubmit = async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const year = fd.get('academic_year');
        const semester = fd.get('semester');
        
        try {
            const res = await fetch('api/manage.php?action=save_settings', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ academic_year: year, semester: semester })
            });
            const result = await res.json();
            if(result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'บันทึกสำเร็จ',
                    text: `บันทึกปีการศึกษา ${year} ภาคเรียนที่ ${semester} เรียบร้อยแล้ว`,
                    timer: 2000
                });
                loadGeneralSettings();
            } else {
                Swal.fire('ผิดพลาด', result.error || 'ไม่สามารถบันทึกได้', 'error');
            }
        } catch (error) {
            Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
        }
    };

    // PERIODS MANAGEMENT
    async function loadPeriods() {
        const res = await fetch('api/manage.php?action=periods_list');
        const data = await res.json();
        const container = document.getElementById('periodRows');
        container.innerHTML = '';
        
        if(data.length === 0) {
            // Add some default rows
            for(let i=1; i<=8; i++) addPeriodRow(i, '08:00', '09:00');
        } else {
            data.forEach(p => addPeriodRow(p.period_number, p.start_time, p.end_time));
        }
    }

    function addPeriodRow(num = '', start = '', end = '') {
        const container = document.getElementById('periodRows');
        const rowCount = container.children.length + 1;
        
        // Clean time string from DB (HH:MM:SS -> HH:MM)
        const cleanStart = start ? start.substring(0, 5) : '';
        const cleanEnd = end ? end.substring(0, 5) : '';

        const tr = document.createElement('tr');
        tr.className = 'period-row';
        tr.innerHTML = `
            <td class="py-3"><input type="number" class="p-num w-16 px-2 py-1 border rounded" value="${num || rowCount}"></td>
            <td class="py-3"><input type="text" class="p-start w-24 px-2 py-1 border rounded" value="${cleanStart}" placeholder="08:30" oninput="autoFormatTime(this)" maxlength="5"></td>
            <td class="py-3"><input type="text" class="p-end w-24 px-2 py-1 border rounded" value="${cleanEnd}" placeholder="09:20" oninput="autoFormatTime(this)" maxlength="5"></td>
            <td class="py-3 text-right">
                <button onclick="this.closest('tr').remove()" class="text-slate-300 hover:text-red-500 transition-colors"><i data-lucide="minus-circle" size="18"></i></button>
            </td>
        `;
        container.appendChild(tr);
        lucide.createIcons();
    }

    function autoFormatTime(input) {
        // Remove everything except numbers
        let val = input.value.replace(/\D/g, '');
        if (val.length > 4) val = val.substring(0, 4);
        
        // Add colon
        if (val.length >= 3) {
            val = val.substring(0, 2) + ':' + val.substring(2);
        }
        
        input.value = val;
    }

    async function savePeriods() {
        const rows = document.querySelectorAll('.period-row');
        const items = Array.from(rows).map(row => ({
            period_number: row.querySelector('.p-num').value,
            start_time: row.querySelector('.p-start').value,
            end_time: row.querySelector('.p-end').value
        }));

        try {
            const res = await fetch('api/manage.php?action=period_save', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ items })
            });
            const result = await res.json();
            if(result.success) {
                Swal.fire('สำเร็จ', `บันทึกข้อมูลเวลาคาบเรียนจำนวน ${items.length} คาบเรียบร้อยแล้ว`, 'success');
                loadPeriods();
            } else {
                Swal.fire('ผิดพลาด', result.error || 'ไม่สามารถบันทึกได้', 'error');
            }
        } catch (error) {
            Swal.fire('ผิดพลาด', 'เซิร์ฟเวอร์ขัดข้อง', 'error');
        }
    }

    // SPECIAL PERIODS
    async function loadLevels() {
        const res = await fetch('api/manage.php?action=get_levels');
        const levels = await res.json();
        const container = document.getElementById('levelCheckboxes');
        if (levels.length > 0) {
            container.innerHTML = levels.map(lv => `
                <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-slate-50 p-1 rounded transition-all">
                    <input type="checkbox" name="levels" value="${lv}" class="level-cb">
                    <span>${lv}</span>
                </label>
            `).join('');
        }
    }

    function toggleSelectAll(cb) {
        document.querySelectorAll('.level-cb').forEach(el => el.checked = cb.checked);
    }

    async function loadSpecialPeriods() {
        const res = await fetch('api/manage.php?action=special_periods_list');
        const data = await res.json();
        const container = document.getElementById('specialList');
        const days = ['', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'];
        
        container.innerHTML = data.map(item => `
            <div class="flex items-center justify-between bg-white p-4 rounded-xl border border-slate-100 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                        <i data-lucide="${item.event_name === 'พักกลางวัน' ? 'utensils' : 'star'}" size="20"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-900">${item.event_name}</div>
                        <div class="text-xs text-slate-500">วัน${days[item.day]} • คาบที่ ${item.period} ${item.applies_to_level ? ' • สำหรับ ' + item.applies_to_level : ' • ทุกระดับชั้น'}</div>
                    </div>
                </div>
                <button onclick="deleteSpecial(${item.id})" class="text-slate-300 hover:text-red-500 transition-colors"><i data-lucide="x-circle" size="20"></i></button>
            </div>
        `).join('');
        lucide.createIcons();
    }

    document.getElementById('specialPeriodForm').onsubmit = async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const event_name = fd.get('event_name');
        const day = fd.get('day');
        const period = fd.get('period');
        
        const selectedLevels = Array.from(document.querySelectorAll('.level-cb:checked')).map(cb => cb.value);
        const levelStr = selectedLevels.length === 0 ? '' : selectedLevels.join(',');

        try {
            const res = await fetch('api/manage.php?action=special_period_add', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    event_name: event_name,
                    day: day,
                    period: period,
                    applies_to_level: levelStr
                })
            });
            const result = await res.json();
            if(result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'บันทึกกิจกรรมสำเร็จ',
                    text: `เพิ่มกิจกรรม "${event_name}" ในวัน ${['', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์'][day]} คาบที่ ${period} เรียบร้อยแล้ว`,
                    timer: 2000
                });
                e.target.reset();
                document.getElementById('selectAllLevels').checked = false;
                loadSpecialPeriods();
            } else {
                Swal.fire('ผิดพลาด', result.error || 'ไม่สามารถบันทึกได้', 'error');
            }
        } catch (error) {
            Swal.fire('ผิดพลาด', 'เซิร์ฟเวอร์ขัดข้อง', 'error');
        }
    };

    async function deleteSpecial(id) {
        if(confirm('ลบกิจกรรมที่เลือก?')) {
            await fetch('api/manage.php?action=special_period_delete&id=' + id);
            loadSpecialPeriods();
        }
    }

    async function syncDB() {
        Swal.fire({
            title: 'กำลังซิงค์ฐานข้อมูล...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
        const res = await fetch('api/manage.php?action=system_db_update');
        const result = await res.json();
        if (result.success) {
            Swal.fire('สำเร็จ', result.message, 'success');
        } else {
            Swal.fire('ผิดพลาด', result.error, 'error');
        }
    }
    loadGeneralSettings();
    loadPeriods();
    loadLevels();
    loadSpecialPeriods();
</script>
</body>
</html>
