<?php
require_once 'config.php';

// Authentication API
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'login') {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'];
        $password = $data['password'];

        $stmt = $pdo->prepare("SELECT u.*, s.name as school_name FROM users u LEFT JOIN schools s ON u.school_id = s.id WHERE u.username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $password === $user['password']) { // In production use password_verify with hashed passwords
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['school_id'] = $user['school_id'];
            $_SESSION['school_name'] = $user['school_name'];
            
            unset($user['password']);
            jsonResponse($user);
        } else {
            jsonResponse(['error' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'], 401);
        }
    }

    if ($action === 'register') {
        $data = json_decode(file_get_contents('php://input'), true);
        // ประมวลผลการลงทะเบียน
        jsonResponse(['message' => 'ลงทะเบียนสำเร็จ']);
    }
}
?>
