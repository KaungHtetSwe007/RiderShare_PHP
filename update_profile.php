<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['rider_id'])) {
    $rider_id = $_SESSION['rider_id'];
    $name = $_POST['name'];
    
    $response = ['success' => false];
    
    try {
        // Handle file upload
        if (!empty($_FILES['profile_picture']['name'])) {
            $target_dir = "uploads/riders/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $filename = "rider_" . $rider_id . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $filename;
            
            // Check if image file is an actual image
            $check = getimagesize($_FILES['profile_picture']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                    // Update database with new profile picture
                    $stmt = $pdo->prepare("UPDATE riders SET profile_picture = ?, name = ? WHERE id = ?");
                    $stmt->execute([$target_file, $name, $rider_id]);
                    
                    $response['success'] = true;
                    $response['profile_picture'] = $target_file;
                    $response['name'] = $name;
                    
                    // Update session
                    $_SESSION['rider_name'] = $name;
                }
            }
        } else {
            // Update only name
            $stmt = $pdo->prepare("UPDATE riders SET name = ? WHERE id = ?");
            $stmt->execute([$name, $rider_id]);
            
            $response['success'] = true;
            $response['name'] = $name;
            
            // Update session
            $_SESSION['rider_name'] = $name;
        }
    } catch (PDOException $e) {
        $response['error'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

header("HTTP/1.1 400 Bad Request");
echo json_encode(['success' => false, 'error' => 'Invalid request']);