<?php
// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rideshare');

session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['driver_id'])) {
    header('Location: driver_login.php');
    exit;
}

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$driver_id = $_SESSION['driver_id'];

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $target_dir = "uploads/drivers/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $new_filename = "driver_" . $driver_id . "_" . time() . "." . $imageFileType;
    $target_file = $target_dir . $new_filename;

    $uploadOk = 1;
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check === false) {
        $upload_error = "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size (max 2MB)
    if ($_FILES["profile_picture"]["size"] > 2000000) {
        $upload_error = "Sorry, your file is too large. Max 2MB allowed.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $upload_error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Delete old profile picture if exists
            $stmt = $conn->prepare("SELECT profile_picture FROM drivers WHERE id = ?");
            $stmt->bind_param("i", $driver_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (!empty($row['profile_picture']) && file_exists($row['profile_picture'])) {
                    unlink($row['profile_picture']);
                }
            }

            // Update database with new profile picture path
            $stmt = $conn->prepare("UPDATE drivers SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $target_file, $driver_id);
            $stmt->execute();
            $stmt->close();

            $upload_success = "Profile picture updated successfully.";
        } else {
            $upload_error = "Sorry, there was an error uploading your file.";
        }
    }
}

// Handle availability toggle
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_availability'])) {
    $is_available = intval($_POST['is_available']);
    $stmt = $conn->prepare("UPDATE drivers SET is_available = ? WHERE id = ?");
    $stmt->bind_param("ii", $is_available, $driver_id);
    $stmt->execute();
    $stmt->close();

    // Refresh page after update
    header("Location: driver_dashboard.php");
    exit;
}

// Get driver information
$driver_info = [];
$stmt = $conn->prepare("SELECT * FROM drivers WHERE id = ?");
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $driver_info = $result->fetch_assoc();
}

// Get vehicle information
$vehicle_info = [];
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE driver_id = ?");
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $vehicle_info = $result->fetch_assoc();
}

// Get ride history
$ride_history = [];
$stmt = $conn->prepare("SELECT * FROM rides WHERE driver_id = ? ORDER BY ride_date DESC LIMIT 5");
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ride_history[] = $row;
    }
}

// Get earnings summary
$earnings = ['today' => 0, 'week' => 0, 'month' => 0, 'total' => 0];
$stmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN DATE(ride_date) = CURDATE() THEN fare ELSE 0 END) AS today,
        SUM(CASE WHEN YEARWEEK(ride_date) = YEARWEEK(CURDATE()) THEN fare ELSE 0 END) AS week,
        SUM(CASE WHEN MONTH(ride_date) = MONTH(CURDATE()) AND YEAR(ride_date) = YEAR(CURDATE()) THEN fare ELSE 0 END) AS month,
        SUM(fare) AS total
    FROM rides 
    WHERE driver_id = ? AND status = 'completed'
");
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $earnings = $result->fetch_assoc();
}

