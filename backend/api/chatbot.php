<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$message = strtolower(trim($data['message'] ?? ''));

$response = "";

if (strpos($message, 'report') !== false) {
    $response = "To report an item:\n1. Go to 'Report Item' tab\n2. Select Lost or Found\n3. Choose category and subcategory\n4. Fill title, description, location\n5. Upload image (or use AI generation)\n6. Click Submit - AI will automatically match!";
} 
elseif (strpos($message, 'claim') !== false) {
    $response = "Secure claim process:\n1. Go to 'AI Matches' tab\n2. Find your match\n3. Click 'Secure Claim'\n4. Answer verification question\n5. Enter OTP sent to your phone\n6. Upload proof if needed\n7. Item marked as recovered!\n\nYou'll earn trust points for successful claims.";
}
elseif (strpos($message, 'emergency') !== false) {
    $response = "🚨 Emergency Feature:\n- Check 'Emergency' box when reporting critical items\n- Police and admins are notified immediately\n- Item gets priority visibility\n- Faster response time\n- Suitable for: IDs, passports, valuables, certificates";
}
elseif (strpos($message, 'category') !== false) {
    $response = "Available Categories:\n📱 Electronics (Mobiles, Laptops, Earphones, Accessories)\n💍 Ornaments (Rings, Necklaces, Bracelets, Earrings)\n📄 Certificates (Aadhaar, PAN, Passport, Certificates)\n💰 Money (Wallets, Cash, Cards, Cheques)\n🎒 Bags (Backpacks, Handbags, Luggage)\n📦 General (Keys, Glasses, Medicines, Books)";
}
elseif (strpos($message, 'reward') !== false || strpos($message, 'donation') !== false) {
    $response = "Reward & Donation System:\n- Earn trust points for returning items\n- Owner can give cash reward to finder\n- OR donate to NGO in finder's name\n- Top helpers appear on leaderboard\n- Builds community trust and encouragement!";
}
elseif (strpos($message, 'qr') !== false) {
    $response = "QR Code Feature:\n- Generate unique QR for your items\n- Attach QR to belongings\n- When scanned, owner gets instant notification\n- Helps recover items faster\n- Available in 'Report Item' section";
}
elseif (strpos($message, 'trust') !== false) {
    $response = "Trust Score System:\n- Start with 100 points\n- +10 for successful claims\n- +5 for reporting found items\n- -20 for false claims\n- Higher trust = more credibility\n- Top helpers get special badges!";
}
elseif (strpos($message, 'location') !== false || strpos($message, 'nearby') !== false) {
    $response = "Location Features:\n- Geo-fencing shows items within 5km\n- Map view with all nearby items\n- Real-time location alerts\n- Use 'Use My Location' for accuracy\n- Check 'Nearby Map' tab for visualization";
}
elseif (strpos($message, 'ocr') !== false) {
    $response = "OCR (Optical Character Recognition):\n- Extract text from document images\n- Upload ID cards, certificates, receipts\n- Helps identify owners from documents\n- Text used for AI matching\n- Available in 'Report Item' form";
}
else {
    $response = "👋 I'm your AI assistant! I can help with:\n\n📝 How to report lost/found items\n🔐 How to claim items\n🚨 Emergency alerts\n📂 Categories & subcategories\n💰 Rewards & donations\n📱 QR code feature\n📍 Location & geo-fencing\n🔍 OCR text extraction\n\nWhat would you like to know?";
}

echo json_encode(['success' => true, 'response' => $response]);
?>