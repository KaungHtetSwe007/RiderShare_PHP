<?php
// Start session at the very beginning
session_start();

// Database connection using MySQLi
$connection = new mysqli("localhost", "root", "", "rideshare");

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Initialize variables
$status = 'unknown';
$driver_name = '';
$debug_info = '';

// Check if we have a driver ID from session or request parameters
$driver_id = $_SESSION['driver_id'] ?? $_GET['driver_id'] ?? null;

if ($driver_id) {
    // Query the database for driver status using MySQLi
    $stmt = $connection->prepare("SELECT status, name FROM drivers WHERE id = ?");
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $driver = $result->fetch_assoc();
        $status = $driver['status'];
        $driver_name = $driver['name'];
    } else {
        $debug_info = "No driver found with ID: $driver_id";
    }
    $stmt->close();
} else {
    $debug_info = "No driver ID provided in session or URL parameters";
}

// Set page title based on status
$page_title = "Driver Registration Status - RideShare";
if ($status === 'pending') {
    $page_title = "Registration Under Review - RideShare";
} elseif ($status === 'approved') {
    $page_title = "Registration Approved - RideShare";
} elseif ($status === 'rejected') {
    $page_title = "Registration Not Approved - RideShare";
}

include('navbar.php');
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Padauk', 'Noto Sans Myanmar', sans-serif;
        }
        
        .status-card {
            max-width: 600px;
            margin: 100px auto;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .success-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .pending-icon {
            font-size: 5rem;
            color: #ffc107;
            margin-bottom: 20px;
        }
        
        .rejected-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .highlight {
            color: #1fbad6;
            font-weight: bold;
        }
        
        .debug-info {
            font-size: 12px;
            color: #888;
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f8f8;
            border-radius: 5px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn-custom {
            min-width: 180px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card status-card">
            <div class="card-body text-center p-5">
                <?php if ($status === 'pending'): ?>
                    <i class="fas fa-hourglass-half pending-icon"></i>
                    <h2 class="mb-4"><?php echo htmlspecialchars($driver_name); ?> ရှင့် ယာဉ်မောင်း မှတ်ပုံတင်မှု စစ်ဆေးဆဲဖြစ်ပါသည်!</h2>
                    <p class="lead">
                        သင်၏ မှတ်ပုံတင်မှုကို လက်ခံရရှိပါသည်။ 
                        <span class="highlight">24-48 နာရီအတွင်း</span> ကျွန်ုပ်တို့၏ အဖွဲ့မှ ဆက်သွယ်ပေးပါမည်။
                    </p>
                    <p class="mb-4">
                        အတည်ပြုချက်အတွက် စောင့်ဆိုင်းနေစဉ် သင်၏ အကောင့်အခြေအနေကို 
                        <span class="highlight">ဆက်တင်များ</span> ကဏ္ဍတွင် စစ်ဆေးနိုင်ပါသည်။
                    </p>
                    <div class="action-buttons">
                        <a href="index.php" class="btn btn-primary btn-custom">
                            <i class="fas fa-home me-2"></i> ပင်မစာမျက်နှာ
                        </a>
                        <a href="driver_dashboard.php" class="btn btn-outline-secondary btn-custom">
                            <i class="fas fa-cog me-2"></i> ဆက်တင်များ
                        </a>
                    </div>
                
                <?php elseif ($status === 'approved'): ?>
                    <i class="fas fa-check-circle success-icon"></i>
                    <h2 class="mb-4"><?php echo htmlspecialchars($driver_name); ?> ရှင့် ယာဉ်မောင်း မှတ်ပုံတင်မှု အောင်မြင်ပါသည်!</h2>
                    <p class="lead">
                        သင်၏ မှတ်ပုံတင်မှုကို အတည်ပြုပြီးဖြစ်ပါသည်။ 
                        <span class="highlight">ယခုပင် RideShare ယာဉ်မောင်းအဖြစ် စတင်အလုပ်လုပ်နိုင်ပါပြီ။</span>
                    </p>
                    <p class="mb-4">
                        ကျေးဇူးပြု၍ အောက်ပါ Login ခလုတ်ကို နှိပ်၍ သင်၏ အကောင့်သို့ ဝင်ရောက်ပါ။
                    </p>
                    <div class="action-buttons">
                        <a href="driver_login.php" class="btn btn-success btn-custom">
                            <i class="fas fa-sign-in-alt me-2"></i> Login ဝင်ရန်
                        </a>
                        <a href="index.php" class="btn btn-outline-primary btn-custom">
                            <i class="fas fa-home me-2"></i> ပင်မစာမျက်နှာ
                        </a>
                    </div>
                
                <?php elseif ($status === 'rejected'): ?>
                    <i class="fas fa-times-circle rejected-icon"></i>
                    <h2 class="mb-4"><?php echo htmlspecialchars($driver_name); ?> ရှင့် ယာဉ်မောင်း မှတ်ပုံတင်မှု မအောင်မြင်ပါ</h2>
                    <p class="lead">
                        ဝမ်းနည်းပါသည်၊ သင်၏ မှတ်ပုံတင်မှုကို အတည်မပြုနိုင်ပါ။ 
                        <span class="highlight">သင်တင်ပြခဲ့သော စာရွက်စာတမ်းများတွင် ပြဿနာတစ်ခုခုရှိနေပါသည်။</span>
                    </p>
                    <p class="mb-4">
                        ပိုမိုသိရှိလိုပါက ကျွန်ုပ်တို့၏ ဖောက်သည်ဝန်ဆောင်မှုဌာနသို့ ဆက်သွယ်နိုင်ပါသည်။
                        သို့မဟုတ် <span class="highlight">ပြန်လည်မှတ်ပုံတင်ရန်</span> ကြိုးစားနိုင်ပါသည်။
                    </p>
                    <div class="action-buttons">
                        <a href="driver_register.php" class="btn btn-primary btn-custom">
                            <i class="fas fa-redo me-2"></i> ပြန်�လည်မှတ်ပုံတင်ရန်
                        </a>
                        <a href="contact.php" class="btn btn-outline-secondary btn-custom">
                            <i class="fas fa-headset me-2"></i> ဆက်သွယ်ရန်
                        </a>
                    </div>
                
                <?php else: ?>
                    <i class="fas fa-question-circle pending-icon"></i>
                    <h2 class="mb-4">ယာဉ်�မောင်း မှတ်ပုံတင်မှု အခြေအနေ</h2>
                    <p class="lead">
                        သင်၏ မှတ်ပုံတင်မှု အခြေအနေကို ရှာဖွေ၍မရပါ။ 
                        <span class="highlight">ကျေးဇူးပြု၍ ကျွန်ုပ်တို့၏ ဖောက်သည်�ဝန်ဆောင်မှုဌာနသို့ ဆက်သွယ်ပါ။</span>
                    </p>
                    <?php if (!empty($debug_info)): ?>
                    <div class="debug-info">
                        <strong>Debug Information:</strong><br>
                        <?php echo $debug_info; ?><br>
                        Driver ID: <?php echo $driver_id ?? 'Not set'; ?><br>
                        Session ID: <?php echo session_id(); ?>
                    </div>
                    <?php endif; ?>
                    <div class="action-buttons mt-3">
                        <a href="index.php" class="btn btn-primary btn-custom">
                            <i class="fas fa-home me-2"></i> ပင်မစာမျက်နှာ
                        </a>
                        <a href="contact.php" class="btn btn-outline-danger btn-custom">
                            <i class="fas fa-headset me-2"></i> ဆက်သွယ်ရန်
                        </a>
                        <a href="driver_register.php" class="btn btn-outline-primary btn-custom">
                            <i class="fas fa-user-plus me-2"></i> ပြန်လည်မှတ်ပုံတင်ရန်
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php 
    // Close the database connection
    $connection->close();
    include('footer.php') 
    ?>
</body>
</html>