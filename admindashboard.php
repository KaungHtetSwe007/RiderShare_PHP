<?php
// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rideshare');
// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Create stored procedure if it doesn't exist
$createProcedureSQL = "
CREATE PROCEDURE IF NOT EXISTS reject_driver(IN driver_id INT)
BEGIN
    -- Start transaction
    START TRANSACTION;
    
    -- Delete related records from driver_documents table
    DELETE FROM driver_documents WHERE driver_id = driver_id;
    
    -- Delete related records from vehicles table
    DELETE FROM vehicles WHERE driver_id = driver_id;
    
    -- Delete the driver record
    DELETE FROM drivers WHERE id = driver_id;
    
    -- Commit transaction
    COMMIT;
END;
";

if (!$conn->query($createProcedureSQL)) {
    // Error creating procedure, but we'll continue anyway
    error_log("Error creating stored procedure: " . $conn->error);
}

// Get total riders
$total_riders = 0;
$riders_result = $conn->query("SELECT COUNT(*) as total FROM riders");
if ($riders_result && $riders_result->num_rows > 0) {
    $row = $riders_result->fetch_assoc();
    $total_riders = $row['total'];
}

// Get total drivers
$total_drivers = 0;
$drivers_result = $conn->query("SELECT COUNT(*) as total FROM drivers");
if ($drivers_result && $drivers_result->num_rows > 0) {
    $row = $drivers_result->fetch_assoc();
    $total_drivers = $row['total'];
}

// Get pending approvals
$pending_approvals = 0;
$pending_result = $conn->query("SELECT COUNT(*) as total FROM drivers WHERE status = 'pending'");
if ($pending_result && $pending_result->num_rows > 0) {
    $row = $pending_result->fetch_assoc();
    $pending_approvals = $row['total'];
}

// Get pending drivers with vehicle info
$pending_drivers = [];
$pending_drivers_query = "
    SELECT d.id, d.phone, d.name, d.created_at, d.profile_picture, v.registration_no, v.vehicle_model 
    FROM drivers d
    LEFT JOIN vehicles v ON d.id = v.driver_id
    WHERE d.status = 'pending'
";
$pending_drivers_result = $conn->query($pending_drivers_query);
if ($pending_drivers_result && $pending_drivers_result->num_rows > 0) {
    while ($row = $pending_drivers_result->fetch_assoc()) {
        $pending_drivers[] = $row;
    }
}

// Get all riders
$riders = [];
$riders_query = "SELECT * FROM riders ORDER BY created_at DESC";
$riders_result = $conn->query($riders_query);
if ($riders_result && $riders_result->num_rows > 0) {
    while ($row = $riders_result->fetch_assoc()) {
        $riders[] = $row;
    }
}

// Get all drivers
$drivers = [];
$drivers_query = "SELECT d.*, v.registration_no, v.vehicle_model 
                  FROM drivers d
                  LEFT JOIN vehicles v ON d.id = v.driver_id";
$drivers_result = $conn->query($drivers_query);
if ($drivers_result && $drivers_result->num_rows > 0) {
    while ($row = $drivers_result->fetch_assoc()) {
        $drivers[] = $row;
    }
}

// Calculate total profit (20% of all completed rides)
$total_profit = 0;
$profit_query = "SELECT SUM(fare) as total_income FROM rides WHERE status = 'completed'";
$profit_result = $conn->query($profit_query);
if ($profit_result && $profit_result->num_rows > 0) {
    $row = $profit_result->fetch_assoc();
    $total_profit = $row['total_income'] * 0.2; // 20% platform commission
}

// Get daily profit data (last 7 days)
$daily_profit = [];
$daily_query = "
    SELECT 
        DATE(ride_date) as date, 
        SUM(fare) * 0.2 as profit 
    FROM rides 
    WHERE status = 'completed' 
    AND ride_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(ride_date)
    ORDER BY date DESC
";
$daily_result = $conn->query($daily_query);
if ($daily_result && $daily_result->num_rows > 0) {
    while ($row = $daily_result->fetch_assoc()) {
        $daily_profit[] = $row;
    }
}

// Get monthly profit data (last 6 months)
$monthly_profit = [];
$monthly_query = "
    SELECT 
        YEAR(ride_date) as year, 
        MONTH(ride_date) as month, 
        SUM(fare) * 0.2 as profit 
    FROM rides 
    WHERE status = 'completed' 
    AND ride_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY YEAR(ride_date), MONTH(ride_date)
    ORDER BY year DESC, month DESC
";
$monthly_result = $conn->query($monthly_query);
if ($monthly_result && $monthly_result->num_rows > 0) {
    while ($row = $monthly_result->fetch_assoc()) {
        $monthly_profit[] = $row;
    }
}

// Get yearly profit data
$yearly_profit = [];
$yearly_query = "
    SELECT 
        YEAR(ride_date) as year, 
        SUM(fare) * 0.2 as profit 
    FROM rides 
    WHERE status = 'completed'
    GROUP BY YEAR(ride_date)
    ORDER BY year DESC
";
$yearly_result = $conn->query($yearly_query);
if ($yearly_result && $yearly_result->num_rows > 0) {
    while ($row = $yearly_result->fetch_assoc()) {
        $yearly_profit[] = $row;
    }
}

// Handle driver status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['driver_id']) && isset($_POST['status'])) {
        $driver_id = $conn->real_escape_string($_POST['driver_id']);
        $status = $conn->real_escape_string($_POST['status']);

        if ($status == 'rejected') {
            // Use the stored procedure to completely remove the driver
            $delete_query = "CALL reject_driver($driver_id)";
            if ($conn->query($delete_query)) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $error = "Error rejecting driver: " . $conn->error;
            }
        } else {
            $update_query = "UPDATE drivers SET status = '$status' WHERE id = $driver_id";
            if ($conn->query($update_query)) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $error = "Error updating driver status: " . $conn->error;
            }
        }
    }
}

