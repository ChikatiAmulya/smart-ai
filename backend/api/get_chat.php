<?php
require_once '../config/database.php';

$match_id = $_GET['match_id'] ?? 0;
$limit = intval($_GET['limit'] ?? 50);

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT c.*, u.fullname as sender_name 
                       FROM chat_messages c
                       JOIN users u ON c.sender_id = u.id
                       WHERE c.match_id = ? 
                       ORDER BY c.created_at ASC 
                       LIMIT $limit");
$stmt->execute([$match_id]);
$messages = $stmt->fetchAll();

// Mark messages as read
$user_id = $_GET['user_id'] ?? 0;
if ($user_id) {
    $update = $db->prepare("UPDATE chat_messages SET is_read = 1, read_at = NOW() WHERE receiver_id = ? AND match_id = ?");
    $update->execute([$user_id, $match_id]);
}

echo json_encode(['success' => true, 'messages' => $messages]);
?>