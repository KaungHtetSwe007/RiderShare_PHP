<?php
require_once __DIR__ . '/../include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate input
    $errors = [];
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no validation errors, check credentials
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM riders WHERE phone = ?");
        $stmt->execute([$phone]);
        $rider = $stmt->fetch();
        
        if ($rider && password_verify($password, $rider['password'])) {
            // Start session and redirect
            session_start();
            $_SESSION['rider_id'] = $rider['id'];
            $_SESSION['rider_phone'] = $rider['phone'];
            $_SESSION['rider_name'] = $rider['name'];
            
            // Redirect back to homepage
            header("Location: /index.php");
            exit;
        } else {
            $errors[] = "Invalid phone number or password";
        }
    }
    
    // If there were errors, pass them back to the form
    session_start();
    $_SESSION['login_errors'] = $errors;
    $_SESSION['login_data'] = ['phone' => $phone];
    header("Location: /index.php#rider-auth");
    exit;
}
?>