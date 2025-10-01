<?php
// get_ride_details.php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rideshare');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $ride_id = $conn->real_escape_string($_GET['id']);
    
    $query = "
        SELECT r.*, d.name as driver_name, d.phone as driver_phone, 
               rider.name as rider_name, rider.phone as rider_phone,
               v.registration_no, v.vehicle_model
        FROM rides r
        LEFT JOIN drivers d ON r.driver_id = d.id
        LEFT JOIN riders rider ON r.rider_id = rider.id
        LEFT JOIN vehicles v ON d.id = v.driver_id
        WHERE r.id = $ride_id
    ";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $ride = $result->fetch_assoc();
        
        // Determine status text
        switch ($ride['status']) {
            case 'completed': $status_text = 'ပြီးစီး'; break;
            case 'cancelled': $status_text = 'ပယ်ဖျက်'; break;
            case 'pending': $status_text = 'မစတင်သေး'; break;
            case 'assigned': $status_text = 'တာဝန်ပေးပြီး'; break;
            case 'in_progress': $status_text = 'ဆောင်ရွက်ဆဲ'; break;
            default: $status_text = $ride['status'];
        }
        
        echo '
        <div class="ride-details-grid">
            <div class="ride-info">
                <h3>ခရီးစဉ် အချက်အလက်</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">ခရီးစဉ် ID</div>
                        <div class="info-value">#' . $ride['id'] . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">အခြေအနေ</div>
                        <div class="info-value">' . $status_text . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">စတင်ရန် နေရာ</div>
                        <div class="info-value">' . htmlspecialchars($ride['pickup_location']) . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ဆုံးရန် နေရာ</div>
                        <div class="info-value">' . htmlspecialchars($ride['dropoff_location']) . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ခရီးစဉ်ကုန်ကျစရိတ်</div>
                        <div class="info-value">' . number_format($ride['fare'], 2) . ' Ks</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">အကွာအဝေး</div>
                        <div class="info-value">' . number_format($ride['distance'], 2) . ' km</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ကြာချိန်</div>
                        <div class="info-value">' . $ride['duration'] . ' မိနစ်</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ယာဉ်အမျိုးအစား</div>
                        <div class="info-value">' . htmlspecialchars($ride['vehicle_type']) . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ခရီးသည် အရေအတွက်</div>
                        <div class="info-value">' . $ride['passengers'] . ' ဦး</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ခရီးစဉ်ရက်စွဲ</div>
                        <div class="info-value">' . date('d/m/Y H:i', strtotime($ride['ride_date'])) . '</div>
                    </div>
                </div>
            </div>
            
            <div class="participants-info">
                <h3>ခရီးသည် အချက်အလက်</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">အမည်</div>
                        <div class="info-value">' . htmlspecialchars($ride['rider_name']) . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ဖုန်းနံပါတ်</div>
                        <div class="info-value">' . htmlspecialchars($ride['rider_phone']) . '</div>
                    </div>
                </div>';
                
        if (!empty($ride['driver_name'])) {
            echo '
                <h3>ယာဉ်မောင်း အချက်အလက်</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">အမည်</div>
                        <div class="info-value">' . htmlspecialchars($ride['driver_name']) . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ဖုန်းနံပါတ်</div>
                        <div class="info-value">' . htmlspecialchars($ride['driver_phone']) . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">ယာဉ်</div>
                        <div class="info-value">' . htmlspecialchars($ride['vehicle_model']) . ' (' . htmlspecialchars($ride['registration_no']) . ')</div>
                    </div>
                </div>';
        }
        
        echo '
            </div>
        </div>';
    } else {
        echo '<p>ခရီးစဉ် အချက်အလက်များ မတွေ့ရှိပါ</p>';
    }
} else {
    echo '<p>မှားယွင်းသော တောင်းဆိုချက်</p>';
}
?>
