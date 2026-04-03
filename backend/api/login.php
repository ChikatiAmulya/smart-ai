<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($data['email'] ?? '');
    $password = md5($data['password'] ?? '');
    
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        
        // Remove sensitive data
        unset($user['password']);
        
        echo json_encode([
            'success' => true,
            'user' => $user,
            'message' => 'Login successful'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
}
?>