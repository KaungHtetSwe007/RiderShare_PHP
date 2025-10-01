
<?php
session_start();
require_once __DIR__ . '/../include/db.php';

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    // Validate required fields
    $requiredFields = [
        'phone', 'name', 'vehicle_type', 'nrc', 'dob', 'address',
        'license_number', 'license_expiry', 'vehicle_registration',
        'vehicle_model', 'vehicle_year', 'vehicle_color', 'engine_number'
    ];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }
    
    // Validate file uploads
    $requiredFiles = ['nrc_front', 'nrc_back', 'license_front', 'license_back', 'bluebook'];
    foreach ($requiredFiles as $file) {
        if (empty($_FILES[$file]['name'])) {
            throw new Exception("$file is required");
        }
    }
    
    if (empty($_FILES['vehicle_photos']['name'][0])) {
        throw new Exception("At least one vehicle photo is required");
    }
    
    // Check for duplicates
    $phone = $_POST['phone'];
    $nrc = $_POST['nrc'];
    $license_number = $_POST['license_number'];
    $vehicle_registration = $_POST['vehicle_registration'];
    $engine_number = $_POST['engine_number'];
    
    // Check phone number
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM drivers WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Phone number already registered");
    }
    
    // Check NRC
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM drivers WHERE nrc = ?");
    $stmt->execute([$nrc]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("NRC number already registered");
    }
    
    // Check license number
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM drivers WHERE license_number = ?");
    $stmt->execute([$license_number]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("License number already registered");
    }
    
    // Check vehicle registration number
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE registration_no = ?");
    $stmt->execute([$vehicle_registration]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Vehicle registration number already registered");
    }
    
    // Check engine number
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE engine_number = ?");
    $stmt->execute([$engine_number]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Engine number already registered");
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert driver data
    $stmt = $pdo->prepare("INSERT INTO drivers 
        (phone, name, nrc, dob, address, vehicle_type, license_number, license_expiry, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([
        $phone,
        $_POST['name'],
        $nrc,
        $_POST['dob'],
        $_POST['address'],
        $_POST['vehicle_type'],
        $license_number,
        $_POST['license_expiry']
    ]);
    $driver_id = $pdo->lastInsertId();


// ✅ Set driver_id in session AFTER insert
    $_SESSION['driver_id'] = $driver_id;
    
    // Insert vehicle data
    $stmt = $pdo->prepare("INSERT INTO vehicles 
        (driver_id, registration_no, vehicle_model, vehicle_year, vehicle_color, engine_number) 
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $driver_id,
        $vehicle_registration,
        $_POST['vehicle_model'],
        $_POST['vehicle_year'],
        $_POST['vehicle_color'],
        $engine_number
    ]);
    
    // Create upload directory
    $upload_dir = "uploads/drivers/$driver_id/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Process file uploads (NRC, License, Bluebook)
    $fileTypes = ['nrc_front', 'nrc_back', 'license_front', 'license_back', 'bluebook'];
    foreach ($fileTypes as $type) {
        if (!empty($_FILES[$type]['name'])) {
            $file_name = $type . '_' . time() . '_' . basename($_FILES[$type]['name']);
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES[$type]['tmp_name'], $target_file)) {
                $stmt = $pdo->prepare("INSERT INTO driver_documents 
                    (driver_id, document_type, file_path) 
                    VALUES (?, ?, ?)");
                $stmt->execute([$driver_id, $type, $target_file]); // ✅ save full path
            }
        }
    }
    
    // Process vehicle photos
    foreach ($_FILES['vehicle_photos']['tmp_name'] as $key => $tmp_name) {
        if (!empty($_FILES['vehicle_photos']['name'][$key])) {
            $file_name = 'vehicle_' . ($key+1) . '_' . time() . '_' . basename($_FILES['vehicle_photos']['name'][$key]);
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($tmp_name, $target_file)) {
                $stmt = $pdo->prepare("INSERT INTO driver_documents 
                    (driver_id, document_type, file_path) 
                    VALUES (?, 'vehicle_photo', ?)");
                $stmt->execute([$driver_id, $target_file]); // ✅ save full path
            }
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Redirect to success page
    header("Location: /driver_signup_success.php");
    exit();
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
    
    // Log error for debugging
    error_log("Driver signup error: " . $response['message']);
    
    // Store error in session for display on form
    $_SESSION['driver_signup_errors'] = [$response['message']];
    $_SESSION['driver_form_data'] = $_POST;
    
    // Redirect back to form
    header("Location: /index.php#driverForm");
    exit();
}