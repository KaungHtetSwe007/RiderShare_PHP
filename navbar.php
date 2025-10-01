<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <!-- Google Fonts - Myanmar -->
    <link href="https://fonts.googleapis.com/css2?family=Padauk:wght@400;700&display=swap" rel="stylesheet">

    <link href="assests/css/navbar.css" rel="stylesheet">
    <script src="navbar.js"></script>
   

</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-car me-2"></i>RideShare
        </a>
        <button class="navbar-toggler" type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php"><i class="fas fa-home me-1"></i> ပင်မစာမျက်နှာ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-map-marked-alt me-1"></i> ခရီးစဉ်များ</a>
                </li>
                <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                    <a class="btn btn-cta" style="background-color: goldenrod;" href="#driverForm"><i class="fas fa-bolt me-1"></i> ယခုစီးပါ</a>
                </li>
                
                <!-- Profile Dropdown -->
                <?php if(isset($_SESSION['rider_name'])): ?>
                <li class="nav-item profile-dropdown ms-lg-3 mt-3 mt-lg-0">
                    <div class="profile-btn">
                        <?php echo substr($_SESSION['rider_name'], 0, 1); ?>
                    </div>
                    <div class="profile-dropdown-content">
                        <div class="profile-header">
                            <div class="profile-btn" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <?php echo substr($_SESSION['rider_name'], 0, 1); ?>
                            </div>
                            <div class="profile-name"><?php echo $_SESSION['rider_name']; ?></div>
                            <div class="small"><?php echo $_SESSION['rider_phone']; ?></div>
                        </div>
                        <a href="#"><i class="fas fa-user-circle"></i> ကိုယ်ရေးအချက်အလက်</a>
                        <a href="/rides_history.php"><i class="fas fa-history"></i> ခရီးစဉ်မှတ်တမ်း</a>
                        <a href="#"><i class="fas fa-cog"></i> ဆက်တင်များ</a>
                        <a href="#"><i class="fas fa-lock"></i> လျှို့ဝှက်နံပါတ် ပြောင်းရန်</a>
                        <a href="/auth/rider_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> အကောင့်မှ ထွက်ရန်</a>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

    </body>
</html>