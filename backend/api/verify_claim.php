<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $claim_id = $data['claim_id'] ?? 0;
    $otp = $data['otp'] ?? '';
    
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM claims WHERE id = ? AND otp_code = ? AND otp_expiry > NOW()");
    $stmt->execute([$claim_id, $otp]);
    $claim = $stmt->fetch();
    
    if ($claim) {
        // Update claim status
        $update = $db->prepare("UPDATE claims SET status = 'approved' WHERE id = ?");
        $update->execute([$claim_id]);
        
        // Update match status
        $update_match = $db->prepare("UPDATE matches SET status = 'claimed' WHERE id = ?");
        $update_match->execute([$claim['match_id']]);
        
        // Update item as resolved
        $stmt = $db->prepare("SELECT lost_item_id, found_item_id FROM matches WHERE id = ?");
        $stmt->execute([$claim['match_id']]);
        $match = $stmt->fetch();
        
        $update_item = $db->prepare("UPDATE items SET is_resolved = 1, resolved_by = ?, resolved_date = NOW() WHERE id IN (?, ?)");
        $update_item->execute([$claim['claimant_id'], $match['lost_item_id'], $match['found_item_id']]);
        
        // Add reward points to finder
        $reward_points = 50;
        $stmt = $db->prepare("INSERT INTO rewards (user_id, item_id, points_earned, reward_type) VALUES (?, ?, ?, 'points')");
        $stmt->execute([$claim['claimant_id'], $match['found_item_id'], $reward_points]);
        
        // Update user trust score
        $update_trust = $db->prepare("UPDATE users SET trust_score = trust_score + ? WHERE id = ?");
        $update_trust->execute([$reward_points, $claim['claimant_id']]);
        
        // Send notifications
        sendNotification($claim['claimant_id'], '✅ Claim Verified', 'Your claim has been verified! You can now contact the owner.', 'claim', $claim_id);
        sendNotification($claim['owner_id'], '✅ Item Recovered', 'Your item has been claimed successfully!', 'claim', $claim_id);
        
        echo json_encode([
            'success' => true,
            'message' => 'Claim verified successfully! Item recovery initiated.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
    }
}
?>