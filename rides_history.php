<?php
session_start();

// Check if rider is logged in
if (!isset($_SESSION['rider_id'])) {
    header("Location: index.php");
    exit;
}

// Database connection
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'rideshare';

try {
    // Connect to database
    $conn = new mysqli($host, $user, $pass, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Get ride history for the logged-in rider
    $rider_id = $_SESSION['rider_id'];
    $historyQuery = "
        SELECT r.* 
        FROM rides r 
        WHERE r.rider_id = $rider_id 
        ORDER BY r.created_at DESC
    ";
    $historyResult = $conn->query($historyQuery);
    $history = [];
    
    if ($historyResult) {
        while ($row = $historyResult->fetch_assoc()) {
            $history[] = $row;
        }
    }

    // Close connection
    $conn->close();
} catch (Exception $e) {
    error_log($e->getMessage());
    $history = [];
}

include('navbar.php');
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rides History - RideShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Padauk:wght@400;700&display=swap" rel="stylesheet">
    <style>
         :root {
            --primary-color: #1fbad6;
            --secondary-color: #2d3436;
            --accent-color: #00b894;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: 'Padauk', 'Noto Sans Myanmar', sans-serif;
            background-color: var(--light-bg);
            color: var(--secondary-color);
            padding-top: 80px;
        }

        .history-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .history-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .history-card:hover {
            transform: translateY(-5px);
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .history-action {
            font-weight: bold;
            font-size: 1.1rem;
            color: var(--primary-color);
        }

        .history-date {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .history-details {
            color: var(--secondary-color);
            line-height: 1.6;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }

        .action-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-right: 10px;
        }

        .account-created {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .ride-requested {
            background-color: #e3f2fd;
            color: #1565c0;
        }

        .ride-completed {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .ride-cancelled {
            background-color: #ffebee;
            color: #c62828;
        }

        .page-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--primary-color);
        }
        .cancel-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            cursor: pointer;
        }
        
        .cancel-btn:hover {
            background-color: #c0392b;
        }
        
        .action-buttons {
            margin-top: 10px;
        }
        
        .cancel-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            cursor: pointer;
        }
        
        .cancel-btn:hover {
            background-color: #c0392b;
        }
        
        .action-buttons {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="history-container">
            <h2 class="page-title">ခရီးစဉ်မှတ်တမ်း</h2>
            
            <?php if (count($history) > 0): ?>
                <?php foreach ($history as $item): ?>
                    <div class="history-card">
                        <div class="history-header">
                            <div class="history-action">
                                <?php 
                                $badgeClass = '';
                                $actionText = '';
                                
                                switch($item['status']) {
                                    case 'pending':
                                        $badgeClass = 'ride-requested';
                                        $actionText = 'စောင့်ဆိုင်းနေ';
                                        break;
                                    case 'assigned':
                                        $badgeClass = 'ride-requested';
                                        $actionText = 'ယာဉ်မောင်းလက်ခံပြီး';
                                        break;
                                    case 'in_progress':
                                        $badgeClass = 'ride-completed';
                                        $actionText = 'ခရီးစဉ်ဆောင်ရွက်နေ';
                                        break;
                                    case 'completed':
                                        $badgeClass = 'ride-completed';
                                        $actionText = 'ပြီးဆုံးပြီ';
                                        break;
                                    case 'cancelled':
                                        $badgeClass = 'ride-cancelled';
                                        $actionText = 'ပယ်ဖျက်ပြီး';
                                        break;
                                    default:
                                        $badgeClass = '';
                                        $actionText = $item['status'];
                                }
                                ?>
                                <span class="action-badge <?php echo $badgeClass; ?>"><?php echo $actionText; ?></span>
                            </div>
                            <div class="history-date">
                                <i class="far fa-clock me-1"></i>
                                <?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?>
                            </div>
                        </div>
                        <div class="history-details">
                            <p><strong>စတင်မည့်နေရာ:</strong> <?php echo $item['pickup_location']; ?></p>
                            <p><strong>ဆုံးမည့်နေရာ:</strong> <?php echo $item['dropoff_location']; ?></p>
                            <p><strong>ကုန်ကျငွေ:</strong> <?php echo $item['fare']; ?> Ks</p>
                            <p><strong>ယာဉ်အမျိုးအစား:</strong> <?php echo $item['vehicle_type']; ?></p>
                            <p><strong>ခရီးသည်အရေအတွက်:</strong> <?php echo $item['passengers']; ?></p>
                        </div>
                        
                        <?php if ($item['status'] == 'pending'): ?>
                        <div class="action-buttons">
                            <button class="cancel-btn" data-ride-id="<?php echo $item['id']; ?>">
                                <i class="fas fa-times"></i> ခရီးစဉ်ပယ်ဖျက်ရန်
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h4>မှတ်တမ်းများ မရှိသေးပါ</h4>
                    <p>သင့်ခရီးစဉ်မှတ်တမ်းများကို ဤနေရာတွင် တွေ့ရှိရမည်ဖြစ်ပါသည်</p>
                    <a href="rider_dashboard.php" class="btn btn-primary mt-3">
                        <i class="fas fa-car me-2"></i> ကားခေါ်ရန်
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add event listeners to cancel buttons
        document.querySelectorAll('.cancel-btn').forEach(button => {
            button.addEventListener('click', function() {
                const rideId = this.getAttribute('data-ride-id');
                if (confirm('ဤခရီးစဉ်ကို ပယ်ဖျက်လိုပါသလား?')) {
                    cancelRide(rideId);
                }
            });
        });

        function cancelRide(rideId) {
            fetch('cancel_ride.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ride_id=' + rideId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('ခရီးစဉ်ပယ်ဖျက်ရာတွင် ပြဿနာတစ်ခုဖြစ်နေပါသည်: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('ခရီးစဉ်ပယ်ဖျက်ရာတွင် ပြဿနာတစ်ခုဖြစ်နေပါသည်။');
            });
        }
    </script>
</body>
</html>

      