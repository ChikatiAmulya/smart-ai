<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $data['user_id'] ?? 1;
    $type = $data['type'] ?? '';
    $main_category = $data['main_category'] ?? 'general';
    $sub_category = $data['sub_category'] ?? '';
    $title = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');
    $location = trim($data['location'] ?? '');
    $latitude = $data['latitude'] ?? null;
    $longitude = $data['longitude'] ?? null;
    $is_emergency = $data['is_emergency'] ?? false;
    $ocr_text = $data['ocr_text'] ?? '';
    
    if (empty($title) || empty($location)) {
        echo json_encode(['success' => false, 'message' => 'Title and location are required']);
        exit();
    }
    
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("INSERT INTO items (user_id, type, main_category, sub_category, title, description, location, latitude, longitude, is_emergency, ocr_text) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$user_id, $type, $main_category, $sub_category, $title, $description, $location, $latitude, $longitude, $is_emergency, $ocr_text])) {
        $item_id = $db->lastInsertId();
        
        // Run AI matching
        runAIMatching($db, $item_id, $type);
        
        // Handle emergency alert
        if ($is_emergency) {
            handleEmergencyAlert($db, $item_id, $user_id, $title, $location);
        }
        
        // Generate QR code if requested
        if ($data['generate_qr'] ?? false) {
            generateQRCode($db, $user_id, $item_id);
        }
        
        echo json_encode([
            'success' => true,
            'item_id' => $item_id,
            'message' => 'Item added successfully',
            'emergency_triggered' => $is_emergency
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add item']);
    }
}

function runAIMatching($db, $new_item_id, $type) {
    $opposite = $type == 'lost' ? 'found' : 'lost';
    
    // Get new item
    $stmt = $db->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$new_item_id]);
    $new_item = $stmt->fetch();
    
    // Get opposite items
    $stmt = $db->prepare("SELECT * FROM items WHERE type = ? AND is_resolved = 0 AND id != ?");
    $stmt->execute([$opposite, $new_item_id]);
    $existing_items = $stmt->fetchAll();
    
    foreach ($existing_items as $item) {
        $score = calculateAIMatch($new_item, $item);
        
        if ($score > 40) {
            if ($type == 'lost') {
                $lost_id = $new_item_id;
                $found_id = $item['id'];
            } else {
                $lost_id = $item['id'];
                $found_id = $new_item_id;
            }
            
            // Check if match already exists
            $check = $db->prepare("SELECT id FROM matches WHERE lost_item_id = ? AND found_item_id = ?");
            $check->execute([$lost_id, $found_id]);
            
            if (!$check->fetch()) {
                $insert = $db->prepare("INSERT INTO matches (lost_item_id, found_item_id, similarity_score) VALUES (?, ?, ?)");
                $insert->execute([$lost_id, $found_id, $score]);
                
                // Send notification to both users
                sendNotification($new_item['user_id'], '🎯 AI Match Found', "Your item matches with another report with {$score}% similarity!", 'match', $lost_id);
                sendNotification($item['user_id'], '🎯 AI Match Found', "Your item matches with another report with {$score}% similarity!", 'match', $found_id);
            }
        }
    }
}

function handleEmergencyAlert($db, $item_id, $user_id, $title, $location) {
    $alert_message = "🚨 EMERGENCY: Critical item '{$title}' reported lost/found at {$location}";
    
    $stmt = $db->prepare("INSERT INTO emergency_alerts (item_id, user_id, alert_message, notified_police, notified_at) VALUES (?, ?, ?, 1, NOW())");
    $stmt->execute([$item_id, $user_id, $alert_message]);
    
    // Notify all admins
    $stmt = $db->prepare("SELECT id FROM users WHERE role IN ('admin', 'volunteer')");
    $stmt->execute();
    $admins = $stmt->fetchAll();
    
    foreach ($admins as $admin) {
        sendNotification($admin['id'], '🚨 Emergency Alert', $alert_message, 'emergency', $item_id);
    }
}

function generateQRCode($db, $user_id, $item_id) {
    $token = 'QR_' . uniqid() . '_' . bin2hex(random_bytes(8));
    $stmt = $db->prepare("INSERT INTO qr_codes (user_id, item_id, qr_token) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $item_id, $token]);
}
?>