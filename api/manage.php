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
        $stmt = $pdo->prepare("INSERT INTO teachers (name, position, school_id) VALUES (?, ?, ?)");
        $stmt->execute([$data['name'], $data['position'] ?? '', $school_id]);
        jsonResponse(['success' => true]);
        break;

    // BULK IMPORTS
    case 'bulk_import':
        $type = $_GET['type'] ?? '';
        $items = $data['items'] ?? [];
        if (empty($items)) jsonResponse(['error' => 'No items found'], 400);

        $pdo->beginTransaction();
        try {
            if ($type === 'subjects') {
                $stmt = $pdo->prepare("INSERT INTO subjects (code, name, hours_per_week, is_double, school_id) VALUES (?, ?, ?, ?, ?)");
                foreach ($items as $item) {
                    $stmt->execute([$item['code'], $item['name'], $item['hours'], $item['is_double'] ? 1 : 0, $school_id]);
                }
            } else if ($type === 'teachers') {
                $stmt = $pdo->prepare("INSERT INTO teachers (name, position, school_id) VALUES (?, ?, ?)");
                foreach ($items as $item) {
                    $stmt->execute([$item['name'], $item['position'] ?? '', $school_id]);
                }
            } else if ($type === 'rooms') {
                $stmt = $pdo->prepare("INSERT INTO rooms (name, school_id) VALUES (?, ?)");
                foreach ($items as $item) {
                    $stmt->execute([$item['name'], $school_id]);
                }
            } else if ($type === 'classrooms') {
                $stmt = $pdo->prepare("INSERT INTO classrooms (name, level, school_id) VALUES (?, ?, ?)");
                foreach ($items as $item) {
                    $stmt->execute([$item['name'], $item['level'], $school_id]);
                }
            }
            $pdo->commit();
            jsonResponse(['success' => true, 'count' => count($items)]);
        } catch (Exception $e) {
            $pdo->rollBack();
            jsonResponse(['error' => $e->getMessage()], 500);
        }
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

    // DASHBOARD STATS
    case 'get_stats':
        $stmt_sub = $pdo->prepare("SELECT COUNT(*) as total FROM subjects WHERE school_id = ?");
        $stmt_sub->execute([$school_id]);
        $stmt_tea = $pdo->prepare("SELECT COUNT(*) as total FROM teachers WHERE school_id = ?");
        $stmt_tea->execute([$school_id]);
        $stmt_room = $pdo->prepare("SELECT COUNT(*) as total FROM rooms WHERE school_id = ?");
        $stmt_room->execute([$school_id]);
        $stmt_cls = $pdo->prepare("SELECT COUNT(*) as total FROM classrooms WHERE school_id = ?");
        $stmt_cls->execute([$school_id]);
        
        jsonResponse([
            'subjects' => $stmt_sub->fetch()['total'],
            'teachers' => $stmt_tea->fetch()['total'],
            'rooms' => $stmt_room->fetch()['total'],
            'classrooms' => $stmt_cls->fetch()['total']
        ]);
        break;

    // SUPER ADMIN - SCHOOLS
    case 'admin_schools_list':
        if (!hasRole('super_admin')) jsonResponse(['error' => 'Forbidden'], 403);
        $stmt = $pdo->prepare("SELECT * FROM schools ORDER BY created_at DESC");
        $stmt->execute();
        jsonResponse($stmt->fetchAll());
        break;
    case 'admin_school_approve':
        if (!hasRole('super_admin')) jsonResponse(['error' => 'Forbidden'], 403);
        $stmt = $pdo->prepare("UPDATE schools SET is_approved = 1 WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['error' => 'Action not found'], 404);
}
?>
