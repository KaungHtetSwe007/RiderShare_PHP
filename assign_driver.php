<?php
header('Content-Type: application/json');

// Database connection
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'rideshare';

$response = ['success' => false, 'message' => ''];

try {
    // Get JSON data from request
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($data['ride_id']) || empty($data['driver_id'])) {
        $response['message'] = 'Ride ID and Driver ID are required';
        echo json_encode($response);
        exit;
    }
    
    // Connect to database
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Update ride with driver ID and change status to assigned
    $stmt = $conn->prepare("UPDATE rides SET driver_id = ?, status = 'assigned', updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ii", $data['driver_id'], $data['ride_id']);
    
    if ($stmt->execute()) {
        // Update driver availability
        $updateDriver = $conn->prepare("UPDATE drivers SET is_available = 0 WHERE id = ?");
        $updateDriver->bind_param("i", $data['driver_id']);
        $updateDriver->execute();
        $updateDriver->close();
        
        $response['success'] = true;
        $response['message'] = 'Driver assigned successfully';
    } else {
        $response['message'] = 'Failed to assign driver: ' . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>