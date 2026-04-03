<?php
require_once '../config/database.php';

$db = Database::getInstance()->getConnection();

// Get counts
$stmt = $db->query("SELECT COUNT(*) as total FROM items");
$total_items = $stmt->fetch();

$stmt = $db->query("SELECT COUNT(*) as lost FROM items WHERE type = 'lost' AND is_resolved = 0");
$lost_items = $stmt->fetch();

$stmt = $db->query("SELECT COUNT(*) as found FROM items WHERE type = 'found' AND is_resolved = 0");
$found_items = $stmt->fetch();

$stmt = $db->query("SELECT COUNT(*) as resolved FROM items WHERE is_resolved = 1");
$resolved_items = $stmt->fetch();

$stmt = $db->query("SELECT COUNT(*) as emergency FROM items WHERE is_emergency = 1 AND is_resolved = 0");
$emergency_items = $stmt->fetch();

$stmt = $db->query("SELECT COUNT(*) as matches FROM matches WHERE status = 'pending'");
$pending_matches = $stmt->fetch();

$stmt = $db->query("SELECT AVG(trust_score) as avg_trust FROM users");
$avg_trust = $stmt->fetch();

echo json_encode([
    'success' => true,
    'stats' => [
        'total_items' => $total_items['total'],
        'lost_items' => $lost_items['lost'],
        'found_items' => $found_items['found'],
        'resolved_items' => $resolved_items['resolved'],
        'emergency_items' => $emergency_items['emergency'],
        'pending_matches' => $pending_matches['matches'],
        'avg_trust_score' => round($avg_trust['avg_trust'] ?? 0)
    ]
]);
?>