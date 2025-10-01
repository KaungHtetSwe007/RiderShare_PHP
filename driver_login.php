<?php
session_start();

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rideshare');

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $name = $_POST['name'];

    // Connect to database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT id, name, phone, status FROM drivers WHERE phone = ? AND name = ?");
    $stmt->bind_param("ss", $phone, $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $driver = $result->fetch_assoc();
        if ($driver['status'] == 'approved') {
            $_SESSION['driver_id'] = $driver['id'];
            $_SESSION['driver_name'] = $driver['name'];
            header('Location: driver_dashboard.php');
            exit;
        } else {
            $error = "သင့်အကောင့်ကို အတည်မပြုရသေးပါ။";
        }
    } else {
        $error = "ဖုန်းနံပါတ် သို့မဟုတ် အမည် မှားယွင်းနေပါသည်။";
    }
}
?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ယာဉ်မောင်း အကောင့်ဝင်ရန်</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Noto Sans Myanmar', 'Padauk', sans-serif;
        }
        
        body {
            background: linear-gradient(120deg, #1fbad6, #2c3e50);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background: white;
            width: 90%;
            max-width: 400px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .login-header {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 25px;
        }
        
        .login-header i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #1fbad6;
        }
        
        .login-header h1 {
            font-size: 22px;
        }
        
        .login-body {
            padding: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #1fbad6;
        }
        
        .input-icon input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .input-icon input:focus {
            outline: none;
            border-color: #1fbad6;
            box-shadow: 0 0 0 3px rgba(31, 186, 214, 0.2);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #1fbad6;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: #1899b3;
        }
        
        .error {
            color: #e74c3c;
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            background: rgba(231, 76, 60, 0.1);
            border-radius: 5px;
        }
        
        .login-footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
            border-top: 1px solid #eee;
        }
        
        .login-footer a {
            color: #1fbad6;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-car"></i>
            <h1>RideShare ယာဉ်မောင်း</h1>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="phone">ဖုန်းနံပါတ်</label>
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" id="phone" name="phone" placeholder="09xxxxxxxxx" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="name">အမည်</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" placeholder="အမည်ထည့်ပါ" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">အကောင့်ဝင်မည်</button>
            </form>
        </div>
        
        <div class="login-footer">
            အကောင့်မရှိသေးပါက? <a href="#">အက်မင်နှင့်ဆက်သွယ်ပါ</a>
        </div>
    </div>
</body>
</html>