// Handle ride actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $ride_id = intval($_POST['ride_id']);

        switch ($_POST['action']) {
            case 'accept_ride':
                // Update ride status to accepted and set start time
                $stmt = $conn->prepare("UPDATE rides SET status = 'assigned', start_time = NOW() WHERE id = ? AND driver_id = ?");
                $stmt->bind_param("ii", $ride_id, $driver_id);
                $stmt->execute();

                // Update driver availability to 0 (not available)
                $stmt2 = $conn->prepare("UPDATE drivers SET is_available = 0 WHERE id = ?");
                $stmt2->bind_param("i", $driver_id);
                $stmt2->execute();

                // Create notification for rider
                $stmt3 = $conn->prepare("SELECT rider_id FROM rides WHERE id = ?");
                $stmt3->bind_param("i", $ride_id);
                $stmt3->execute();
                $result = $stmt3->get_result();
                if ($result->num_rows > 0) {
                    $ride = $result->fetch_assoc();
                    $rider_id = $ride['rider_id'];
                    $message = "Your ride request has been accepted by driver " . $driver_info['name'];
                    $stmt4 = $conn->prepare("INSERT INTO notifications (rider_id, message, type) VALUES (?, ?, 'ride_accepted')");
                    $stmt4->bind_param("is", $rider_id, $message);
                    $stmt4->execute();
                }
                break;

            case 'reject_ride':
                // Update ride status to cancelled
                $stmt = $conn->prepare("UPDATE rides SET status = 'cancelled', end_time = NOW() WHERE id = ? AND driver_id = ?");
                $stmt->bind_param("ii", $ride_id, $driver_id);
                $stmt->execute();

                // Create notification for rider
                $stmt2 = $conn->prepare("SELECT rider_id FROM rides WHERE id = ?");
                $stmt2->bind_param("i", $ride_id);
                $stmt2->execute();
                $result = $stmt2->get_result();
                if ($result->num_rows > 0) {
                    $ride = $result->fetch_assoc();
                    $rider_id = $ride['rider_id'];
                    $message = "Your ride request has been rejected by driver " . $driver_info['name'];
                    $stmt3 = $conn->prepare("INSERT INTO notifications (rider_id, message, type) VALUES (?, ?, 'ride_rejected')");
                    $stmt3->bind_param("is", $rider_id, $message);
                    $stmt3->execute();
                }
                break;

            case 'start_ride':
                $stmt = $conn->prepare("UPDATE rides SET status = 'in_progress' WHERE id = ? AND driver_id = ?");
                $stmt->bind_param("ii", $ride_id, $driver_id);
                $stmt->execute();
                break;

            case 'complete_ride':
                $stmt = $conn->prepare("UPDATE rides SET status = 'completed', end_time = NOW() WHERE id = ? AND driver_id = ?");
                $stmt->bind_param("ii", $ride_id, $driver_id);
                $stmt->execute();

                // Update driver availability to 1 (available)
                $stmt2 = $conn->prepare("UPDATE drivers SET is_available = 1 WHERE id = ?");
                $stmt2->bind_param("i", $driver_id);
                $stmt2->execute();
                break;

            case 'cancel_ride':
                $stmt = $conn->prepare("UPDATE rides SET status = 'cancelled', end_time = NOW() WHERE id = ? AND driver_id = ?");
                $stmt->bind_param("ii", $ride_id, $driver_id);
                $stmt->execute();

                // Update driver availability to 1 (available)
                $stmt2 = $conn->prepare("UPDATE drivers SET is_available = 1 WHERE id = ?");
                $stmt2->bind_param("i", $driver_id);
                $stmt2->execute();
                break;
        }

        // Refresh page after action
        header("Location: driver_dashboard.php");
        exit;
    }
}
// Get pending ride requests (exclude cancelled rides)
$pending_requests = [];
$stmt = $conn->prepare("
    SELECT r.*, 
           riders.name AS rider_name, 
           riders.phone AS rider_phone 
    FROM rides r
    JOIN riders ON r.rider_id = riders.id
    WHERE r.driver_id = ? AND r.status = 'pending'
    ORDER BY r.ride_date DESC
");
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pending_requests[] = $row;
    }
}

