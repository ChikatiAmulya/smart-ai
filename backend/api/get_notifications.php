<?php
require_once '../config/database.php';

$user_id = $_GET['user_id'] ?? 0;
$limit = intval($_GET['limit'] ?? 20);

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Count unread
$stmt = $db->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread = $stmt->fetch();

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => $unread['unread']
]);
?>