<?php
require_once __DIR__ . '/../include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $phone = $_POST['phone'] ?? '';
    $name = $_POST['name'] ?? '';
    $vehicle_type = $_POST['vehicle_type'] ?? '';
    $nrc = $_POST['nrc'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $address = $_POST['address'] ?? '';
    $license_number = $_POST['license_number'] ?? '';
    $license_expiry = $_POST['license_expiry'] ?? '';
    $vehicle_registration = $_POST['vehicle_registration'] ?? '';
    $vehicle_model = $_POST['vehicle_model'] ?? '';
    $vehicle_year = $_POST['vehicle_year'] ?? '';
    $vehicle_color = $_POST['vehicle_color'] ?? '';
    $engine_number = $_POST['engine_number'] ?? '';
    $terms = isset($_POST['terms']) ? 1 : 0;
    
    // File upload directory
    $uploadDir = __DIR__ . '/../uploads/drivers/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Handle file uploads
    $files = [];
    $fileFields = [
        'nrc_front', 'nrc_back', 'license_front', 'license_back', 'bluebook', 'vehicle_photos'
    ];
    
    foreach ($fileFields as $field) {
        if (!empty($_FILES[$field]['name'])) {
            // Handle multiple files for vehicle photos
            if ($field === 'vehicle_photos') {
                $vehiclePhotos = [];
                for ($i = 0; $i < count($_FILES[$field]['name']); $i++) {
                    $fileName = uniqid() . '_' . basename($_FILES[$field]['name'][$i]);
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES[$field]['tmp_name'][$i], $targetPath)) {
                        $vehiclePhotos[] = 'uploads/drivers/' . $fileName;
                    }
                }
                $files[$field] = implode(',', $vehiclePhotos);
            } 
            // Handle single file uploads
            else {
                $fileName = uniqid() . '_' . basename($_FILES[$field]['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES[$field]['tmp_name'], $targetPath)) {
                    $files[$field] = 'uploads/drivers/' . $fileName;
                }
            }
        }
    }
    
    // Validate input
    $errors = [];
    
    // Phone validation
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (!preg_match('/^9[0-9]{8,9}$/', $phone)) {
        $errors[] = "Invalid phone number format";
    }
    
    // Name validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    // Add more validations as needed...
    
    // Check if phone already exists
    $stmt = $pdo->prepare("SELECT id FROM drivers WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Phone number already registered";
    }
    
    // Check terms agreement
    if (!$terms) {
        $errors[] = "You must agree to the terms and conditions";
    }
    
    // If no errors, create driver
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO drivers (
                phone, name, vehicle_type, nrc, dob, address, license_number, license_expiry,
                vehicle_registration, vehicle_model, vehicle_year, vehicle_color, engine_number,
                nrc_front, nrc_back, license_front, license_back, bluebook, vehicle_photos
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )");
            
            $stmt->execute([
                $phone, $name, $vehicle_type, $nrc, $dob, $address, $license_number, $license_expiry,
                $vehicle_registration, $vehicle_model, $vehicle_year, $vehicle_color, $engine_number,
                $files['nrc_front'] ?? null, $files['nrc_back'] ?? null, 
                $files['license_front'] ?? null, $files['license_back'] ?? null,
                $files['bluebook'] ?? null, $files['vehicle_photos'] ?? null
            ]);
            
            // Success - redirect to confirmation page
            header("Location: driver_signup_success.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    
    // If there were errors, pass them back to the form
    session_start();
    $_SESSION['driver_signup_errors'] = $errors;
    $_SESSION['driver_signup_data'] = $_POST;
    header("Location: /index.php#driver-auth");
    exit;
}