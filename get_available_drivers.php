<?php
header('Content-Type: application/json');

// Database connection
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'rideshare';

$response = ['success' => false, 'message' => '', 'drivers' => []];

try {
    // Get vehicle type from POST request (not GET)
    $vehicle_type = isset($_POST['vehicle_type']) ? $_POST['vehicle_type'] : '';
    
    // Connect to database
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Prepare query based on vehicle type
    if (!empty($vehicle_type)) {
        $stmt = $conn->prepare("SELECT d.*, v.registration_no, v.vehicle_model 
                               FROM drivers d 
                               JOIN vehicles v ON d.id = v.driver_id 
                               WHERE d.status = 'approved' AND d.is_available = 1 AND d.vehicle_type = ?");
        $stmt->bind_param("s", $vehicle_type);
    } else {
        $stmt = $conn->prepare("SELECT d.*, v.registration_no, v.vehicle_model 
                               FROM drivers d 
                               JOIN vehicles v ON d.id = v.driver_id 
                               WHERE d.status = 'approved' AND d.is_available = 1");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Add some random data for demonstration
            $row['avg_rating'] = rand(40, 50) / 10; // Random rating between 4.0 and 5.0
            $row['completed_rides'] = rand(10, 100); // Random number of completed rides
            $row['eta'] = rand(3, 15); // Random ETA in minutes
            
            $response['drivers'][] = $row;
        }
        $response['success'] = true;
    } else {
        $response['message']  = 'No available drivers found';
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>