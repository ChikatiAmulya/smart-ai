<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $match_id = $data['match_id'] ?? 0;
    $claimant_id = $data['claimant_id'] ?? 0;
    $verification_answer = $data['verification_answer'] ?? '';
    
    $db = Database::getInstance()->getConnection();
    
    // Get match details
    $stmt = $db->prepare("SELECT * FROM matches WHERE id = ?");
    $stmt->execute([$match_id]);
    $match = $stmt->fetch();
    
    if (!$match) {
        echo json_encode(['success' => false, 'message' => 'Match not found']);
        exit();
    }
    
    // Get item details to determine owner
    $stmt = $db->prepare("SELECT user_id FROM items WHERE id = ?");
    $stmt->execute([$match['lost_item_id']]);
    $lost_item = $stmt->fetch();
    
    $owner_id = $lost_item['user_id'];
    $otp = rand(100000, 999999);
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    $stmt = $db->prepare("INSERT INTO claims (match_id, claimant_id, owner_id, verification_answer, otp_code, otp_expiry, status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    
    if ($stmt->execute([$match_id, $claimant_id, $owner_id, $verification_answer, $otp, $otp_expiry])) {
        $claim_id = $db->lastInsertId();
        
        // Send OTP to claimant (in production, send via SMS/Email)
        sendNotification($claimant_id, '🔐 Claim OTP', "Your verification OTP is: {$otp}. Valid for 10 minutes.", 'claim', $claim_id);
        sendNotification($owner_id, '📝 Claim Request', "Someone wants to claim your item. Please review the claim.", 'claim', $claim_id);
        
        echo json_encode([
            'success' => true,
            'claim_id' => $claim_id,
            'otp' => $otp,
            'message' => 'Claim initiated. OTP sent for verification.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to initiate claim']);
    }
}
?>