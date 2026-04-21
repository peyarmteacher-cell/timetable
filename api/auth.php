<?php
require_once 'config.php';

// Authentication API
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'login') {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'];
        $password = $data['password'];

        // ในระบบจริงจะตรวจสอบกับ MySQL
        // $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        // $stmt->execute([$username]);
        // $user = $stmt->fetch();
        // if ($user && password_verify($password, $user['password'])) { ... }

        // จำลองการเข้าสู่ระบบสำหรับ Preview
        jsonResponse(['id' => 1, 'name' => 'Admin User', 'role' => 'admin', 'school_id' => 1]);
    }

    if ($action === 'register') {
        $data = json_decode(file_get_contents('php://input'), true);
        // ประมวลผลการลงทะเบียน
        jsonResponse(['message' => 'ลงทะเบียนสำเร็จ']);
    }
}
?>
