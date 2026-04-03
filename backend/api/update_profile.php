<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $data['user_id'] ?? 0;
    $fullname = $data['fullname'] ?? '';
    $phone = $data['phone'] ?? '';
    
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("UPDATE users SET fullname = ?, phone = ? WHERE id = ?");
    
    if ($stmt->execute([$fullname, $phone, $user_id])) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
}
?>