// Get active rides (assigned or in progress, exclude cancelled rides)
$active_rides = [];
$stmt = $conn->prepare("
    SELECT r.*, 
           riders.name AS rider_name, 
           riders.phone AS rider_phone 
    FROM rides r
    JOIN riders ON r.rider_id = riders.id
    WHERE r.driver_id = ? AND (r.status = 'assigned' OR r.status = 'in_progress')
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $active_rides[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="my">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ယာဉ်မောင်း ဒက်ရှ်ဘုတ်</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Noto Sans Myanmar', 'Padauk', sans-serif;
        }

        :root {
            --primary: #1fbad6;
            --secondary: #2c3e50;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #f8f9fa;
            --dark: #343a40;
            --gray: #6c757d;
            --light-gray: #e9ecef;
        }

        body {
            background-color: #f0f2f5;
            font-size: 14px;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px;
        }

        header {
            background: linear-gradient(120deg, var(--secondary), var(--primary));
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .logo i {
            font-size: 24px;
            margin-right: 10px;
            color: var(--primary);
        }

        .logo h1 {
            font-size: 18px;
        }

        .driver-info {
            text-align: left;
            width: 100%;
        }

        .driver-name {
            font-size: 16px;
            font-weight: bold;
        }

        .driver-status {
            font-size: 12px;
            background: rgba(255, 255, 255, 0.2);
            padding: 3px 8px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 5px;
        }

        .logout-btn {
            background: none;
            border: none;
            color: white;
            font-size: 12px;
            cursor: pointer;
            text-decoration: underline;
            margin-top: 5px;
            padding: 0;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 15px;
        }

        .card h2 {
            font-size: 16px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--light-gray);
            color: var(--dark);
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 80px 1fr;
            gap: 15px;
        }

        .profile-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
        }

        .profile-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .detail-item {
            margin-bottom: 8px;
        }

        .detail-label {
            font-size: 12px;
            color: var(--gray);
            margin-bottom: 3px;
        }

        .detail-value {
            font-weight: 500;
            font-size: 13px;
        }

        .vehicle-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .vehicle-photo {
            width: 100%;
            height: auto;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 10px;
        }

        .earnings-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            text-align: center;
        }

        .earning-card {
            padding: 12px;
            border-radius: 8px;
            background: var(--light);
        }

        .earning-value {
            font-size: 16px;
            font-weight: bold;
            margin: 8px 0;
        }

        .today .earning-value {
            color: var(--primary);
        }

        .week .earning-value {
            color: #3498db;
        }

        .month .earning-value {
            color: #9b59b6;
        }

        .total .earning-value {
            color: var(--success);
        }

        .earning-label {
            font-size: 12px;
            color: var(--gray);
        }

        .ride-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .ride-item {
            padding: 12px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .ride-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .ride-id {
            font-weight: bold;
            color: var(--dark);
            font-size: 14px;
        }

        .ride-status {
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-pending {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning);
        }

        .status-assigned {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }

        .status-in_progress {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .status-completed {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success);
        }

        .status-cancelled {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }

        .ride-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .rider-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rider-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }

        .rider-name {
            font-weight: 500;
            font-size: 14px;
        }

        .rider-phone {
            font-size: 12px;
            color: var(--gray);
        }

        .ride-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 10px;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.3s;
            font-size: 13px;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .btn-accept {
            background: var(--success);
            color: white;
        }

        .btn-reject {
            background: var(--danger);
            color: white;
        }

        .btn-start {
            background: var(--primary);
            color: white;
        }

        .btn-complete {
            background: var(--success);
            color: white;
        }

        .btn-cancel {
            background: var(--danger);
            color: white;
        }

        .btn-view {
            background: var(--secondary);
            color: white;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .history-table th,
        .history-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .history-table th {
            background: var(--light);
            font-weight: 600;
            color: var(--dark);
            font-size: 12px;
        }

        .availability-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .toggle-label {
            font-weight: 500;
            font-size: 14px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 28px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--success);
        }

        input:checked+.slider:before {
            transform: translateX(22px);
        }

        .chart-container {
            height: 250px;
            margin-top: 15px;
        }

        /* Tablet View */
        @media (min-width: 600px) {
            .container {
                padding: 20px;
            }

            header {
                flex-direction: row;
                align-items: center;
                padding: 15px 20px;
            }

            .logo {
                margin-bottom: 0;
            }

            .driver-info {
                text-align: right;
                width: auto;
            }

            .dashboard-grid {
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }

            .earnings-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .profile-details,
            .vehicle-details {
                grid-template-columns: 1fr 1fr;
            }

            .ride-actions {
                flex-direction: row;
            }

            .btn {
                width: auto;
                padding: 8px 15px;
            }
        }

        /* Laptop View */
        @media (min-width: 900px) {
            body {
                font-size: 15px;
            }

            .container {
                padding: 25px;
            }

            .card {
                padding: 20px;
            }

            .card h2 {
                font-size: 18px;
            }

            .profile-grid {
                grid-template-columns: 100px 1fr;
            }

            .profile-photo {
                width: 100px;
                height: 100px;
            }

            .detail-label {
                font-size: 13px;
            }

            .detail-value {
                font-size: 15px;
            }

            .earning-value {
                font-size: 18px;
            }

            .ride-details {
                grid-template-columns: 1fr 1fr;
            }

            .history-table {
                font-size: 14px;
            }

            .history-table th {
                font-size: 13px;
            }
        }

        /* Large Desktop View */
        @media (min-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .chart-container {
                height: 300px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-car"></i>
                <h1>RideShare ယာဉ်မောင်း ဒက်ရှ်ဘုတ်</h1>
            </div>
            <div class="driver-info">
                <div class="driver-name"><?php echo htmlspecialchars($driver_info['name']); ?></div>
                <div class="driver-status">ယာဉ်မောင်းအဖြစ် အတည်ပြုပြီး</div>
                <form action="driver_logout.php" method="post">
                    <button type="submit" class="logout-btn">အကောင့်ထွက်မည်</button>
                </form>
            </div>
        </header>

        <div class="dashboard-grid">
            <!-- Profile Card -->
            <div class="card">
                <h2>ကိုယ်ရေးအချက်အလက်</h2>
                <div class="profile-grid">
                    <?php
                    $profile_picture = !empty($driver_info['profile_picture']) ? $driver_info['profile_picture'] :
                        "https://randomuser.me/api/portraits/men/" . rand(1, 99) . ".jpg";
                    ?>
                    <img src="<?php echo $profile_picture; ?>" alt="Profile" class="profile-photo" id="profileImage">

                    <div class="profile-details">
                        <div class="detail-item">
                            <div class="detail-label">ဖုန်းနံပါတ်</div>
                            <div class="detail-value"><?php echo htmlspecialchars($driver_info['phone']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">NRC အမှတ်</div>
                            <div class="detail-value"><?php echo htmlspecialchars($driver_info['nrc']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">ယာဉ်မောင်းလိုင်စင်</div>
                            <div class="detail-value"><?php echo htmlspecialchars($driver_info['license_number']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">လိုင်စင်သက်တမ်း</div>
                            <div class="detail-value"><?php echo date('d/m/Y', strtotime($driver_info['license_expiry'])); ?></div>
                        </div>
                    </div>
                </div>

                <div class="profile-upload-container">
                    <form method="post" enctype="multipart/form-data" class="profile-upload-form">
                        <label class="profile-upload-btn">
                            <i class="fas fa-camera"></i> ဓာတ်ပုံပြောင်းရန်
                            <input type="file" name="profile_picture" id="profileUpload" accept="image/*">
                        </label>
                        <button type="submit" class="btn btn-start" style="width: auto;">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    </form>

                    <?php if (isset($upload_success)): ?>
                        <div class="upload-status upload-success">
                            <?php echo $upload_success; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($upload_error)): ?>
                        <div class="upload-status upload-error">
                            <?php echo $upload_error; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Vehicle Card -->
            <div class="card">
                <h2>ယာဉ်အချက်အလက်</h2>
                <div class="vehicle-details">
                    <div class="detail-item">
                        <div class="detail-label">ယာဉ်မှတ်ပုံတင်အမှတ်</div>
                        <div class="detail-value"><?php echo htmlspecialchars($vehicle_info['registration_no']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">ယာဉ်အမျိုးအစား</div>
                        <div class="detail-value"><?php echo htmlspecialchars($driver_info['vehicle_type']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">ယာဉ်မော်ဒယ်</div>
                        <div class="detail-value"><?php echo htmlspecialchars($vehicle_info['vehicle_model']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">ထုတ်လုပ်နှစ်</div>
                        <div class="detail-value"><?php echo htmlspecialchars($vehicle_info['vehicle_year']); ?></div>
                    </div>
                </div>
                <img src="https://via.placeholder.com/600x400?text=Vehicle+Photo" alt="Vehicle" class="vehicle-photo">
            </div>

            <!-- Earnings Card -->
            <div class="card">
                <h2>အမြတ်ငွေ</h2>
                <div class="earnings-grid">
                    <div class="earning-card today">
                        <div class="earning-label">ယနေ့</div>
                        <div class="earning-value"><?php echo $earnings['today']; ?> Ks</div>
                    </div>
                    <div class="earning-card week">
                        <div class="earning-label">ဒီအပတ်</div>
                        <div class="earning-value"><?php echo $earnings['week']; ?> Ks</div>
                    </div>
                    <div class="earning-card month">
                        <div class="earning-label">ဒီလ</div>
                        <div class="earning-value"><?php echo $earnings['month']; ?> Ks</div>
                    </div>
                    <div class="earning-card total">
                        <div class="earning-label">စုစုပေါင်း</div>
                        <div class="earning-value"><?php echo $earnings['total']; ?> Ks</div>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="earningsChart"></canvas>
                </div>
            </div>

            <!-- Availability Card -->
            <div class="card">
                <h2>လုပ်ဆောင်နိုင်မှု အခြေအနေ</h2>
                <form method="post" id="availabilityForm">
                    <div class="availability-toggle">
                        <span class="toggle-label">ယာဉ်မောင်းလိုက်ပါရန် အသင့်ဖြစ်ပါပြီ</span>
                        <label class="switch">
                            <input type="checkbox" name="is_available" id="availabilityToggle"
                                <?php echo ($driver_info['is_available'] == 1) ? 'checked' : ''; ?>
                                value="1">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <input type="hidden" name="toggle_availability" value="1">
                </form>
                <p style="margin-top: 15px; color: var(--gray); font-size: 14px;">
                    <i class="fas fa-info-circle"></i> ယာဉ်မောင်းလိုက်ပါရန် အသင့်ဖြစ်ပါက ခလုတ်ဖွင့်ထားပါ။
                    ဤအခြေအနေတွင် ခရီးသည်များက သင့်အားမြင်တွေ့နိုင်မည်ဖြစ်ပြီး ခရီးစဉ်များကို လက်ခံရရှိနိုင်မည်ဖြစ်သည်။
                </p>
            </div>
        </div>

        <!-- Pending Ride Requests Card -->
        <?php if (count($pending_requests) > 0): ?>
            <div class="card">
                <h2>စောင့်ဆိုင်းနေသော ခရီးစဉ် တောင်းဆိုမှုများ</h2>
                <div class="ride-list">
                    <?php foreach ($pending_requests as $ride): ?>
                        <div class="ride-item">
                            <div class="ride-header">
                                <div class="ride-id">ခရီးစဉ် #<?php echo $ride['id']; ?></div>
                                <div class="ride-status status-pending">စောင့်ဆိုင်းနေ</div>
                            </div>

                            <div class="ride-details">
                                <div>
                                    <div class="detail-label">ခရီးသည်</div>
                                    <div class="rider-info">
                                        <img src="https://randomuser.me/api/portraits/<?php echo rand(0, 1) ? 'men' : 'women'; ?>/<?php echo rand(1, 99); ?>.jpg" alt="Rider" class="rider-avatar">
                                        <div>
                                            <div class="rider-name"><?php echo htmlspecialchars($ride['rider_name']); ?></div>
                                            <div class="rider-phone"><?php echo htmlspecialchars($ride['rider_phone']); ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="detail-label">တည်နေရာ</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($ride['pickup_location']); ?> မှ <?php echo htmlspecialchars($ride['dropoff_location']); ?></div>
                                </div>

                                <div>
                                    <div class="detail-label">ခန့်မှန်း ကုန်ကျငွေ</div>
                                    <div class="detail-value"><?php echo $ride['fare']; ?> Ks</div>
                                </div>

                                <div>
                                    <div class="detail-label">ခန့်မှန်း ခရီးကြာချိန်</div>
                                    <div class="detail-value"><?php echo $ride['duration']; ?> မိနစ်</div>
                                </div>
                            </div>

                            <div class="ride-actions">
                                <form method="post">
                                    <input type="hidden" name="ride_id" value="<?php echo $ride['id']; ?>">
                                    <button type="submit" name="action" value="accept_ride" class="btn btn-accept">
                                        <i class="fas fa-check"></i> လက်ခံမည်
                                    </button>
                                    <button type="submit" name="action" value="reject_ride" class="btn btn-reject">
                                        <i class="fas fa-times"></i> ငြင်းပယ်မည်
                                    </button>
                                </form>
                                <button class="btn btn-view">
                                    <i class="fas fa-map-marked-alt"></i> မြေပုံကြည့်ရန်
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Active Rides Card -->
        <div class="card">
            <h2>လက်ရှိ ခရီးစဉ်များ</h2>
            <div class="ride-list">
                <?php if (count($active_rides) > 0): ?>
                    <?php foreach ($active_rides as $ride): ?>
                        <div class="ride-item">
                            <div class="ride-header">
                                <div class="ride-id">ခရီးစဉ် #<?php echo $ride['id']; ?></div>
                                <div class="ride-status status-<?php echo $ride['status']; ?>">
                                    <?php
                                    switch ($ride['status']) {
                                        case 'assigned':
                                            echo 'လက်ခံပြီး';
                                            break;
                                        case 'in_progress':
                                            echo 'ခရီးစဉ်ဆောင်ရွက်နေ';
                                            break;
                                        default:
                                            echo $ride['status'];
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="ride-details">
                                <div>
                                    <div class="detail-label">ခရီးသည်</div>
                                    <div class="rider-info">
                                        <img src="https://randomuser.me/api/portraits/<?php echo rand(0, 1) ? 'men' : 'women'; ?>/<?php echo rand(1, 99); ?>.jpg" alt="Rider" class="rider-avatar">
                                        <div>
                                            <div class="rider-name"><?php echo htmlspecialchars($ride['rider_name']); ?></div>
                                            <div class="rider-phone"><?php echo htmlspecialchars($ride['rider_phone']); ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="detail-label">တည်နေရာ</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($ride['pickup_location']); ?> မှ <?php echo htmlspecialchars($ride['dropoff_location']); ?></div>
                                </div>

                                <div>
                                    <div class="detail-label">ခန့်မှန်း ကုန်ကျငွေ</div>
                                    <div class="detail-value"><?php echo $ride['fare']; ?> Ks</div>
                                </div>

                                <div>
                                    <div class="detail-label">ခန့်မှန်း ခရီးကြာချိန်</div>
                                    <div class="detail-value"><?php echo $ride['duration']; ?> မိနစ်</div>
                                </div>
                            </div>

                            <div class="ride-actions">
                                <?php if ($ride['status'] == 'assigned'): ?>
                                    <form method="post">
                                        <input type="hidden" name="ride_id" value="<?php echo $ride['id']; ?>">
                                        <button type="submit" name="action" value="start_ride" class="btn btn-start">
                                            <i class="fas fa-play"></i> ခရီးစဉ်စတင်မည်
                                        </button>
                                        <button type="submit" name="action" value="cancel_ride" class="btn btn-cancel">
                                            <i class="fas fa-times"></i> ပယ်ဖျက်မည်
                                        </button>
                                    </form>
                                <?php elseif ($ride['status'] == 'in_progress'): ?>
                                    <form method="post">
                                        <input type="hidden" name="ride_id" value="<?php echo $ride['id']; ?>">
                                        <button type="submit" name="action" value="complete_ride" class="btn btn-complete">
                                            <i class="fas fa-flag-checkered"></i> ခရီးစဉ်ပြီးမြောက်ပြီ
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <button class="btn btn-view">
                                    <i class="fas fa-map-marked-alt"></i> မြေပုံကြည့်ရန်
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ride-item" style="text-align: center; padding: 30px;">
                        <i class="fas fa-car" style="font-size: 48px; color: var(--light-gray); margin-bottom: 15px;"></i>
                        <p>လက်ရှိတွင် ခရီးစဉ်မရှိပါ</p>
                        <p style="color: var(--gray); margin-top: 10px; font-size: 14px;">ခရီးစဉ်များကို ဤနေရာတွင် မြင်တွေ့ရမည်ဖြစ်သည်</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ride History Card -->
        <div class="card">
            <h2>ပြီးဆုံးခဲ့သော ခရီးစဉ်များ</h2>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>ရက်စွဲ</th>
                        <th>ခရီးသည်</th>
                        <th>အစ</th>
                        <th>အဆုံး</th>
                        <th>ကုန်ကျငွေ</th>
                        <th>အခြေအနေ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($ride_history) > 0): ?>
                        <?php foreach ($ride_history as $ride): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($ride['ride_date'])); ?></td>
                                <td><?php echo htmlspecialchars($ride['rider_name']); ?></td>
                                <td><?php echo htmlspecialchars($ride['pickup_location']); ?></td>
                                <td><?php echo htmlspecialchars($ride['dropoff_location']); ?></td>
                                <td><?php echo $ride['fare']; ?> Ks</td>
                                <td>
                                    <span class="ride-status status-<?php echo $ride['status']; ?>">
                                        <?php
                                        switch ($ride['status']) {
                                            case 'completed':
                                                echo 'ပြီးဆုံး';
                                                break;
                                            case 'cancelled':
                                                echo 'ပယ်ဖျက်';
                                                break;
                                            default:
                                                echo $ride['status'];
                                        }
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px;">
                                <i class="fas fa-history" style="font-size: 48px; color: var(--light-gray); margin-bottom: 15px;"></i>
                                <p>ခရီးစဉ် မှတ်တမ်းများ မရှိသေးပါ</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Initialize earnings chart
        const ctx = document.getElementById('earningsChart').getContext('2d');
        const earningsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'အမြတ်ငွေ (Ks)',
                    data: [12000, 19000, 15000, 18000, 22000, 28000, 25000],
                    borderColor: '#1fbad6',
                    backgroundColor: 'rgba(31, 186, 214, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Toggle availability and submit form
        const toggle = document.getElementById('availabilityToggle');
        toggle.addEventListener('change', function() {
            document.getElementById('availabilityForm').submit();
        });

        // Function to check for ride status updates
        function checkRideStatusUpdates() {
            setInterval(() => {
                fetch('check_ride_updates.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.has_updates) {
                            // Refresh page to show updates
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error checking ride updates:', error);
                    });
            }, 5000); // Check every 5 seconds
        }

        // Start checking for updates
        checkRideStatusUpdates();
    </script>
</body>

</html>