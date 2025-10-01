<?php
$connection = new mysqli("localhost", "root", "", "rideshare");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id > 0){
    $sql = "SELECT r.*, d.name as driver_name, d.phone as driver_phone, 
                   rider.name as rider_name, rider.phone as rider_phone
            FROM rides r
            LEFT JOIN drivers d ON r.driver_id = d.id
            LEFT JOIN riders rider ON r.rider_id = rider.id
            WHERE r.id = $id
            LIMIT 1";
    $result = $connection->query($sql);

    if($ride = $result->fetch_assoc()){
        echo '<div style="font-family: Arial, sans-serif; color: #333;">';

        // Ride Info Card
        echo '<div style="background: #fdfdfd; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <h3 style="margin-bottom: 15px; color: #1a73e8;">ခရီးစဉ် အချက်အလက်</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div><strong>ခရီးစဉ် ID:</strong> #'.$ride['id'].'</div>
                    <div><strong>အခြေအနေ:</strong> '.ucwords($ride['status']).'</div>
                    <div><strong>စတင်ရန် နေရာ:</strong> '.$ride['pickup_location'].'</div>
                    <div><strong>ဆုံးရန် နေရာ:</strong> '.$ride['dropoff_location'].'</div>
                    <div><strong>ခရီးစဉ်ကုန်ကျစရိတ်:</strong> '.$ride['fare'].' Ks</div>
                    <div><strong>အကွာအဝေး:</strong> '.$ride['distance'].' km</div>
                </div>
              </div>';

        // Rider Info Card
        echo '<div style="background: #f1f8ff; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 15px; color: #34a853;">ခရီးသည် အချက်အလက်</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><strong>အမည်:</strong> '.$ride['rider_name'].'</div>
                    <div><strong>ဖုန်းနံပါတ်:</strong> '.$ride['rider_phone'].'</div>
                </div>
              </div>';

        // Driver Info Card
        echo '<div style="background: #fff4e5; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 15px; color: #fbbc05;">ယာဉ်မောင်း အချက်အလက်</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><strong>အမည်:</strong> '.($ride['driver_name'] ?? 'မရှိသေး').'</div>
                    <div><strong>ဖုန်းနံပါတ်:</strong> '.($ride['driver_phone'] ?? 'မရှိသေး').'</div>
                </div>
              </div>';

        echo '</div>'; // End wrapper

    } else {
        echo '<p style="color: red; font-weight: bold;">ခရီးစဉ် မတွေ့ပါ</p>';
    }
} else {
    echo '<p style="color: red; font-weight: bold;">Ride ID မမှန်ပါ</p>';
}
?>
