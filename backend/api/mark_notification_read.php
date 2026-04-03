<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$notification_id = $data['notification_id'] ?? 0;

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
$stmt->execute([$notification_id]);

echo json_encode(['success' => true]);
?>