<?php
require_once __DIR__ . '/../include/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exists' => false, 'message' => 'Invalid request']);
    exit;
}

$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';

if (empty($field) || empty($value)) {
    echo json_encode(['exists' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    $exists = false;
    $message = '';
    
    switch ($field) {
        case 'phone':
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM drivers WHERE phone = ?");
            $stmt->execute([$value]);
            $exists = $stmt->fetchColumn() > 0;
            $message = 'Phone number already registered';
            break;
            
        case 'nrc':
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM drivers WHERE nrc = ?");
            $stmt->execute([$value]);
            $exists = $stmt->fetchColumn() > 0;
            $message = 'NRC number already registered';
            break;
            
        case 'license_number':
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM drivers WHERE license_number = ?");
            $stmt->execute([$value]);
            $exists = $stmt->fetchColumn() > 0;
            $message = 'License number already registered';
            break;
            
        case 'vehicle_registration':
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE registration_no = ?");
            $stmt->execute([$value]);
            $exists = $stmt->fetchColumn() > 0;
            $message = 'Vehicle registration number already registered';
            break;
            
        case 'engine_number':
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE engine_number = ?");
            $stmt->execute([$value]);
            $exists = $stmt->fetchColumn() > 0;
            $message = 'Engine number already registered';
            break;
            
        default:
            $exists = false;
            $message = 'Unknown field';
    }
    
    echo json_encode(['exists' => $exists, 'message' => $message]);
    
} catch (Exception $e) {
    echo json_encode(['exists' => false, 'message' => 'Error checking database']);
}
?>