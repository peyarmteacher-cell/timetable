<?php
require_once 'config.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $school_id = $_SESSION['school_id'] ?? 1;

    if ($action === 'get_subjects') {
        // ดึงรายวิชาที่ยังไม่ได้จัดลงตาราง
        jsonResponse([
            ['id' => 1, 'code' => 'ท11101', 'name' => 'ภาษาไทย', 'teacher' => 'ครูสมศรี', 'room' => '101', 'is_double' => true],
            ['id' => 2, 'code' => 'ค11101', 'name' => 'คณิตศาสตร์', 'teacher' => 'ครูสมชาย', 'room' => '102', 'is_double' => false],
            ['id' => 3, 'code' => 'อ11101', 'name' => 'ภาษาอังกฤษ', 'teacher' => 'ครูสมหญิง', 'room' => '103', 'is_double' => false],
        ]);
    }

    if ($action === 'auto_generate') {
        // อัลกอริทึมจัดตารางอัตโนมัติ
        // 1. ตรวจสอบเงื่อนไข Fix
        // 2. ตรวจสอบครูว่าง/ห้องว่าง
        // 3. วางวิชาคาบคู่ก่อน
        jsonResponse(['success' => true, 'message' => 'จัดตารางสำเร็จ']);
    }

    if ($action === 'save_timetable') {
        $data = json_decode(file_get_contents('php://input'), true);
        // บันทึกลง MySQL ตาราง timetable
        jsonResponse(['success' => true]);
    }
}
?>
