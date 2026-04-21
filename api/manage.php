<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';
$school_id = $_SESSION['school_id'] ?? 0;

if (!$school_id && $action !== 'get_school_info') {
    jsonResponse(['error' => 'กรุณาเข้าสู่ระบบ'], 401);
}

// Helper to get raw JSON input
$data = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    // SUBJECTS
    case 'subjects_list':
        $stmt = $pdo->prepare("SELECT * FROM subjects WHERE school_id = ? ORDER BY code ASC");
        $stmt->execute([$school_id]);
        jsonResponse($stmt->fetchAll());
        break;
    case 'subject_add':
        $stmt = $pdo->prepare("INSERT INTO subjects (code, name, hours_per_week, is_double, school_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['code'], $data['name'], $data['hours'], $data['is_double'], $school_id]);
        jsonResponse(['success' => true]);
        break;
    case 'subject_delete':
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ? AND school_id = ?");
        $stmt->execute([$_GET['id'], $school_id]);
        jsonResponse(['success' => true]);
        break;

    // TEACHERS
    case 'teachers_list':
        $stmt = $pdo->prepare("SELECT * FROM teachers WHERE school_id = ? ORDER BY name ASC");
        $stmt->execute([$school_id]);
        jsonResponse($stmt->fetchAll());
        break;
    case 'teacher_add':
        $stmt = $pdo->prepare("INSERT INTO teachers (name, school_id) VALUES (?, ?)");
        $stmt->execute([$data['name'], $school_id]);
        jsonResponse(['success' => true]);
        break;
    case 'teacher_delete':
        $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ? AND school_id = ?");
        $stmt->execute([$_GET['id'], $school_id]);
        jsonResponse(['success' => true]);
        break;

    // ROOMS
    case 'rooms_list':
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE school_id = ? ORDER BY name ASC");
        $stmt->execute([$school_id]);
        jsonResponse($stmt->fetchAll());
        break;
    case 'room_add':
        $stmt = $pdo->prepare("INSERT INTO rooms (name, school_id) VALUES (?, ?)");
        $stmt->execute([$data['name'], $school_id]);
        jsonResponse(['success' => true]);
        break;
    case 'room_delete':
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ? AND school_id = ?");
        $stmt->execute([$_GET['id'], $school_id]);
        jsonResponse(['success' => true]);
        break;

    // CLASSROOMS
    case 'classrooms_list':
        $stmt = $pdo->prepare("SELECT * FROM classrooms WHERE school_id = ?");
        $stmt->execute([$school_id]);
        jsonResponse($stmt->fetchAll());
        break;
    case 'classroom_add':
        $stmt = $pdo->prepare("INSERT INTO classrooms (name, level, school_id) VALUES (?, ?, ?)");
        $stmt->execute([$data['name'], $data['level'], $school_id]);
        jsonResponse(['success' => true]);
        break;
    case 'classroom_delete':
        $stmt = $pdo->prepare("DELETE FROM classrooms WHERE id = ? AND school_id = ?");
        $stmt->execute([$_GET['id'], $school_id]);
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['error' => 'Action not found'], 404);
}
?>
