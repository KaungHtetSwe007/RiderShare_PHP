<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rideshare"; // your DB name

$connection = new mysqli($servername, $username, $password, $dbname);
if ($connection->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $connection->connect_error]));
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM drivers WHERE id = $id AND status = 'pending' LIMIT 3";
    $result = $connection->query($sql);

    if ($result && $result->num_rows > 0) {
        $driver = $result->fetch_assoc();
        echo json_encode($driver);
    } else {
        echo json_encode(["error" => "Driver not found"]);
    }
} else {
    echo json_encode(["error" => "No ID provided"]);
}
