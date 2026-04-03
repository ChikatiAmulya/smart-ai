<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($data['fullname'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';
    
    // Validation
    if (empty($fullname) || empty($email) || empty($phone) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit();
    }
    
    $hashedPassword = md5($password);
    $otp = rand(100000, 999999);
    
    $stmt = $db->prepare("INSERT INTO users (fullname, email, phone, password, otp_code) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$fullname, $email, $phone, $hashedPassword, $otp])) {
        $user_id = $db->lastInsertId();
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'user_id' => $user_id,
            'otp' => $otp
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed']);
    }
}
?>