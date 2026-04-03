<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $match_id = $data['match_id'] ?? 0;
    $sender_id = $data['sender_id'] ?? 0;
    $receiver_id = $data['receiver_id'] ?? 0;
    $message = trim($data['message'] ?? '');
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
        exit();
    }
    
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("INSERT INTO chat_messages (match_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$match_id, $sender_id, $receiver_id, $message])) {
        // Send notification to receiver
        sendNotification($receiver_id, '💬 New Message', "You have a new message regarding your lost/found item.", 'chat', $match_id);
        
        echo json_encode(['success' => true, 'message' => 'Message sent']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message']);
    }
}
?>