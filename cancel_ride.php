<?php
// cancel_ride.php
session_start();

// Check if rider is logged in
if (!isset($_SESSION['rider_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Database connection
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'rideshare';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ride_id'])) {
    $ride_id = $conn->real_escape_string($_POST['ride_id']);
    $rider_id = $_SESSION['rider_id'];
    
    // Verify that the ride belongs to the logged-in rider and is still pending
    $verify_query = "SELECT * FROM rides WHERE id = $ride_id AND rider_id = $rider_id AND status = 'pending'";
    $verify_result = $conn->query($verify_query);
    
    if ($verify_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Ride not found or cannot be cancelled']);
        exit;
    }
    
    // Update ride status to cancelled
    $query = "UPDATE rides SET status = 'cancelled', updated_at = NOW() WHERE id = $ride_id";
    
    if ($conn->query($query)) {
        echo json_encode(['success' => true, 'message' => 'ခရီးစဉ် ပယ်ဖျက်ခြင်း အောင်မြင်ပါသည်']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'မှားယွင်းသော တောင်းဆိုချက်']);
}

$conn->close();
?>