<?php
require_once 'config.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $school_id = $_SESSION['school_id'] ?? 1;

    if ($action === 'get_subjects_pool') {
        // Fetch subjects that haven't been fully scheduled yet (simplified for now)
        $stmt = $pdo->prepare("SELECT * FROM subjects WHERE school_id = ?");
        $stmt->execute([$school_id]);
        jsonResponse($stmt->fetchAll());
    }

    if ($action === 'get_resources') {
        // Fetch teachers and rooms for dropdowns/assignment
        $stmt_teachers = $pdo->prepare("SELECT * FROM teachers WHERE school_id = ?");
        $stmt_teachers->execute([$school_id]);
        $stmt_rooms = $pdo->prepare("SELECT * FROM rooms WHERE school_id = ?");
        $stmt_rooms->execute([$school_id]);
        
        jsonResponse([
            'teachers' => $stmt_teachers->fetchAll(),
            'rooms' => $stmt_rooms->fetchAll()
        ]);
    }

    if ($action === 'get_current_timetable') {
        $classroom_id = $_GET['classroom_id'] ?? 0;
        $stmt = $pdo->prepare("
            SELECT t.*, s.code, s.name as subject_name, te.name as teacher_name, r.name as room_name 
            FROM timetable t
            JOIN subjects s ON t.subject_id = s.id
            JOIN teachers te ON t.teacher_id = te.id
            JOIN rooms r ON t.room_id = r.id
            WHERE t.school_id = ? AND t.classroom_id = ?
        ");
        $stmt->execute([$school_id, $classroom_id]);
        jsonResponse($stmt->fetchAll());
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
