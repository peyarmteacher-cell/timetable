<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;
$school_id = $_SESSION['school_id'] ?? 0;
$role = $_SESSION['role'] ?? '';

if (!$user_id) {
    jsonResponse(['error' => 'กรุณาเข้าสู่ระบบ'], 401);
}

// Helper to get raw JSON input
$data = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        // SUBJECTS
        case 'subjects_list':
            try {
                $stmt = $pdo->prepare("SELECT * FROM subjects WHERE school_id = ? ORDER BY level, code ASC");
                $stmt->execute([$school_id]);
                jsonResponse($stmt->fetchAll());
            } catch (PDOException $e) {
                if ($e->getCode() == '42S22') { // Column level missing
                    try {
                        $pdo->exec("ALTER TABLE subjects ADD `level` VARCHAR(20) DEFAULT NULL AFTER `is_double` ");
                    } catch (Exception $err) {}
                    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE school_id = ? ORDER BY code ASC");
                    $stmt->execute([$school_id]);
                    jsonResponse($stmt->fetchAll());
                } else { throw $e; }
            }
            break;
        case 'subject_add':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            $stmt = $pdo->prepare("INSERT INTO subjects (code, name, level, hours_per_week, is_double, school_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['code'], $data['name'], $data['level'] ?? '', $data['hours'], $data['is_double'], $school_id]);
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
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            try {
                $stmt = $pdo->prepare("INSERT INTO teachers (name, position, school_id) VALUES (?, ?, ?)");
                $stmt->execute([$data['name'], $data['position'] ?? '', $school_id]);
                jsonResponse(['success' => true]);
            } catch (PDOException $e) {
                // Check if it's the missing column error
                if ($e->getCode() == '42S22') {
                    // Try to fix it on the fly
                    $pdo->exec("ALTER TABLE teachers ADD `position` VARCHAR(255) DEFAULT NULL AFTER `name` ");
                    // Retry
                    $stmt = $pdo->prepare("INSERT INTO teachers (name, position, school_id) VALUES (?, ?, ?)");
                    $stmt->execute([$data['name'], $data['position'] ?? '', $school_id]);
                    jsonResponse(['success' => true]);
                }
                throw $e;
            }
            break;

        // BULK IMPORTS
        case 'bulk_import':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            $type = $_GET['type'] ?? '';
            $items = $data['items'] ?? [];
            if (empty($items)) jsonResponse(['error' => 'No items found'], 400);

            // 1. Column Check (Pre-Transaction to avoid implicit commit)
            if ($type === 'subjects') {
                $check = $pdo->query("SHOW COLUMNS FROM subjects LIKE 'level'")->fetch();
                if (!$check) {
                    $pdo->exec("ALTER TABLE subjects ADD `level` VARCHAR(20) DEFAULT NULL AFTER `is_double` ");
                }
            } else if ($type === 'teachers') {
                $check = $pdo->query("SHOW COLUMNS FROM teachers LIKE 'position'")->fetch();
                if (!$check) {
                    $pdo->exec("ALTER TABLE teachers ADD `position` VARCHAR(255) DEFAULT NULL AFTER `name` ");
                }
            }

            // 2. Start Transaction
            $pdo->beginTransaction();
            try {
                if ($type === 'subjects') {
                    $stmt = $pdo->prepare("INSERT INTO subjects (code, name, level, hours_per_week, is_double, school_id) VALUES (?, ?, ?, ?, ?, ?)");
                    foreach ($items as $item) {
                        $stmt->execute([$item['code'], $item['name'], $item['level'] ?? '', $item['hours'], $item['is_double'] ? 1 : 0, $school_id]);
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
                if ($pdo->inTransaction()) $pdo->rollBack();
                jsonResponse(['error' => 'Import Error: ' . $e->getMessage()], 500);
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
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
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
        case 'get_levels':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            $stmt = $pdo->prepare("SELECT DISTINCT level FROM classrooms WHERE school_id = ? ORDER BY level ASC");
            $stmt->execute([$school_id]);
            jsonResponse($stmt->fetchAll(PDO::FETCH_COLUMN));
            break;
        case 'classroom_add':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
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
            if ($role === 'super_admin') {
                $stmt_sub = $pdo->query("SELECT COUNT(*) as total FROM subjects");
                $stmt_tea = $pdo->query("SELECT COUNT(*) as total FROM teachers");
                $stmt_sch = $pdo->query("SELECT COUNT(*) as total FROM schools");
                $stmt_usr = $pdo->query("SELECT COUNT(*) as total FROM users");
                
                jsonResponse([
                    'subjects' => $stmt_sub->fetch()['total'],
                    'teachers' => $stmt_tea->fetch()['total'],
                    'schools' => $stmt_sch->fetch()['total'],
                    'users' => $stmt_usr->fetch()['total']
                ]);
            } else {
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
            }
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
        case 'admin_school_toggle_status':
            if (!hasRole('super_admin')) jsonResponse(['error' => 'Forbidden'], 403);
            $stmt = $pdo->prepare("UPDATE schools SET is_approved = 1 - is_approved WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            jsonResponse(['success' => true]);
            break;
        case 'admin_school_delete':
            if (!hasRole('super_admin')) jsonResponse(['error' => 'Forbidden'], 403);
            $pdo->beginTransaction();
            try {
                $id = $_GET['id'];
                $stmt = $pdo->prepare("DELETE FROM schools WHERE id = ?");
                $stmt->execute([$id]);
                $pdo->commit();
                jsonResponse(['success' => true]);
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                jsonResponse(['error' => 'Database Error: ' . $e->getMessage()], 500);
            }
            break;

        // SUPER ADMIN - USERS
        case 'admin_users_list':
            if (!hasRole('super_admin')) jsonResponse(['error' => 'Forbidden'], 403);
            $stmt = $pdo->prepare("SELECT u.*, s.name as school_name FROM users u LEFT JOIN schools s ON u.school_id = s.id ORDER BY u.id DESC");
            $stmt->execute();
            jsonResponse($stmt->fetchAll());
            break;
        case 'admin_user_approve':
            if (!hasRole('super_admin')) jsonResponse(['error' => 'Forbidden'], 403);
            $stmt = $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            jsonResponse(['success' => true]);
            break;
        case 'admin_user_delete':
            if (!hasRole('super_admin')) jsonResponse(['error' => 'Forbidden'], 403);
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'super_admin'");
            $stmt->execute([$_GET['id']]);
            jsonResponse(['success' => true]);
            break;

        // SYSTEM SYNC
        case 'system_sync':
            try {
                // Check connection and basic structure
                $stmt = $pdo->query("SELECT CURRENT_TIMESTAMP");
                $time = $stmt->fetchColumn();
                jsonResponse(['success' => true, 'timestamp' => $time, 'db' => $db]);
            } catch (Exception $e) {
                jsonResponse(['error' => $e->getMessage()], 500);
            }
            break;

        case 'system_db_update':
            if (!hasRole('super_admin')) jsonResponse(['error' => 'Forbidden'], 403);
            try {
                $sqlFile = dirname(__DIR__) . '/database.sql';
                if (!file_exists($sqlFile)) jsonResponse(['error' => 'SQL file not found'], 404);
                
                $sql = file_get_contents($sqlFile);
                $queries = explode(';', $sql);
                $successCount = 0;
                $errorCount = 0;
                $details = [];

                foreach ($queries as $q) {
                    $q = trim($q);
                    if (empty($q)) continue;
                    if (stripos($q, 'DROP TABLE') === 0) continue; 
                    
                    try {
                        $pdo->exec($q);
                        $successCount++;
                    } catch (Exception $e) {
                        $errorCount++;
                    }
                }

                // [CRITICAL FIX] Specifically add missing columns/tables
                try {
                    $pdo->exec("ALTER TABLE teachers ADD `position` VARCHAR(255) DEFAULT NULL AFTER `name` ");
                    $details[] = "เพิ่มคอลัมน์ 'position' ในตาราง teachers";
                } catch (Exception $e) {}

                try {
                    $pdo->exec("ALTER TABLE subjects ADD `level` VARCHAR(20) DEFAULT NULL AFTER `is_double` ");
                    $details[] = "เพิ่มคอลัมน์ 'level' ในตาราง subjects";
                } catch (Exception $e) {}

                try {
                    $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `school_id` int(11) NOT NULL,
                        `academic_year` varchar(4) NOT NULL,
                        `semester` int(1) NOT NULL,
                        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `school_id` (`school_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
                    $details[] = "ตรวจสอบตาราง settings";
                } catch (Exception $e) {}

                try {
                    $pdo->exec("CREATE TABLE IF NOT EXISTS `periods` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `school_id` int(11) NOT NULL,
                        `period_number` int(11) NOT NULL,
                        `start_time` time NOT NULL,
                        `end_time` time NOT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
                    $details[] = "ตรวจสอบตาราง periods";
                } catch (Exception $e) {}

                try {
                    $pdo->exec("CREATE TABLE IF NOT EXISTS `special_periods` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `school_id` int(11) NOT NULL,
                        `event_name` varchar(255) NOT NULL,
                        `day` int(1) NOT NULL,
                        `period` int(11) NOT NULL,
                        `applies_to_level` varchar(100) DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
                    $details[] = "ตรวจสอบตาราง special_periods";
                } catch (Exception $e) {}

                try {
                    $pdo->exec("CREATE TABLE IF NOT EXISTS `teaching_load` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `school_id` int(11) NOT NULL,
                        `teacher_id` int(11) NOT NULL,
                        `subject_id` int(11) NOT NULL,
                        `classroom_id` int(11) NOT NULL,
                        `room_id` int(11) DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
                    $details[] = "ตรวจสอบตาราง teaching_load";
                } catch (Exception $e) {}

                // Add level to subjects if not exists
                try {
                    $pdo->exec("ALTER TABLE subjects ADD `level` VARCHAR(20) DEFAULT NULL AFTER `name` ");
                    $details[] = "เพิ่มคอลัมน์ 'level' ในตาราง subjects";
                } catch (Exception $e) {}

                $msg = "อัพเดทโครงสร้างฐานข้อมูลเสร็จสิ้น\n";
                $msg .= "- ประมวลผลคำสั่ง SQL ทั้งหมด: " . ($successCount + $errorCount) . " คำสั่ง\n";
                if (!empty($details)) {
                    $msg .= "- การเปลี่ยนแปลงที่สำคัญ: " . implode(", ", $details);
                } else {
                    $msg .= "- ไม่มีการเปลี่ยนแปลงโครงสร้างใหม่ที่จำเป็น (ฐานข้อมูลทันสมัยอยู่แล้ว)";
                }

                jsonResponse(['success' => true, 'message' => $msg]);
            } catch (Exception $e) {
                jsonResponse(['error' => $e->getMessage()], 500);
            }
            break;

        // SETTINGS & PERIODS
        case 'get_settings':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            try {
                $stmt = $pdo->prepare("SELECT * FROM settings WHERE school_id = ?");
                $stmt->execute([$school_id]);
                $settings = $stmt->fetch();
                if (!$settings) {
                    $settings = ['academic_year' => date('Y') + 543, 'semester' => 1];
                }
                jsonResponse($settings);
            } catch (Exception $e) {
                // If table doesn't exist, return defaults
                jsonResponse(['academic_year' => date('Y') + 543, 'semester' => 1]);
            }
            break;

        case 'save_settings':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            try {
                $stmt = $pdo->prepare("INSERT INTO settings (school_id, academic_year, semester) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE academic_year = VALUES(academic_year), semester = VALUES(semester)");
                $stmt->execute([$school_id, $data['academic_year'], $data['semester']]);
                jsonResponse(['success' => true]);
            } catch (PDOException $e) {
                if ($e->getCode() == '42S02') { // Table not found
                    $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `school_id` int(11) NOT NULL,
                        `academic_year` varchar(4) NOT NULL,
                        `semester` int(1) NOT NULL,
                        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `school_id` (`school_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
                    // Retry
                    $stmt = $pdo->prepare("INSERT INTO settings (school_id, academic_year, semester) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE academic_year = VALUES(academic_year), semester = VALUES(semester)");
                    $stmt->execute([$school_id, $data['academic_year'], $data['semester']]);
                    jsonResponse(['success' => true]);
                }
                throw $e;
            }
            break;

        case 'periods_list':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            $stmt = $pdo->prepare("SELECT * FROM periods WHERE school_id = ? ORDER BY period_number ASC");
            $stmt->execute([$school_id]);
            jsonResponse($stmt->fetchAll());
            break;

        case 'period_save':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            $items = $data['items'] ?? [];
            $pdo->beginTransaction();
            try {
                try {
                    $pdo->prepare("DELETE FROM periods WHERE school_id = ?")->execute([$school_id]);
                } catch (PDOException $e) {
                    if ($e->getCode() == '42S02') {
                        $pdo->exec("CREATE TABLE IF NOT EXISTS `periods` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `school_id` int(11) NOT NULL,
                            `period_number` int(11) NOT NULL,
                            `start_time` time NOT NULL,
                            `end_time` time NOT NULL,
                            PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
                    } else { throw $e; }
                }
                
                $stmt = $pdo->prepare("INSERT INTO periods (school_id, period_number, start_time, end_time) VALUES (?, ?, ?, ?)");
                foreach($items as $item) {
                    $stmt->execute([$school_id, $item['period_number'], $item['start_time'], $item['end_time']]);
                }
                $pdo->commit();
                jsonResponse(['success' => true]);
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                jsonResponse(['error' => $e->getMessage()], 500);
            }
            break;

        case 'special_periods_list':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            $stmt = $pdo->prepare("SELECT * FROM special_periods WHERE school_id = ? ORDER BY day, period ASC");
            $stmt->execute([$school_id]);
            jsonResponse($stmt->fetchAll());
            break;

        case 'special_period_add':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            try {
                $stmt = $pdo->prepare("INSERT INTO special_periods (school_id, event_name, day, period, applies_to_level) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$school_id, $data['event_name'], $data['day'], $data['period'], $data['applies_to_level']]);
                jsonResponse(['success' => true]);
            } catch (PDOException $e) {
                if ($e->getCode() == '42S02') { // Table not found
                    $pdo->exec("CREATE TABLE IF NOT EXISTS `special_periods` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `school_id` int(11) NOT NULL,
                        `event_name` varchar(255) NOT NULL,
                        `day` int(1) NOT NULL,
                        `period` int(11) NOT NULL,
                        `applies_to_level` varchar(100) DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
                    // Retry
                    $stmt = $pdo->prepare("INSERT INTO special_periods (school_id, event_name, day, period, applies_to_level) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$school_id, $data['event_name'], $data['day'], $data['period'], $data['applies_to_level']]);
                    jsonResponse(['success' => true]);
                }
                throw $e;
            }
            break;

        case 'special_period_delete':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            $stmt = $pdo->prepare("DELETE FROM special_periods WHERE id = ? AND school_id = ?");
            $stmt->execute([$_GET['id'], $school_id]);
            jsonResponse(['success' => true]);
            break;

        case 'teaching_load_list':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            $teacher_id = $_GET['teacher_id'] ?? 0;
            $sql = "SELECT tl.*, s.name as subject_name, s.code as subject_code, c.name as classroom_name, c.level as classroom_level, r.name as room_name 
                    FROM teaching_load tl 
                    JOIN subjects s ON tl.subject_id = s.id 
                    JOIN classrooms c ON tl.classroom_id = c.id 
                    LEFT JOIN rooms r ON tl.room_id = r.id 
                    WHERE tl.school_id = ?";
            $params = [$school_id];
            if ($teacher_id) {
                $sql .= " AND tl.teacher_id = ?";
                $params[] = $teacher_id;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            jsonResponse($stmt->fetchAll());
            break;

        case 'teaching_load_add':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            try {
                $stmt = $pdo->prepare("INSERT INTO teaching_load (school_id, teacher_id, subject_id, classroom_id, room_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$school_id, $data['teacher_id'], $data['subject_id'], $data['classroom_id'], $data['room_id']]);
                jsonResponse(['success' => true]);
            } catch (PDOException $e) {
                if ($e->getCode() == '42S02') { // Table not found
                    $pdo->exec("CREATE TABLE IF NOT EXISTS `teaching_load` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `school_id` int(11) NOT NULL,
                        `teacher_id` int(11) NOT NULL,
                        `subject_id` int(11) NOT NULL,
                        `classroom_id` int(11) NOT NULL,
                        `room_id` int(11) DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
                    
                    $stmt = $pdo->prepare("INSERT INTO teaching_load (school_id, teacher_id, subject_id, classroom_id, room_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$school_id, $data['teacher_id'], $data['subject_id'], $data['classroom_id'], $data['room_id']]);
                    jsonResponse(['success' => true]);
                } else { throw $e; }
            }
            break;

        case 'teaching_load_delete':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            $stmt = $pdo->prepare("DELETE FROM teaching_load WHERE id = ? AND school_id = ?");
            $stmt->execute([$_GET['id'], $school_id]);
            jsonResponse(['success' => true]);
            break;

        case 'subjects_by_level':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            $level = $_GET['level'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM subjects WHERE school_id = ? AND level = ?");
            $stmt->execute([$school_id, $level]);
            jsonResponse($stmt->fetchAll());
            break;

        case 'get_teacher_timetable':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            $teacher_id = $_GET['teacher_id'];
            $stmt = $pdo->prepare("SELECT t.*, s.name as subject_name, s.code as subject_code, c.level as classroom_level, c.name as classroom_name, r.name as room_name 
                                    FROM timetable t
                                    JOIN subjects s ON t.subject_id = s.id
                                    JOIN classrooms c ON t.classroom_id = c.id
                                    JOIN rooms r ON t.room_id = r.id
                                    WHERE t.teacher_id = ? AND t.school_id = ?");
            $stmt->execute([$teacher_id, $school_id]);
            jsonResponse($stmt->fetchAll());
            break;

        case 'get_classroom_timetable':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            $classroom_id = $_GET['classroom_id'];
            $stmt = $pdo->prepare("SELECT t.*, s.name as subject_name, s.code as subject_code, tea.name as teacher_name, r.name as room_name 
                                    FROM timetable t
                                    JOIN subjects s ON t.subject_id = s.id
                                    JOIN teachers tea ON t.teacher_id = tea.id
                                    JOIN rooms r ON t.room_id = r.id
                                    WHERE t.classroom_id = ? AND t.school_id = ?");
            $stmt->execute([$classroom_id, $school_id]);
            jsonResponse($stmt->fetchAll());
            break;

        case 'auto_generate_timetable':
            if (!$school_id) jsonResponse(['error' => 'No school associated'], 400);
            
            try {
                $pdo->beginTransaction();
                
                // 1. Clear existing timetable for this school
                $stmt = $pdo->prepare("DELETE FROM timetable WHERE school_id = ?");
                $stmt->execute([$school_id]);
                
                // 2. Load teaching loads
                $stmt = $pdo->prepare("SELECT * FROM teaching_load WHERE school_id = ?");
                $stmt->execute([$school_id]);
                $loads = $stmt->fetchAll();
                
                // 3. Load periods to know which ones are available (type='normal')
                $stmt = $pdo->prepare("SELECT * FROM periods WHERE school_id = ? ORDER BY period_number ASC");
                $stmt->execute([$school_id]);
                $allPeriods = $stmt->fetchAll();
                $availablePeriodNums = array_map(function($p) { return $p['period_number']; }, array_filter($allPeriods, function($p) { 
                    return strtolower(trim($p['type'])) == 'normal'; 
                }));
                
                if (empty($availablePeriodNums)) {
                    throw new Exception("ยังไม่ได้ตั้งค่าช่วงเวลาปกติ (Normal) ในเมนูตั้งค่าพื้นฐาน");
                }

                $assignedCount = 0;
                $days = [1, 2, 3, 4, 5]; // Mon-Fri
                
                // Keep track of busy slots: [day][period][teacher_id] or [day][period][classroom_id]
                $busyTeachers = [];
                $busyClassrooms = [];
                
                // Simple Greedy Algorithm
                foreach ($loads as $load) {
                    // Get hours per week (default 2 if not specified elsewhere)
                    // We'll fetch the actual value from subjects table for better accuracy
                    $stmtSubject = $pdo->prepare("SELECT hours FROM subjects WHERE id = ?");
                    $stmtSubject->execute([$load['subject_id']]);
                    $subject = $stmtSubject->fetch();
                    $hoursToSchedule = $subject ? (int)$subject['hours'] : 2;

                    for ($h = 0; $h < $hoursToSchedule; $h++) {
                        $scheduled = false;
                        foreach ($days as $day) {
                            foreach ($availablePeriodNums as $period) {
                                $teacherKey = "{$day}-{$period}-{$load['teacher_id']}";
                                $classroomKey = "{$day}-{$period}-{$load['classroom_id']}";
                                
                                if (!isset($busyTeachers[$teacherKey]) && !isset($busyClassrooms[$classroomKey])) {
                                    // Slot is free!
                                    $stmt = $pdo->prepare("INSERT INTO timetable (school_id, teacher_id, subject_id, classroom_id, room_id, day, period) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                    $stmt->execute([
                                        $school_id, 
                                        $load['teacher_id'], 
                                        $load['subject_id'], 
                                        $load['classroom_id'], 
                                        $load['room_id'], 
                                        $day, 
                                        $period
                                    ]);
                                    
                                    $busyTeachers[$teacherKey] = true;
                                    $busyClassrooms[$classroomKey] = true;
                                    $assignedCount++;
                                    $scheduled = true;
                                    break;
                                }
                            }
                            if ($scheduled) break;
                        }
                    }
                }
                
                $pdo->commit();
                jsonResponse(['success' => true, 'count' => $assignedCount]);
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                jsonResponse(['error' => $e->getMessage()], 500);
            }
            break;

        default:
            jsonResponse(['error' => 'Action not found'], 404);
    }
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
?>