// Get driver details for modal
$driver_details = [];
if (isset($_GET['driver_id'])) {
    $driver_id = $conn->real_escape_string($_GET['driver_id']);
    $driver_query = "SELECT d.*, v.registration_no, v.vehicle_model 
                     FROM drivers d 
                     LEFT JOIN vehicles v ON d.id = v.driver_id 
                     WHERE d.id = $driver_id";
    $driver_result = $conn->query($driver_query);
    if ($driver_result && $driver_result->num_rows > 0) {
        $driver_details = $driver_result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="my">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RideShare စီမံခန့်ခွဲမှု</title>
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
            --sidebar-width: 260px;
            --topbar-height: 70px;
        }

        body {
            background-color: #f0f2f5;
            display: flex;
            min-height: 100vh;
        }

        .driver-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--secondary) 0%, #1a2530 100%);
            color: white;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo i {
            font-size: 28px;
            margin-right: 12px;
            color: var(--primary);
        }

        .logo h1 {
            font-size: 18px;
            font-weight: 600;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .menu-item.active {
            background: var(--primary);
        }

        .menu-item i {
            font-size: 18px;
            margin-right: 12px;
            width: 24px;
            text-align: center;
        }

        .menu-item span {
            font-size: 16px;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding-top: var(--topbar-height);
        }

        .top-bar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 90;
        }

        .search-bar {
            position: relative;
            width: 300px;
        }

        .search-bar i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .search-bar input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid var(--light-gray);
            border-radius: 30px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(31, 186, 214, 0.2);
        }

        .admin-actions {
            display: flex;
            align-items: center;
        }

        .notification {
            position: relative;
            margin-right: 25px;
            font-size: 18px;
            color: var(--dark);
            cursor: pointer;
        }

        .badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-profile {
            display: flex;
            align-items: center;
        }

        .admin-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 2px solid var(--primary);
        }

        .admin-info h3 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .admin-info p {
            font-size: 13px;
            color: var(--gray);
        }

        /* Dashboard Content */
        .content-section {
            padding: 25px;
            display: none;
        }

        #dashboard-section {
            display: block;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .summary-card {
            display: flex;
            flex-direction: column;
        }

        .summary-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 24px;
        }

        .summary-card.riders .icon {
            background: rgba(31, 186, 214, 0.1);
            color: var(--primary);
        }

        .summary-card.drivers .icon {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .summary-card.revenue .icon {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }

        .summary-card.pending .icon {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }

        .summary-card h2 {
            font-size: 16px;
            color: var(--gray);
            margin-bottom: 10px;
        }

        .summary-card .value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .change {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: var(--success);
        }

        .change.down {
            color: var(--danger);
        }

        .change i {
            margin-right: 5px;
        }

        /* Charts */
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-header h2 {
            font-size: 18px;
            font-weight: 600;
        }

        .filter {
            display: flex;
            background: var(--light-gray);
            border-radius: 30px;
            padding: 3px;
        }

        .filter button {
            padding: 5px 15px;
            border: none;
            background: none;
            border-radius: 30px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter button.active {
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .chart {
            height: 300px;
            position: relative;
        }

        /* Tables */
        .tables-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-header h2 {
            font-size: 18px;
            font-weight: 600;
        }

        .table-header a {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .table-header a:hover {
            text-decoration: underline;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: var(--light-gray);
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            color: var(--dark);
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--light-gray);
            font-size: 14px;
        }

        tr:hover td {
            background: rgba(31, 186, 214, 0.05);
        }

        .driver-info {
            display: flex;
            align-items: center;
        }

        .driver-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .driver-name {
            font-weight: 600;
        }

        .driver-phone {
            font-size: 13px;
            color: var(--gray);
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status.pending {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning);
        }

        .status.approved {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success);
        }

        .status.rejected {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background: none;
            margin: 0 3px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .action-btn:hover {
            background: var(--light-gray);
        }

        .approve-btn {
            color: var(--success);
        }

        .reject-btn {
            color: var(--danger);
        }

        .view-btn {
            color: var(--primary);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(3px);
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 22px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray);
        }

        .modal-body {
            padding: 20px;
        }

        .driver-details-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
        }

        .driver-profile {
            text-align: center;
            padding: 20px;
            border-right: 1px solid var(--light-gray);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .driver-profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid var(--primary);
        }

        .driver-profile h3 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .driver-profile p {
            color: var(--gray);
            margin-bottom: 15px;
        }

        .driver-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
        }

        .driver-details h3 {
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--light-gray);
        }

        .driver-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-item {
            margin-bottom: 10px;
        }

        .info-label {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 3px;
        }

        .info-value {
            font-weight: 500;
        }

        .documents-section h3 {
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--light-gray);
        }

        .documents-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .document-item {
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .document-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .document-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid var(--light-gray);
        }

        .document-name {
            padding: 10px;
            text-align: center;
            font-size: 14px;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--light-gray);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-approve {
            background: var(--success);
            color: white;
        }

        .btn-reject {
            background: var(--danger);
            color: white;
        }

        .btn-close {
            background: var(--light-gray);
            color: var(--dark);
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
            opacity: 0;
        }

        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        .delay-3 {
            animation-delay: 0.3s;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 1200px) {

            .charts-row,
            .tables-row {
                grid-template-columns: 1fr;
            }

            .driver-details-grid {
                grid-template-columns: 1fr;
            }

            .driver-profile {
                border-right: none;
                border-bottom: 1px solid var(--light-gray);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .sidebar .logo h1,
            .menu-item span {
                display: none;
            }

            .logo {
                justify-content: center;
            }

            .menu-item {
                justify-content: center;
                padding: 15px;
            }

            .menu-item i {
                margin-right: 0;
            }

            .main-content {
                margin-left: 70px;
            }

            .documents-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .driver-info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .top-bar {
                padding: 0 15px;
            }

            .search-bar {
                width: 200px;
            }

            .admin-info {
                display: none;
            }

            .documents-grid {
                grid-template-columns: 1fr;
            }
        }


        /* Fix for analytics/rides section */
        #analytics-section {
            padding: 25px;
            width: 100%;
            box-sizing: border-box;
            overflow-y: auto;
            margin-top: 0;
        }

        #analytics-section .dashboard-grid {
            margin-bottom: 30px;
        }

        #analytics-section .table-container {
            margin-top: 20px;
            width: 100%;
        }

        /* Ensure proper spacing for the filter controls */
        #analytics-section .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        #status-filter, #date-filter {
            padding: 8px 12px;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            background: white;
            font-family: 'Noto Sans Myanmar', 'Padauk', sans-serif;
        }

        /* Profit section specific styles */
        .profit-period-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .profit-period-btn {
            padding: 8px 16px;
            border: 1px solid var(--primary);
            background: white;
            color: var(--primary);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .profit-period-btn.active {
            background: var(--primary);
            color: white;
        }

        .profit-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .profit-table th, .profit-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .profit-table th {
            background-color: var(--light-gray);
            font-weight: 600;
        }

        .profit-table tr:hover {
            background-color: rgba(31, 186, 214, 0.05);
        }

        .profit-amount {
            font-weight: 600;
            color: var(--success);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #analytics-section .table-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            #analytics-section .table-header > div {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                width: 100%;
            }
            
            #status-filter, #date-filter {
                flex: 1;
                min-width: 140px;
            }

            .profit-period-selector {
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-car"></i>
            <h1>RideShare စီမံခန့်ခွဲမှု</h1>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item active" data-page="dashboard">
                <i class="fas fa-home"></i>
                <span>ဒက်ရှ်ဘုတ်</span>
            </div>
            <div class="menu-item" data-page="riders">
                <i class="fas fa-users"></i>
                <span>ခရီးသည်</span>
            </div>
            <div class="menu-item" data-page="drivers">
                <i class="fas fa-car"></i>
                <span>ယာဉ်မောင်း</span>
            </div>
            <div class="menu-item" data-page="analytics">
                <i class="fas fa-chart-line"></i>
                <span>ခရီးစဉ်များ</span>
            </div>
            <div class="menu-item" data-page="profit">
                <i class="fas fa-money-bill-wave"></i>
                <span>အမြတ်ငွေ</span>
            </div>
            <div class="menu-item" data-page="logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>အကောင့်မှ ထွက်မည်</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="ရှာဖွေရန်...">
            </div>
            <div class="admin-actions">
                <div class="notification">
                    <i class="fas fa-bell"></i>
                    <span class="badge"><?php echo $pending_approvals; ?></span>
                </div>
                <!-- <div class="admin-profile">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Admin">
                    <div class="admin-info">
                        <h3>ဂျွန် အက်မင်</h3>
                        <p>စူပါ အက်မင်</p>
                    </div>
                </div> -->
            </div>
        </div>

        <!-- Dashboard Content -->
        <div id="dashboard-section" class="content-section">
            <!-- Dashboard Summary -->
            <div class="dashboard-grid">
                <div class="card summary-card riders fade-in">
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h2>စုစုပေါင်း ခရီးသည်</h2>
                    <div class="value" id="total-riders"><?php echo $total_riders; ?></div>
                    <div class="change">
                        <i class="fas fa-arrow-up"></i>
                        <span id="riders-change">12% တိုး</span>
                    </div>
                </div>

                <div class="card summary-card drivers fade-in delay-1">
                    <div class="icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <h2>စုစုပေါင်း ယာဉ်မောင်း</h2>
                    <div class="value" id="total-drivers"><?php echo $total_drivers; ?></div>
                    <div class="change">
                        <i class="fas fa-arrow-up"></i>
                        <span id="drivers-change">8% တိုး</span>
                    </div>
                </div>

                <div class="card summary-card revenue fade-in delay-2">
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h2>စုစုပေါင်း အမြတ်</h2>
                    <div class="value" id="total-revenue">Ks<?php echo number_format($total_profit, 2); ?></div>
                    <div class="change">
                        <i class="fas fa-arrow-up"></i>
                        <span id="revenue-change">18% တိုး</span>
                    </div>
                </div>

                <div class="card summary-card pending fade-in delay-3">
                    <div class="icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <h2>အတည်ပြုရန် ကျန်ရှိ</h2>
                    <div class="value" id="pending-approvals"><?php echo $pending_approvals; ?></div>
                    <div class="change down">
                        <i class="fas fa-arrow-down"></i>
                        <span id="pending-change">5% လျော့</span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-row">
                <div class="chart-container fade-in">
                    <div class="chart-header">
                        <h2>အမြတ်ငွေ ခြုံငုံကြည့်ရှုခြင်း</h2>
                        <div class="filter">
                            <button class="active" data-period="week">အပတ်စဉ်</button>
                            <button data-period="month">လစဉ်</button>
                            <button data-period="year">နှစ်စဉ်</button>
                        </div>
                    </div>
                    <div class="chart">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                <div class="chart-container fade-in delay-1">
                    <div class="chart-header">
                        <h2>အသုံးပြုသူများ ဖြန့်ဖြူးမှု</h2>
                        <div class="filter">
                            <button class="active" data-year="2023">၂၀၂၃</button>
                            <button data-year="2024">၂၀၂၄</button>
                        </div>
                    </div>
                    <div class="chart">
                        <canvas id="userChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tables Section -->
            <div class="tables-row">
                <div class="table-container fade-in">
                    <div class="table-header">
                        <h2>ယာဉ်မောင်း အတည်ပြုမှုများ (<?php echo count($pending_drivers); ?>)</h2>
                        <a href="#" id="view-all-drivers">အားလုံးကြည့်ရန်</a>
                    </div>
                    <div class="table-responsive">
                        <table id="pending-drivers-table">
                            <thead>
                                <tr>
                                    <th>ယာဉ်မောင်း</th>
                                    <th>ယာဉ်</th>
                                    <th>တင်သွင်းခဲ့သည့်ရက်</th>
                                    <th>အခြေအနေ</th>
                                    <th>လုပ်ဆောင်ချက်</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($pending_drivers) > 0): ?>
                                    <?php foreach ($pending_drivers as $driver): ?>
                                        <?php
                                        $created_at = new DateTime($driver['created_at']);
                                        $now = new DateTime();
                                        $interval = $now->diff($created_at);

                                        if ($interval->d == 0) {
                                            $time_ago = "ယနေ့";
                                        } elseif ($interval->d == 1) {
                                            $time_ago = "မနေ့က";
                                        } else {
                                            $time_ago = $interval->d . " ရက် အကြာက";
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="driver-info">
                                                    <?php if (!empty($driver['profile_picture'])): ?>
                                                        <img src="<?php echo htmlspecialchars($driver['profile_picture']); ?>"
                                                            alt="<?php echo htmlspecialchars($driver['name']); ?>"
                                                            class="driver-avatar">
                                                    <?php else: ?>
                                                        <img src="uploads/drivers/default.png"
                                                            alt="Default Driver"
                                                            class="driver-avatar">
                                                    <?php endif; ?>

                                                    <div>
                                                        <div class="driver-name"><?php echo htmlspecialchars($driver['name']); ?></div>
                                                        <div class="driver-phone"><?php echo htmlspecialchars($driver['phone']); ?></div>
                                                    </div>
                                                </div>

                                            </td>
                                            <td><?php echo htmlspecialchars($driver['vehicle_model']); ?> (<?php echo htmlspecialchars($driver['registration_no']); ?>)</td>
                                            <td><?php echo $time_ago; ?></td>
                                            <td><span class="status pending">ဆိုင်းငံ့ထား</span></td>
                                            <td>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="driver_id" value="<?php echo $driver['id']; ?>">
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="action-btn approve-btn"><i class="fas fa-check"></i></button>
                                                </form>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="driver_id" value="<?php echo $driver['id']; ?>">
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="action-btn reject-btn"><i class="fas fa-times"></i></button>
                                                </form>
                                                <button class="action-btn view-btn" data-id="<?php echo $driver['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center;">ဆိုင်းငံ့ထားသော ယာဉ်မောင်းများ မရှိပါ</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "rideshare";

                $connection = new mysqli($servername, $username, $password, $dbname);
                if ($connection->connect_error) {
                    die("Connection failed: " . $connection->connect_error);
                }

                $sql = "SELECT r.id, r.rider_id, r.rider_name, r.driver_id, r.pickup_location, r.dropoff_location,
               r.fare, r.distance, r.duration, r.vehicle_type, r.passengers, r.status, r.ride_date,
               d.name AS driver_name
        FROM rides r
        LEFT JOIN drivers d ON r.driver_id = d.id
        ORDER BY r.ride_date DESC
        LIMIT 3";

                $result = $connection->query($sql);
                if (!$result) {
                    die("Query failed: " . $connection->error);
                }
                ?>
                <div class="table-container fade-in delay-1">
                    <div class="table-header">
                        <h2>လတ်တလော ခရီးစဉ်များ</h2>
                        <a href="#" id="view-all-rides">အားလုံးကြည့်ရန်</a>
                    </div>
                    <div class="table-responsive">
                        <table id="recent-rides-table">
                            <thead>
                                <tr>
                                    <th>ခရီးသည်</th>
                                    <th>ယာဉ်မောင်း</th>
                                    <th>ရက်စွဲ</th>
                                    <th>ပမာဏ</th>
                                    <th>အခြေအနေ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($ride = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ride['rider_name']); ?></td>
                                            <td><?php echo htmlspecialchars($ride['driver_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo date("F j, Y", strtotime($ride['ride_date'])); ?></td>
                                            <td><?php echo number_format($ride['fare'], 2); ?> Ks</td>
                                            <td>
                                                <?php
                                                switch ($ride['status']) {
                                                    case 'completed':
                                                        $status_class = 'approved';
                                                        $status_text = 'ပြီးစီး';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'rejected';
                                                        $status_text = 'ပယ်ဖျက်';
                                                        break;
                                                    case 'pending':
                                                        $status_class = 'pending';
                                                        $status_text = 'မစတင်သေး';
                                                        break;
                                                    default:
                                                        $status_class = '';
                                                        $status_text = $ride['status'];
                                                }
                                                ?>
                                                <span class="status <?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">ခရီးစဉ်မရှိသေးပါ။</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riders Section -->
        <div id="riders-section" class="content-section">
            <div class="table-container">
                <div class="table-header">
                    <h2>ခရီးသည်များ (<?php echo $total_riders; ?>)</h2>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>အမည်</th>
                                <th>ဖုန်းနံပါတ်</th>
                                <th>အကောင့်ဖွင့်သည့်နေ့</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($riders) > 0): ?>
                                <?php foreach ($riders as $rider): ?>
                                    <?php
                                    $created_at = new DateTime($rider['created_at']);
                                    $formatted_date = $created_at->format('d/m/Y');
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="driver-info">
                                                <?php if (!empty($rider['profile_picture'])): ?>
                                                    <img src="<?php echo htmlspecialchars($rider['profile_picture']); ?>" 
                                                         alt="<?php echo htmlspecialchars($rider['name']); ?>" 
                                                         class="driver-avatar">
                                                <?php else: ?>
                                                    <img src="uploads/riders/default.png" 
                                                         alt="Default Rider" 
                                                         class="driver-avatar">
                                                <?php endif; ?>
                                                <div>
                                                    <div class="driver-name"><?php echo htmlspecialchars($rider['name']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($rider['phone']); ?></td>
                                        <td><?php echo $formatted_date; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center;">ခရီးသည်များ မရှိသေးပါ</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Drivers Section -->
        <div id="drivers-section" class="content-section">
            <div class="table-container">
                <div class="table-header">
                    <h2>ယာဉ်မောင်းများ (<?php echo $total_drivers; ?>)</h2>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ယာဉ်မောင်း</th>
                                <th>ယာဉ်</th>
                                <th>အမျိုးအစား</th>
                                <th>အခြေအနေ</th>
                                <th>တင်သွင်းခဲ့သည့်ရက်</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($drivers) > 0): ?>
                                <?php foreach ($drivers as $driver): ?>
                                    <?php
                                    $created_at = new DateTime($driver['created_at']);
                                    $formatted_date = $created_at->format('d/m/Y');
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="driver-info">
                                                <?php if (!empty($driver['profile_picture'])): ?>
                                                    <img src="<?php echo htmlspecialchars($driver['profile_picture']); ?>" 
                                                         alt="<?php echo htmlspecialchars($driver['name']); ?>" 
                                                         class="driver-avatar">
                                                <?php else: ?>
                                                    <img src="uploads/drivers/default.png" 
                                                         alt="Default Driver" 
                                                         class="driver-avatar">
                                                <?php endif; ?>
                                                <div>
                                                    <div class="driver-name"><?php echo htmlspecialchars($driver['name']); ?></div>
                                                    <div class="driver-phone"><?php echo htmlspecialchars($driver['phone']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($driver['vehicle_model']); ?> (<?php echo htmlspecialchars($driver['registration_no']); ?>)</td>
                                        <td><?php echo htmlspecialchars($driver['vehicle_type']); ?></td>
                                        <td>
                                            <?php if ($driver['status'] == 'pending'): ?>
                                                <span class="status pending">ဆိုင်းငံ့ထား</span>
                                            <?php elseif ($driver['status'] == 'approved'): ?>
                                                <span class="status approved">အတည်ပြုပြီး</span>
                                            <?php else: ?>
                                                <span class="status rejected">ပယ်ဖျက်ပြီး</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $formatted_date; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">ယာဉ်မောင်းများ မရှိသေးပါ</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Profit Analytics Section -->
        <div id="profit-section" class="content-section">
            <div class="dashboard-grid">
                <div class="card summary-card fade-in">
                    <div class="icon" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h2>စုစုပေါင်း အမြတ်ငွေ</h2>
                    <div class="value">Ks<?php echo number_format($total_profit, 2); ?></div>
                    <div class="change">
                        <i class="fas fa-arrow-up"></i>
                        <span>18% တိုး</span>
                    </div>
                </div>

                <div class="card summary-card fade-in delay-1">
                    <div class="icon" style="background: rgba(52, 152, 219, 0.1); color: #3498db;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <h2>ယနေ့ အမြတ်ငွေ</h2>
                    <div class="value">
                        <?php
                        $today = date('Y-m-d');
                        $today_profit = 0;
                        foreach ($daily_profit as $profit) {
                            if ($profit['date'] == $today) {
                                $today_profit = $profit['profit'];
                                break;
                            }
                        }
                        echo 'Ks' . number_format($today_profit, 2);
                        ?>
                    </div>
                    <div class="change">
                        <i class="fas fa-arrow-up"></i>
                        <span>5% တိုး</span>
                    </div>
                </div>

                <div class="card summary-card fade-in delay-2">
                    <div class="icon" style="background: rgba(155, 89, 182, 0.1); color: #9b59b6;">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <h2>ဒီအပတ် အမြတ်ငွေ</h2>
                    <div class="value">
                        <?php
                        $week_profit = 0;
                        foreach ($daily_profit as $profit) {
                            $week_profit += $profit['profit'];
                        }
                        echo 'Ks' . number_format($week_profit, 2);
                        ?>
                    </div>
                    <div class="change">
                        <i class="fas fa-arrow-up"></i>
                        <span>12% တိုး</span>
                    </div>
                </div>

                <div class="card summary-card fade-in delay-3">
                    <div class="icon" style="background: rgba(243, 156, 18, 0.1); color: #f39c12;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h2>ဒီလ အမြတ်ငွေ</h2>
                    <div class="value">
                        <?php
                        $current_month = date('n');
                        $current_year = date('Y');
                        $month_profit = 0;
                        foreach ($monthly_profit as $profit) {
                            if ($profit['year'] == $current_year && $profit['month'] == $current_month) {
                                $month_profit = $profit['profit'];
                                break;
                            }
                        }
                        echo 'Ks' . number_format($month_profit, 2);
                        ?>
                    </div>
                    <div class="change">
                        <i class="fas fa-arrow-up"></i>
                        <span>8% တိုး</span>
                    </div>
                </div>
            </div>

            <div class="chart-container">
                <div class="chart-header">
                    <h2>အမြတ်ငွေ ခွဲခြမ်းစိတ်ဖြာမှု</h2>
                    <div class="filter">
                        <button class="active" data-period="daily">နေ့စဉ်</button>
                        <button data-period="monthly">လစဉ်</button>
                        <button data-period="yearly">နှစ်စဉ်</button>
                    </div>
                </div>
                <div class="chart">
                    <canvas id="profitChart"></canvas>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>အမြတ်ငွေ စာရင်း</h2>
                    <div class="profit-period-selector">
                        <button class="profit-period-btn active" data-period="daily">နေ့စဉ်</button>
                        <button class="profit-period-btn" data-period="monthly">လစဉ်</button>
                        <button class="profit-period-btn" data-period="yearly">နှစ်စဉ်</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="profit-table" id="daily-profit-table">
                        <thead>
                            <tr>
                                <th>ရက်စွဲ</th>
                                <th>စုစုပေါင်း ဝင်ငွေ</th>
                                <th>ပလက်ဖောင်း အမြတ်ငွေ (20%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($daily_profit) > 0): ?>
                                <?php foreach ($daily_profit as $profit): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($profit['date'])); ?></td>
                                        <td>Ks<?php echo number_format($profit['profit'] * 5, 2); ?></td>
                                        <td class="profit-amount">Ks<?php echo number_format($profit['profit'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center;">နေ့စဉ် အမြတ်ငွေ မရှိသေးပါ</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <table class="profit-table" id="monthly-profit-table" style="display: none;">
                        <thead>
                            <tr>
                                <th>လ</th>
                                <th>စုစုပေါင်း ဝင်ငွေ</th>
                                <th>ပလက်ဖောင်း အမြတ်ငွေ (20%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($monthly_profit) > 0): ?>
                                <?php foreach ($monthly_profit as $profit): ?>
                                    <tr>
                                        <td><?php echo date('F Y', mktime(0, 0, 0, $profit['month'], 1, $profit['year'])); ?></td>
                                        <td>Ks<?php echo number_format($profit['profit'] * 5, 2); ?></td>
                                        <td class="profit-amount">Ks<?php echo number_format($profit['profit'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center;">လစဉ် အမြတ်ငွေ မရှိသေးပါ</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <table class="profit-table" id="yearly-profit-table" style="display: none;">
                        <thead>
                            <tr>
                                <th>နှစ်</th>
                                <th>စုစုပေါင်း ဝင်ငွေ</th>
                                <th>ပလက်ဖောင်း အမြတ်ငွေ (20%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($yearly_profit) > 0): ?>
                                <?php foreach ($yearly_profit as $profit): ?>
                                    <tr>
                                        <td><?php echo $profit['year']; ?></td>
                                        <td>Ks<?php echo number_format($profit['profit'] * 5, 2); ?></td>
                                        <td class="profit-amount">Ks<?php echo number_format($profit['profit'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center;">နှစ်စဉ် အမြတ်ငွေ မရှိသေးပါ</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Driver Details Modal -->
        <div class="modal" id="driverModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>ယာဉ်မောင်း အသေးစိတ်</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($driver_details)): ?>
                    <div class="driver-details-grid">
                        <div class="driver-profile">
                            <img src="<?php echo !empty($driver_details['profile_picture']) ? htmlspecialchars($driver_details['profile_picture']) : 'uploads/drivers/default.png'; ?>" 
                                 alt="<?php echo htmlspecialchars($driver_details['name']); ?>" 
                                 class="driver-profile-img">
                            <h3><?php echo htmlspecialchars($driver_details['name']); ?></h3>
                            <p><?php echo htmlspecialchars($driver_details['phone']); ?></p>
                            <span class="driver-status status <?php echo $driver_details['status']; ?>">
                                <?php 
                                if ($driver_details['status'] == 'pending') echo 'ဆိုင်းငံ့ထား';
                                elseif ($driver_details['status'] == 'approved') echo 'အတည်ပြုပြီး';
                                else echo 'ပယ်ဖျက်ပြီး';
                                ?>
                            </span>
                        </div>
                        <div class="driver-details">
                            <h3>အခြေခံ အချက်အလက်များ</h3>
                            <div class="driver-info-grid">
                                <div class="info-item">
                                    <div class="info-label">NRC အမှတ်</div>
                                    <div class="info-value"><?php echo htmlspecialchars($driver_details['nrc']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">မွေးသက္ကရာဇ်</div>
                                    <div class="info-value"><?php echo htmlspecialchars($driver_details['dob']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">လိပ်စာ</div>
                                    <div class="info-value"><?php echo htmlspecialchars($driver_details['address']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">ယာဉ်မောင်းလိုင်စင်</div>
                                    <div class="info-value"><?php echo htmlspecialchars($driver_details['license_number']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">လိုင်စင်သက်တမ်း</div>
                                    <div class="info-value"><?php echo htmlspecialchars($driver_details['license_expiry']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">ယာဉ်အမျိုးအစား</div>
                                    <div class="info-value"><?php echo htmlspecialchars($driver_details['vehicle_type']); ?></div>
                                </div>
                            </div>

                            <div class="documents-section">
                                <h3>စာရွက်စာတမ်းများ</h3>
                                <div class="documents-grid">
                                    <?php
                                    $driver_id = $driver_details['id'];
                                    $documents_query = "SELECT * FROM driver_documents WHERE driver_id = $driver_id";
                                    $documents_result = $conn->query($documents_query);
                                    
                                    if ($documents_result && $documents_result->num_rows > 0) {
                                        while ($doc = $documents_result->fetch_assoc()) {
                                            $doc_type = str_replace('_', ' ', $doc['document_type']);
                                            echo '<div class="document-item">';
                                            echo '<img src="uploads/documents/' . htmlspecialchars($doc['file_path']) . '" alt="' . $doc_type . '">';
                                            echo '<div class="document-name">' . ucfirst($doc_type) . '</div>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<p>စာရွက်စာတမ်းများ မရှိပါ</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                        <p>ယာဉ်မောင်း အချက်အလက်များ မရှိပါ</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-close">ပိတ်မည်</button>
                    <?php if (!empty($driver_details) && $driver_details['status'] == 'pending'): ?>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="driver_id" value="<?php echo $driver_details['id']; ?>">
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn btn-reject">ပယ်ဖျက်မည်</button>
                    </form>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="driver_id" value="<?php echo $driver_details['id']; ?>">
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="btn btn-approve">အတည်ပြုမည်</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<!-- Add this modal for ride details (place it near the driver modal) -->
 <center>
<div class="modal" id="rideModal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>ခရီးစဉ် အသေးစိတ်</h2>
            <button id="closeRideModal">&times;</button>
        </div>
        <div class="modal-body" id="ride-details-content">
            <!-- Ride details will load here -->
        </div>
    </div>
</div>
 </center>
  <script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('rideModal');
    const modalContent = document.getElementById('ride-details-content');
    const closeBtn = document.getElementById('closeRideModal');

    document.querySelectorAll('.tripDetailsBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const rideId = this.dataset.id;

            fetch('rideModal.php?id=' + rideId)
                .then(response => response.text())
                .then(data => {
                    modalContent.innerHTML = data;
                    modal.style.display = 'block';
                });
        });
    });

    closeBtn.addEventListener('click', () => modal.style.display = 'none');
});
</script>


<!-- Analytics/Rides Section -->
<div id="analytics-section" class="content-section">
    <div class="dashboard-grid">
        <div class="card summary-card fade-in">
            <div class="icon" style="background: rgba(155, 89, 182, 0.1); color: #9b59b6;">
                <i class="fas fa-road"></i>
            </div>
            <h2>စုစုပေါင်း ခရီးစဉ်</h2>
            <div class="value"><?php
                $total_rides = $conn->query("SELECT COUNT(*) as total FROM rides")->fetch_assoc()['total'];
                echo $total_rides;
            ?></div>
            <div class="change">
                <i class="fas fa-arrow-up"></i>
                <span>15% တိုး</span>
            </div>
        </div>

        <div class="card summary-card fade-in delay-1">
            <div class="icon" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71;">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>ပြီးဆုံးသော ခရီးစဉ်</h2>
            <div class="value"><?php
                $completed_rides = $conn->query("SELECT COUNT(*) as total FROM rides WHERE status = 'completed'")->fetch_assoc()['total'];
                echo $completed_rides;
            ?></div>
            <div class="change">
                <i class="fas fa-arrow-up"></i>
                <span>10% တိုး</span>
            </div>
        </div>

        <div class="card summary-card fade-in delay-2">
            <div class="icon" style="background: rgba(241, 196, 15, 0.1); color: #f1c40f;">
                <i class="fas fa-clock"></i>
            </div>
            <h2>ဆိုင်းငံ့ထားသော ခရီးစဉ်</h2>
            <div class="value"><?php
                $pending_rides = $conn->query("SELECT COUNT(*) as total FROM rides WHERE status = 'pending'")->fetch_assoc()['total'];
                echo $pending_rides;
            ?></div>
            <div class="change down">
                <i class="fas fa-arrow-down"></i>
                <span>5% လျော့</span>
            </div>
        </div>

        <div class="card summary-card fade-in delay-3">
            <div class="icon" style="background: rgba(231, 76, 60, 0.1); color: #e74c3c;">
                <i class="fas fa-times-circle"></i>
            </div>
            <h2>ပယ်ဖျက်ထားသော ခရီးစဉ်</h2>
            <div class="value"><?php
                $cancelled_rides = $conn->query("SELECT COUNT(*) as total FROM rides WHERE status = 'cancelled'")->fetch_assoc()['total'];
                echo $cancelled_rides;
            ?></div>
            <div class="change down">
                <i class="fas fa-arrow-up"></i>
                <span>3% တိုး</span>
            </div>
        </div>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h2>ခရီးစဉ်များ</h2>
            <div>
                <select id="status-filter" style="padding: 8px; border-radius: 4px; margin-right: 10px;">
                    <option value="all">အားလုံး</option>
                    <option value="pending">မစတင်သေး</option>
                    <option value="assigned">တာဝန်ပေးပြီး</option>
                    <option value="in_progress">ဆောင်ရွက်ဆဲ</option>
                    <option value="completed">ပြီးဆုံး</option>
                    <option value="cancelled">ပယ်ဖျက်</option>
                </select>
                <input type="date" id="date-filter" style="padding: 8px; border-radius: 4px;">
            </div>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ခရီးစဉ် ID</th>
                        <th>ခရီးသည်</th>
                        <th>ယာဉ်မောင်း</th>
                        <th>အပြည့်အစုံ</th>
                        <th>ခရီးစဉ်ကုန်ကျစရိတ်</th>
                        <th>အခြေအနေ</th>
                        <th>ရက်စွဲ</th>
                        <th>လုပ်ဆောင်ချက်</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query to get all rides with rider and driver information
                    $rides_query = "
                        SELECT r.*, d.name as driver_name, d.phone as driver_phone, 
                               rider.name as rider_name, rider.phone as rider_phone
                        FROM rides r
                        LEFT JOIN drivers d ON r.driver_id = d.id
                        LEFT JOIN riders rider ON r.rider_id = rider.id
                        ORDER BY r.created_at DESC
                    ";
                    
                    $rides_result = $conn->query($rides_query);
                    
                    if ($rides_result && $rides_result->num_rows > 0) {
                        while ($ride = $rides_result->fetch_assoc()) {
                            $ride_date = new DateTime($ride['ride_date']);
                            $formatted_date = $ride_date->format('d/m/Y H:i');
                            
                            // Determine status class and text
                            switch ($ride['status']) {
                                case 'completed':
                                    $status_class = 'approved';
                                    $status_text = 'ပြီးစီး';
                                    break;
                                case 'cancelled':
                                    $status_class = 'rejected';
                                    $status_text = 'ပယ်ဖျက်';
                                    break;
                                case 'pending':
                                    $status_class = 'pending';
                                    $status_text = 'မစတင်သေး';
                                    break;
                                case 'assigned':
                                    $status_class = 'pending';
                                    $status_text = 'တာဝန်ပေးပြီး';
                                    break;
                                case 'in_progress':
                                    $status_class = 'approved';
                                    $status_text = 'ဆောင်ရွက်ဆဲ';
                                    break;
                                default:
                                    $status_class = '';
                                    $status_text = $ride['status'];
                            }
                    ?>
                    <tr data-status="<?php echo $ride['status']; ?>" data-date="<?php echo $ride_date->format('Y-m-d'); ?>">
                        <td>#<?php echo $ride['id']; ?></td>
                        <td>
                            <div class="driver-info">
                                <div>
                                    <div class="driver-name"><?php echo htmlspecialchars($ride['rider_name']); ?></div>
                                    <div class="driver-phone"><?php echo htmlspecialchars($ride['rider_phone']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($ride['driver_name'])): ?>
                            <div class="driver-info">
                                <div>
                                    <div class="driver-name"><?php echo htmlspecialchars($ride['driver_name']); ?></div>
                                    <div class="driver-phone"><?php echo htmlspecialchars($ride['driver_phone']); ?></div>
                                </div>
                            </div>
                            <?php else: ?>
                            <span class="status pending">မရှိသေး</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="max-width: 200px;">
                                <div><strong>စတင်ရန်:</strong> <?php echo htmlspecialchars($ride['pickup_location']); ?></div>
                                <div><strong>ဆုံးရန်:</strong> <?php echo htmlspecialchars($ride['dropoff_location']); ?></div>
                            </div>
                        </td>
                        <td><?php echo number_format($ride['fare'], 2); ?> Ks</td>
                        <td><span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        <td><?php echo $formatted_date; ?></td>
                        <td>
                            <button data-id="<?php echo $ride['id']; ?>" class="tripDetailsBtn">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if ($ride['status'] == 'pending' || $ride['status'] == 'assigned'): ?>
                            <button class="action-btn reject-btn cancel-ride-btn" data-id="<?php echo $ride['id']; ?>">
                                <i class="fas fa-times"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">ခရီးစဉ်များ မရှိသေးပါ</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


    <script>
        // Global variables
        let currentPage = 'dashboard';
        let revenueChart, userDistributionChart, profitChart;
        let currentDriverId = null;

        // Initialize the dashboard when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts
            setupChartsWithRealData();
            setupProfitChart();

            // Set up menu item click handlers
            setupMenuHandlers();

            // Set up search functionality
            setupSearch();

            // Set up other event listeners
            setupEventListeners();

            // Set up driver action listeners
            addDriverActionListeners();
            
            // Set up profit period selectors
            setupProfitPeriodSelectors();
            
            // Check if we need to show the driver modal
            <?php if (isset($_GET['driver_id']) && !empty($driver_details)): ?>
            document.getElementById('driverModal').style.display = 'flex';
            <?php endif; ?>
        });

        // Function to set up menu handlers
        function setupMenuHandlers() {
            document.querySelectorAll('.menu-item').forEach(item => {
                item.addEventListener('click', function() {
                    // Remove active class from all menu items
                    document.querySelectorAll('.menu-item').forEach(i => {
                        i.classList.remove('active');
                    });

                    // Add active class to clicked item
                    this.classList.add('active');

                    // Get the page name from data attribute
                    const pageName = this.getAttribute('data-page');

                    // Load the appropriate content
                    loadPageContent(pageName);
                });
            });
        }

        // Function to load page content
        function loadPageContent(pageName) {
            currentPage = pageName;

            // Hide all content sections first
            document.querySelectorAll('.content-section').forEach(section => {
                section.style.display = 'none';
            });

            // Show the appropriate content section
            const sectionId = `${pageName}-section`;
            const section = document.getElementById(sectionId);
            if (section) {
                section.style.display = 'block';
                
                // Special handling for analytics page
                if (pageName === 'analytics') {
                    ensureProperRidesLayout();
                }
                
                // Special handling for profit page
                if (pageName === 'profit') {
                    // Refresh profit chart when navigating to profit page
                    setupProfitChart();
                }
            }
        }

        // Function to set up search functionality
        function setupSearch() {
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.querySelector('.search-bar i');

            searchButton.addEventListener('click', () => performSearch(searchInput.value));
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    performSearch(searchInput.value);
                }
            });
        }

        // Function to perform search
        function performSearch(query) {
            if (!query.trim()) {
                alert('Please enter a search term');
                return;
            }

            alert(`Searching for: ${query}`);
        }

        // Function to set up other event listeners
        function setupEventListeners() {
            // Chart period filter buttons
            document.querySelectorAll('.chart-container .filter button').forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons in this filter group
                    this.parentNode.querySelectorAll('button').forEach(btn => {
                        btn.classList.remove('active');
                    });

                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Update chart based on period
                    if (this.parentNode.parentNode.querySelector('h2').textContent.includes('အမြတ်ငွေ')) {
                        updateProfitChart(this.getAttribute('data-period'));
                    }
                });
            });

            // View all buttons
            document.getElementById('view-all-drivers').addEventListener('click', (e) => {
                e.preventDefault();
                loadPageContent('drivers');
            });

            document.getElementById('view-all-rides').addEventListener('click', (e) => {
                e.preventDefault();
                // This would go to rides page if we had one
                alert('Rides page would show here');
            });

            // Modal close buttons
            document.querySelectorAll('.close-modal, .btn-close').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('driverModal').style.display = 'none';
                    // Remove the driver_id from URL
                    window.history.replaceState({}, document.title, window.location.pathname);
                });
            });
        }

        // Function to set up profit period selectors
        function setupProfitPeriodSelectors() {
            document.querySelectorAll('.profit-period-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove active class from all buttons
                    document.querySelectorAll('.profit-period-btn').forEach(b => {
                        b.classList.remove('active');
                    });
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Get the period
                    const period = this.getAttribute('data-period');
                    
                    // Hide all tables
                    document.querySelectorAll('.profit-table').forEach(table => {
                        table.style.display = 'none';
                    });
                    
                    // Show the selected table
                    document.getElementById(`${period}-profit-table`).style.display = 'table';
                    
                    // Update the chart
                    updateProfitChart(period);
                });
            });
        }

        // Function to add event listeners for driver actions
        function addDriverActionListeners() {
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const driverId = e.currentTarget.getAttribute('data-id');
                    // Redirect to the same page with driver_id parameter
                    window.location.href = window.location.pathname + '?driver_id=' + driverId;
                });
            });
        }

        // Function to initialize charts with real data
        function setupChartsWithRealData() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'အမြတ်ငွေ ($)',
                        data: [1200, 1900, 1500, 1800, 2200, 2800, 2500],
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
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `$${context.raw.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return `$${value.toLocaleString()}`;
                                }
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

            // User Distribution Chart
            const userCtx = document.getElementById('userChart').getContext('2d');
            userDistributionChart = new Chart(userCtx, {
                type: 'doughnut',
                data: {
                    labels: ['ခရီးသည်', 'ယာဉ်မောင်း', 'အက်မင်များ'],
                    datasets: [{
                        data: [<?php echo $total_riders; ?>, <?php echo $total_drivers; ?>, 1],
                        backgroundColor: ['#1fbad6', '#3498db', '#2c3e50'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    family: 'Inter, Noto Sans Myanmar, sans-serif'
                                }
                            }
                        }
                    }
                }
            });
        }

        // Function to set up profit chart
        function setupProfitChart() {
            const profitCtx = document.getElementById('profitChart').getContext('2d');
            
            // Prepare data for daily profit
            const dailyLabels = [];
            const dailyData = [];
            
            <?php foreach ($daily_profit as $profit): ?>
                dailyLabels.push('<?php echo date("M j", strtotime($profit["date"])); ?>');
                dailyData.push(<?php echo $profit["profit"]; ?>);
            <?php endforeach; ?>
            
            profitChart = new Chart(profitCtx, {
                type: 'bar',
                data: {
                    labels: dailyLabels,
                    datasets: [{
                        label: 'အမြတ်ငွေ (Ks)',
                        data: dailyData,
                        backgroundColor: '#1fbad6',
                        borderColor: '#1a9cb5',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Ks${context.raw.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return `Ks${value.toLocaleString()}`;
                                }
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
        }

        // Function to update profit chart based on period
        function updateProfitChart(period) {
            // In a real application, you would fetch data from the server based on the period
            // For this example, we'll just use the data we already have
            
            let labels = [];
            let data = [];
            
            if (period === 'daily') {
                <?php foreach ($daily_profit as $profit): ?>
                    labels.push('<?php echo date("M j", strtotime($profit["date"])); ?>');
                    data.push(<?php echo $profit["profit"]; ?>);
                <?php endforeach; ?>
            } else if (period === 'monthly') {
                <?php foreach ($monthly_profit as $profit): ?>
                    labels.push('<?php echo date("M Y", mktime(0, 0, 0, $profit["month"], 1, $profit["year"])); ?>');
                    data.push(<?php echo $profit["profit"]; ?>);
                <?php endforeach; ?>
            } else if (period === 'yearly') {
                <?php foreach ($yearly_profit as $profit): ?>
                    labels.push('<?php echo $profit["year"]; ?>');
                    data.push(<?php echo $profit["profit"]; ?>);
                <?php endforeach; ?>
            }
            
            // Update the chart
            profitChart.data.labels = labels;
            profitChart.data.datasets[0].data = data;
            profitChart.update();
        }

        // Helper function to show success message
        function showSuccess(message) {
            // Implement your success notification UI
            alert(`Success: ${message}`); // Replace with a proper notification system
        }

        // Function to ensure proper rides layout
        function ensureProperRidesLayout() {
            if (currentPage === 'analytics') {
                document.getElementById('analytics-section').style.display = 'block';
                document.getElementById('analytics-section').style.padding = '25px';
                document.getElementById('analytics-section').style.width = '100%';
                
                // Trigger a resize event to ensure proper rendering
                window.dispatchEvent(new Event('resize'));
            }
        }
    </script>
</body>

</html>