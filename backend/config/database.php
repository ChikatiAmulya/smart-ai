<?php
// config/database.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lost_found_system');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch(PDOException $e) {
            die(json_encode(['success' => false, 'error' => 'Database connection failed']));
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Start session for user management
session_start();

// Helper functions
function sendNotification($user_id, $title, $message, $type = 'system', $reference_id = null) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, reference_id) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $title, $message, $type, $reference_id]);
}

function calculateAIMatch($item1, $item2) {
    $score = 0;
    
    // Title similarity (30%)
    similar_text(strtolower($item1['title']), strtolower($item2['title']), $titlePercent);
    $titleScore = $titlePercent * 0.3;
    $score += $titleScore;
    
    // Description keyword matching (25%)
    $desc1 = strtolower($item1['description']);
    $desc2 = strtolower($item2['description']);
    $words1 = array_unique(preg_split('/\s+/', $desc1));
    $words2 = array_unique(preg_split('/\s+/', $desc2));
    $common = count(array_intersect($words1, $words2));
    $descScore = ($common / max(1, count($words1))) * 25;
    $score += $descScore;
    
    // Category matching (25%)
    if ($item1['main_category'] == $item2['main_category']) {
        $score += 15;
        if ($item1['sub_category'] == $item2['sub_category']) $score += 10;
    }
    
    // Location similarity (20%)
    if (strpos($item1['location'], $item2['location']) !== false || 
        strpos($item2['location'], $item1['location']) !== false) {
        $score += 20;
    }
    
    return min($score, 100);
}
?>