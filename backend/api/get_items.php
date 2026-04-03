<?php
require_once '../config/database.php';

$type = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? 'all';
$subcategory = $_GET['subcategory'] ?? 'all';
$limit = intval($_GET['limit'] ?? 20);
$search = $_GET['search'] ?? '';

$db = Database::getInstance()->getConnection();

$sql = "SELECT i.*, u.fullname, u.phone, u.trust_score 
        FROM items i 
        JOIN users u ON i.user_id = u.id 
        WHERE i.is_resolved = 0";

if ($type != 'all') $sql .= " AND i.type = '$type'";
if ($category != 'all') $sql .= " AND i.main_category = '$category'";
if ($subcategory != 'all') $sql .= " AND i.sub_category = '$subcategory'";
if ($search) $sql .= " AND (i.title LIKE '%$search%' OR i.description LIKE '%$search%')";

$sql .= " ORDER BY i.is_emergency DESC, i.created_at DESC LIMIT $limit";

$stmt = $db->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll();

echo json_encode(['success' => true, 'items' => $items]);
?>