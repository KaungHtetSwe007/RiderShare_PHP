<?php
// create_ride_request.php
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

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Validate and sanitize input data
$driver_id = intval($data['driver_id']);
$rider_id = $_SESSION['rider_id'];
$pickup_location = $conn->real_escape_string($data['pickup_location']);
$dropoff_location = $conn->real_escape_string($data['dropoff_location']);
$vehicle_type = $conn->real_escape_string($data['vehicle_type']);
$passengers = intval($data['passengers']);

// Extract numeric value from fare string (remove "Ks" and commas)
$fare = floatval(preg_replace('/[^0-9.]/', '', $data['fare']));

// Optional fields with default values
$distance = isset($data['distance']) ? floatval($data['distance']) : 0;
$duration = isset($data['duration']) ? intval($data['duration']) : 0;
$start_time = isset($data['start_time']) ? $conn->real_escape_string($data['start_time']) : NULL;
$end_time = isset($data['end_time']) ? $conn->real_escape_string($data['end_time']) : NULL;

// Get rider name
$rider_query = "SELECT name FROM riders WHERE id = $rider_id";
$rider_result = $conn->query($rider_query);
$rider_name = "Unknown Rider";
if ($rider_result && $rider_result->num_rows > 0) {
    $rider = $rider_result->fetch_assoc();
    $rider_name = $conn->real_escape_string($rider['name']);
}

// Insert ride into database with all required fields
$query = "INSERT INTO rides (
            rider_id, rider_name, driver_id, 
            pickup_location, dropoff_location, fare, 
            distance, duration, vehicle_type, passengers, 
            status, ride_date, start_time, end_time, 
            created_at, updated_at
        ) VALUES (
            $rider_id, '$rider_name', $driver_id, 
            '$pickup_location', '$dropoff_location', $fare, 
            $distance, $duration, '$vehicle_type', $passengers, 
            'pending', NOW(), 
            " . ($start_time ? "'$start_time'" : "NULL") . ",
            " . ($end_time ? "'$end_time'" : "NULL") . ",
            NOW(), NOW()
        )";

if ($conn->query($query)) {
    $ride_id = $conn->insert_id;
    echo json_encode(['success' => true, 'ride_id' => $ride_id, 'message' => 'Ride request created successfully']);
} else {
    error_log("Ride creation error: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Failed to create ride: ' . $conn->error]);
}

$conn->close();
?>