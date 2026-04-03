<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $data['user_id'] ?? 0;
    $item_id = $data['item_id'] ?? 0;
    $ngo_name = $data['ngo_name'] ?? '';
    $amount = $data['amount'] ?? 0;
    
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("INSERT INTO rewards (user_id, item_id, reward_amount, donation_ngo, is_donated, reward_type) 
                           VALUES (?, ?, ?, ?, 1, 'donation')");
    
    if ($stmt->execute([$user_id, $item_id, $amount, $ngo_name])) {
        sendNotification($user_id, '❤️ Donation Made', "Thank you! ₹{$amount} has been donated to {$ngo_name} in your name.", 'reward', $item_id);
        
        echo json_encode(['success' => true, 'message' => 'Donation successful!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Donation failed']);
    }
}
?>