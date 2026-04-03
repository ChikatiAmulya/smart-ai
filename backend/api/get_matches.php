<?php
require_once '../config/database.php';

$user_id = $_GET['user_id'] ?? 0;

$db = Database::getInstance()->getConnection();

$sql = "SELECT m.*, 
        l.id as lost_id, l.title as lost_title, l.description as lost_desc, l.location as lost_location, l.main_category as lost_category,
        f.id as found_id, f.title as found_title, f.description as found_desc, f.location as found_location, f.main_category as found_category,
        u1.fullname as lost_owner, u2.fullname as found_owner
        FROM matches m
        JOIN items l ON m.lost_item_id = l.id
        JOIN items f ON m.found_item_id = f.id
        JOIN users u1 ON l.user_id = u1.id
        JOIN users u2 ON f.user_id = u2.id
        WHERE m.status = 'pending'
        ORDER BY m.similarity_score DESC";

$stmt = $db->prepare($sql);
$stmt->execute();
$matches = $stmt->fetchAll();

echo json_encode(['success' => true, 'matches' => $matches]);
?>