<?php
// check_notifications.php
session_start();

if (!isset($_SESSION['rider_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$rider_id = $_SESSION['rider_id'];

// Database connection
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'rideshare';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch unread notifications for this rider
$sql = "SELECT * FROM notifications WHERE rider_id = ? AND is_read = 0 ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
    
    // Mark as read (optional - or do this when the user dismisses them)
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $row['id']);
    $update_stmt->execute();
}

echo json_encode(['success' => true, 'notifications' => $notifications]);

$stmt->close();
$conn->close();
?>