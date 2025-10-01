<?php
// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rideshare');

session_start();

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

if (!isset($_SESSION['driver_id'])) {
    die(json_encode(['success' => false, 'error' => 'Not authenticated']));
}

$driver_id = $_SESSION['driver_id'];

// Check if there are any new ride requests or updates
$stmt = $conn->prepare("
    SELECT COUNT(*) as new_requests 
    FROM rides 
    WHERE driver_id = ? AND status = 'pending' AND created_at > DATE_SUB(NOW(), INTERVAL 10 SECOND)
");
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'success' => true, 
    'has_updates' => $row['new_requests'] > 0
]);
?>