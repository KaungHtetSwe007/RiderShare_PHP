<?php
require_once __DIR__ . '/../include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $phone = $_POST['phone'] ?? '';
    $name = $_POST['name'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate input
    $errors = [];
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (!preg_match('/^9[0-9]{8,9}$/', $phone)) {
        $errors[] = "Invalid phone number format";
    }
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    // Check if phone already exists
    $stmt = $pdo->prepare("SELECT id FROM riders WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Phone number already registered";
    }
    
    // If no errors, create user
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO riders (phone, name, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$phone, $name, $hashedPassword])) {
            // Start session and redirect
            session_start();
            $_SESSION['rider_id'] = $pdo->lastInsertId();
            $_SESSION['rider_phone'] = $phone;
            $_SESSION['rider_name'] = $name;
            
            header("Location: /index.php");
            exit;
        } else {
            $errors[] = "Failed to create account. Please try again.";
        }
    }
    
    // If there were errors, pass them back to the form
    session_start();
    $_SESSION['signup_errors'] = $errors;
    $_SESSION['signup_data'] = ['phone' => $phone, 'name' => $name];
    header("Location: /index.php#rider-auth");
    exit;
}
?>