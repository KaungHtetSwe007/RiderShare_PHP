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

// Initialize values with defaults
$activeDrivers = 0;
$totalRiders = 0;
$cities = 3;
$satisfaction = 98;

try {
    // Connect to database
    $conn = new mysqli($host, $user, $pass, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Fetch active drivers count
    $activeDriversQuery = "SELECT COUNT(*) as count FROM drivers WHERE status='approved' AND is_available=1";
    $activeDriversResult = $conn->query($activeDriversQuery);
    if ($activeDriversResult) {
        $activeDrivers = $activeDriversResult->fetch_assoc()['count'];
    }

    // Fetch total riders count
    $totalRidersQuery = "SELECT COUNT(*) as count FROM riders";
    $totalRidersResult = $conn->query($totalRidersQuery);
    if ($totalRidersResult) {
        $totalRiders = $totalRidersResult->fetch_assoc()['count'];
    }

    // Get rider information
    $rider_id = $_SESSION['rider_id'];
    $riderQuery = "SELECT * FROM riders WHERE id = $rider_id";
    $riderResult = $conn->query($riderQuery);
    if ($riderResult && $riderResult->num_rows > 0) {
        $rider = $riderResult->fetch_assoc();
        $rider_name = $rider['name'];
        $rider_phone = $rider['phone'];
    }

    // Close connection
    $conn->close();
} catch (Exception $e) {
    // Log error but don't display to users
    error_log($e->getMessage());
    // Values will use defaults
}

include('navbar.php');
?>

<!DOCTYPE html>
<html lang="my">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Dashboard - RideShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Padauk:wght@400;700&display=swap" rel="stylesheet">
    <link href="assests/css/maincontent.css" rel="stylesheet">
    <link href="/assests/css/routeselect.css" rel="stylesheet">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link href="assests/css/driverMultiBar.css" rel="stylesheet">
    <script src="./assests/js/driverMultiBar.js"></script>
    <script src="/assests/js/maincontent.js"></script>
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
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), #0d96ad);
            color: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
        }

        .user-avatar {
            width: 100px;
            height: 100px;
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .feature-card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .settings-list .list-group-item {
            border: none;
            border-left: 4px solid transparent;
            margin-bottom: 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .settings-list .list-group-item:hover {
            background-color: rgba(31, 186, 214, 0.1);
            border-left: 4px solid var(--primary-color);
        }

        .btn-ride {
            background-color: var(--primary-color);
            border: none;
            padding: 8px 24px;
            font-weight: bold;
        }

        .btn-ride:hover {
            background-color: #0d96ad;
        }

        .welcome-text {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .section-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: var(--primary-color);
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            color: white;
            z-index: 10000;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.3s ease-out;
        }

        .notification.success {
            background-color: #27ae60;
        }

        .notification.info {
            background-color: #3498db;
        }

        .notification.warning {
            background-color: #f39c12;
        }

        .notification.error {
            background-color: #e74c3c;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <main>
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="floating-element floating-1">
                <i class="fas fa-car" style="font-size: 5rem; color: var(--primary);"></i>
            </div>
            <div class="floating-element floating-2">
                <i class="fas fa-map-marker-alt" style="font-size: 4rem; color: var(--cta);"></i>
            </div>

            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 hero-content">
                        <h1 class="hero-title">သင့်ခရီးကို <span>မြန်မြန်</span> ရောက်အောင်လုပ်ပါ</h1>
                        <p class="hero-subtitle">စက္ကန့်ပိုင်းအတွင်း ကားစီးနိုင်ပါပြီ။ ချောမွေ့စွာဆိုက်ရောက်ပါ။</p>

                        <div class="d-flex gap-3 mb-4">
                            <a href="#rideBooking" class="btn btn-primary btn-lg px-4 py-2" style="border-radius: 10px;">
                                <i class="fas fa-car me-2"></i> ယခုစီးပါ
                            </a>
                            <a href="#" class="btn btn-outline-light btn-lg px-4 py-2" style="border-radius: 10px;">
                                <i class="fas fa-play me-2"></i>အသုံးပြုနည်း
                            </a>
                        </div>

                        <!-- <div class="d-flex align-items-center">
                            <div class="d-flex me-4">
                                <img src="https://randomuser.me/api/portraits/women/32.jpg" class="rounded-circle border border-3 border-white" width="40" height="40">
                                <img src="https://randomuser.me/api/portraits/men/75.jpg" class="rounded-circle border border-3 border-white ms-n2" width="40" height="40">
                                <img src="https://randomuser.me/api/portraits/women/63.jpg" class="rounded-circle border border-3 border-white ms-n2" width="40" height="40">
                            </div>
                            <div>
                                <p class="mb-0 small"><i class="fas fa-star text-warning me-1"></i> <strong>4.9</strong> (10K+ reviews)</p>
                            </div>
                        </div> -->
                    </div>

                    <div class="col-lg-6 mt-5 mt-lg-0">
                        <div class="search-box">
                            <ul class="nav nav-pills search-tabs mb-4" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="pills-ride-tab" data-bs-toggle="pill" data-bs-target="#pills-ride" type="button" role="tab">ကားစီးရန်</button>
                                </li>
                            </ul>
                            <!-- select route section start -->
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="pills-ride" role="tabpanel">
                                    <div class="mb-3">
                                        <label class="form-label">ကားခေါ်မည့်နေရာ</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt text-primary"></i></span>
                                            <input type="text" class="form-control" placeholder="သင့်တည်နေရာထည့်ပါ">
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">သွားလိုသည့်နေရာ</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-flag-checkered text-primary"></i></span>
                                            <input type="text" class="form-control" placeholder="ဘယ်ကိုသွားမလဲ?">
                                        </div>
                                    </div>
                                    <button class="btn search-btn w-100" style="background-color: goldenrod;">
                                        <i class="fas fa-search me-2"></i> ကားရှာပါ
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- select route section end -->
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <div class="container">
                <h2 class="section-title text-center mb-5">RideShare ၏ အားသာချက်များ</h2>

                <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h3 class="feature-title">Fast Pickup</h3>
                            <p>၂-၅ မိနစ်အတွင်း ကားရောက်ရှိပါသည်။ ကျွန်ုပ်တို့၏ drivers များသည် အမြဲနီးကပ်စွာရှိပါသည်။</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="feature-title">လုံခြုံစိတ်ချရ</h3>
                            <p>ယာဉ်မောင်းသူများအား စိစစ်ထားပါသည်။ ခရီးစဉ်အချက်အလက်များကို မျှဝေနိုင်ပါသည်။</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <h3 class="feature-title">စျေးနှုန်းသက်သာ</h3>
                            <p>ကြိုတင်သိရှိနိုင်သော စျေးနှုန်းများ။ အတူတကွစီးခြင်းဖြင့် ပိုမိုသက်သာစေပါသည်။</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h3 class="feature-title">အကူအညီ ၂၄နာရီ</h3>
                            <p>ကျွန်ုပ်တို့၏ အကူအညီအဖွဲ့သည် မည်သည့်အချိန်တွင်မဆို ဝန်ဆောင်မှုပေးနေပါသည်။</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="how-it-works">
            <div class="container">
                <h2 class="section-title text-center mb-5">အသုံးပြုနည်း</h2>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <h3 class="step-title">Book Your Ride</h3>
                            <p>သင့်နေရာနှင့် သွားလိုသည့်နေရာကို ရွေးချယ်ပါ။ ကားအမျိုးအစားရွေးပါ။</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <h3 class="step-title">Meet Your Driver</h3>
                            <p>မိမိခေါ်ထားသော driver မောင်းလာနေသည်ကို အချိန်နှင့်တပြေးညီ ကြည့်နိုင်ပါသည်။</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <h3 class="step-title">Enjoy Your Ride</h3>
                            <p>အက်ပ်မှတဆင့် အလိုအလျောက်ငွေပေးချေပါ။ ခရီးစဉ်အကြောင်း အကြံပြုနိုင်ပါသည်။</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- route select start -->
        <div class="container">
            <div class="header">
                <h1><i class="fas fa-car" id="rideBooking"></i> RideShare</h1>
                <p class="mb-0">စာဖြင့်ရိုက်ထည့်ခြင်း သို့မဟုတ် မြေပုံပေါ်တွင် နေရာရွေးချယ်ခြင်း</p>
            </div>

            <div class="instruction">
                <h5><i class="fas fa-info-circle"></i> ညွှန်ကြားချက်များ</h5>
                <p class="mb-1">1. စတင်မည့်နေရာနှင့် ဆုံးမည့်နေရာကို စာဖြင့်ရိုက်ထည့်နိုင်သည်</p>
                <p class="mb-1">2. သို့မဟုတ် မြေပုံပေါ်တွင် နေရာရွေးချယ်ရန် "မြေပုံပေါ်ရွေးရန်" ခလုတ်ကို နှိပ်ပါ</p>
                <p class="mb-0">3. ရွေးချယ်ထားသောနေရာသည် သက်ဆိုင်ရာအချက်အလက်နေရာတွင် ပြသသွား�မည်ဖြစ်ပါသည်</p>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <div class="location-inputs">
                        <h4 class="section-title">လမ်းကြောင်းရွေးချယ်ပါ</h4>

                        <div class="location-marker">
                            <div class="marker-icon marker-start">
                                <i class="fas fa-circle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label">ကားခေါ်မည့်နေရာ</label>
                                <div class="search-box">
                                    <div class="input-with-button">
                                        <input type="text" id="pickup-input" class="form-control" placeholder="စာဖြင့်ရိုက်ထည့်ပါ သို့မဟုတ် မြေပုံပေါ်ရွေးပါ">
                                        <button id="pickup-map-btn" class="btn btn-outline-primary">
                                            <i class="fas fa-map-marker-alt"></i> မြေပုံပေါ်ရွေးရန်
                                        </button>
                                    </div>
                                    <div id="pickup-suggestions" class="search-suggestions"></div>
                                </div>
                            </div>
                        </div>

                        <div class="location-marker">
                            <div class="marker-icon marker-end">
                                <i class="fas fa-flag"></i>
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label">သွား�လိုသည့်နေရာ</label>
                                <div class="search-box">
                                    <div class="input-with-button">
                                        <input type="text" id="dropoff-input" class="form-control" placeholder="စာဖြင့်ရိုက်ထည့်ပါ သို့မဟုတ် မြေပုံပေါ်ရွေးပါ">
                                        <button id="dropoff-map-btn" class="btn btn-outline-primary">
                                            <i class="fas fa-map-marker-alt"></i> မြေပုံပေါ်ရွေး�ရန်
                                        </button>
                                    </div>
                                    <div id="dropoff-suggestions" class="search-suggestions"></div>
                                </div>
                            </div>
                        </div>

                        <div class="suggested-locations">
                            <p class="mb-2">အကြံပြုထားသောနေရာများ:</p>
                            <div>
                                <span class="location-chip" data-location="ရန်ကုန်မြို့">ရန်ကုန်မြို့</span>
                                <span class="location-chip" data-location="မန္တလေးမြို့">မန္တလေးမြို့</span>
                                <span class="location-chip" data-location="ပုသိမ်မြို့">ပုသိမ်မြို့</span>
                                <span class="location-chip" data-location="ကမာရွတ်">ကမာရွတ်</span>
                                <span class="location-chip" data-location="ဗိုလ်တထောင်">ဗိုလ်တထောင်</span>
                            </div>
                        </div>
                    </div>

                    <div class="map-container">
                        <div id="route-map"></div>
                        <div class="map-controls">
                            <button id="btn-clear" class="btn btn-secondary btn-sm btn-map-control">
                                <i class="fas fa-trash"></i> အားလုံးဖျက်ရန်
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="route-summary">
                        <h4 class="section-title">ခရီးစဉ်အချက်အလက်</h4>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> လမ်းကြောင်းရွေးချယ်�ရန် စတင်ရန်နှင့် ဆုံး�ရန်နေရာများကို ထည့်သွင်းပါ။
                        </div>

                        <div class="route-details">
                            <div class="detail-item">
                                <span>အကွာအဝေး</span>
                                <span id="distance-value">-</span>
                            </div>
                            <div class="detail-item">
                                <span>ခရီးသည်များ</span>
                                <span>
                                    <select id="passenger-count" class="form-select form-select-sm d-inline-block w-auto">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                    </select>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span>ကားအမျိုးအစား</span>
                                <span>
                                    <select id="vehicle-type" class="form-select form-select-sm d-inline-block w-auto">
                                        <option value="Car">တက်(စ်)ီ</option>
                                        <option value="Oway">အိုးဝေ</option>
                                        <option value="Motorcycle">ဆိုင်ကယ်</option>
                                    </select>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span>ခန့်မှန်းချိန်</span>
                                <span id="duration-value">-</span>
                            </div>
                            <div class="detail-item">
                                <span>ခန့်မှန်း�ကုန်ကျငွေ</span>
                                <span id="fare-value" class="fw-bold text-primary">-</span>
                            </div>
                        </div>

                        <button id="confirm-ride" class="btn btn-primary w-100 mt-4 py-2" disabled>
                            <i class="fas fa-car me-2"></i> ကားခေါ်ရန်
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Driver Selection Modal -->
        <div id="driver-modal" class="driver-modal">
            <div class="driver-modal-content">
                <div class="modal-header">
                    <h3><i class="fas fa-car"></i> ရရှိနိုင်သော ကားများ</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div id="searching-drivers" class="searching-drivers">
                        <div class="searching-animation pulse">
                            <i class="fas fa-car-side"></i>
                        </div>
                        <h4>ကားများ ရှာနေပါသည်...</h4>
                        <p>ကျေးဇူးပြု၍ စောင့်ဆိုင်းပေးပါ</p>
                    </div>

                    <div id="no-drivers" class="no-drivers">
                        <i class="fas fa-exclamation-circle" style="font-size: 50px; color: #e74c3c;"></i>
                        <h4>ရရှိနိုင်သော ကားများ မရှိပါ</h4>
                        <p>ကျေးဇူးပြု၍ နောက်မှပြန်ကြိုးစားပါ သို့မဟုတ် နေရာပြောင်းရွေးချယ်ပါ</p>
                        <button class="btn btn-primary mt-3" id="retry-search">ပြန်လည်ရှာဖွေရန်</button>
                    </div>

                    <div id="drivers-list" class="drivers-list">
                        <h4 class="mb-3">သင့်အနီးရှိ ကားများ (<span id="drivers-count">0</span>)</h4>
                        <div id="drivers-container"></div>

                        <div class="estimated-arrival">
                            <i class="fas fa-clock"></i> ခန့်မှန်းရောက်ရှိချိန်: <span id="eta-time">၁၂:၄၅ PM</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- route select end -->
        <!-- Available Rides Section -->
        <section class="rides-section">
            <div class="container">
                <h2 class="section-title text-center mb-5" style="color: var(--accent);">ရရှိနိုင်သော ကားအမျိုးအစားများ</h2>

                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="ride-card">
                            <div class="position-relative">
                                <img src="/assests/image/taxi.webp" class="ride-img" alt="Standard Ride">
                                <span class="ride-badge">Popular</span>
                            </div>
                            <div class="p-3">
                                <h3 class="h5">Taxi</h3>
                                <p class="mb-2"><i class="fas fa-user-friends me-2"></i>ခရီးသည် (အများဆုံး ၆ ယောက်)</p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <!-- <span class="ride-price">$12.50</span> -->
                                    <button class="btn ride-btn">Book Now</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-6 col-lg-4">
                        <div class="ride-card">
                            <div class="position-relative">
                                <img src="/assests/image/oway.avif" class="ride-img" alt="XL Ride">
                                <span class="ride-badge">Popular</span>
                            </div>
                            <div class="p-3">
                                <h3 class="h5">အိုး‌ဝေ(Oway)</h3>
                                <p class="mb-2"><i class="fas fa-user-friends me-2"></i> ခရီးသည် (အများဆုံး ၆ ယောက်)</p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <!-- <span class="ride-price">$18.75</span> -->
                                    <button class="btn ride-btn">Book Now</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="ride-card">
                            <div class="position-relative">
                                <img src="/assests/image/motorcycle.jpg" class="ride-img" alt="Bike Ride">
                            </div>
                            <div class="p-3">
                                <h3 class="h5">ဆိုင်ကယ် ကယ်ရီ</h3>
                                <p class="mb-2"><i class="fas fa-user me-2"></i>ခရီးသည် (အများဆုံး ၁ ယောက်)</p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <!-- <span class="ride-price">$5.99</span> -->
                                    <button class="btn ride-btn">Book Now</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php
        $connection = new mysqli("localhost", "root", "", "rideshare");
        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        $sqlDrivers = "SELECT COUNT(*) AS total_drivers FROM drivers  WHERE status='approved' AND is_available=1";
        $resultDrivers = $connection->query($sqlDrivers);
        $rowDrivers = $resultDrivers->fetch_assoc();

        $sqlRiders = "SELECT COUNT(*) AS total_riders FROM riders";
        $resultRiders = $connection->query($sqlRiders);
        $rowRiders = $resultRiders->fetch_assoc();

        $connection->close();

        // Additional stats
        $cities = 3;
        $satisfaction = 98;
        ?>

        <section class="stats-section">
            <div class="container">
                <div class="row text-center">
                    <div class="col-md-3 col-6 mb-4 mb-md-0">
                        <div class="stat-item">
                            <div class="stat-number" data-count="<?php echo $rowDrivers['total_drivers']; ?>">
                                <?php echo $rowDrivers['total_drivers']; ?>
                            </div>
                            <div class="stat-label">Active Drivers</div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6 mb-4 mb-md-0">
                        <div class="stat-item">
                            <div class="stat-number" data-count="<?php echo $rowRiders['total_riders']; ?>">
                                <?php echo $rowRiders['total_riders']; ?>
                            </div>
                            <div class="stat-label">စီးနင်းသူများ</div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number" data-count="<?php echo $cities; ?>">
                                <?php echo $cities; ?>
                            </div>
                            <div class="stat-label">မြိုများ</div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number" data-count="<?php echo $satisfaction; ?>">
                                <?php echo $satisfaction; ?>
                            </div>
                            <div class="stat-label">ကျေနပ်မှု</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials">
            <div class="container">
                <h2 class="section-title text-center mb-5">ကျွန်ုပ်တို့၏ စီးနင်းသူများ၏ အကြံပြုချက်များ</h2>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="testimonial-card">
                            <div class="d-flex align-items-center mb-3">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" class="user-img me-3">
                                <div>
                                    <h5 class="mb-0">မင်းသိန်း</h5>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                            <p>"RideShare သည် မြို့တွင်းသွား�လာရေးကို အပြောင်းအလဲဖြစ်စေပါသည်။ ဆိုက်ကားမောင်းများသည် အမြဲပညာရှိပြီး အက်ပ်အသုံးပြု�ရန် အလွန်လွယ်ကူပါသည်။"</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="testimonial-card">
                            <div class="d-flex align-items-center mb-3">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" class="user-img me-3">
                                <div>
                                    <h5 class="mb-0">ကျော်ကြီး</h5>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                </div>
                            </div>
                            <p>"နေ့စဉ်သွားလာရန်အတွက် RideShare ကိုအသုံးပြုပါသည်။ ယုံကြည်စိတ်ချရပြီး ကား�ရပ်နားရန်ထက် ပိုမိုကောင်းမွန်ပါသည်။"</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="testimonial-card">
                            <div class="d-flex align-items-center mb-3">
                                <img src="https://randomuser.me/api/portraits/women/68.jpg" class="user-img me-3">
                                <div>
                                    <h5 class="mb-0">ဝင်းပပစိုး</h5>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                            <p>"ညဘက်စီးနင်းသည့်အခါ လုံခြုံမှုအာမခံချက်များက စိတ်ချရစေပါသည်။ မိသားစုများနှင့် ခရီးစဉ်အချက်အလက်များကို မျှဝေနိုင်ခြင်းကို နှစ်သက်ပါသည်။"</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include('footer.php') ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize map centered on Yangon
            const map = L.map('route-map').setView([16.8409, 96.1735], 13);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Add markers for start and end points
            let startMarker = null;
            let endMarker = null;
            let routeLine = null;

            // Default locations (Yangon)
            const defaultStart = [16.8409, 96.1735];
            const defaultEnd = [16.8000, 96.1500];

            // Add default markers
            startMarker = L.marker(defaultStart, {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map).bindPopup('စတင်မည့်နေရာ');

            endMarker = L.marker(defaultEnd, {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map).bindPopup('ဆုံးမည့်နေရာ');

            // Draw a sample route line
            routeLine = L.polyline([defaultStart, defaultEnd], {
                color: 'blue'
            }).addTo(map);

            // Fit map to show both markers
            map.fitBounds(L.latLngBounds(defaultStart, defaultEnd));

            // Calculate initial distance and duration
            updateRouteDetails(defaultStart, defaultEnd);

            // Track which input we're setting (pickup or dropoff)
            let settingPickup = false;
            let settingDropoff = false;

            // Set up button event listeners
            document.getElementById('pickup-map-btn').addEventListener('click', function() {
                settingPickup = true;
                settingDropoff = false;
                alert('မြေပုံပေါ်ရှိ စတင်မည့်နေရာကို နှိပ်ပါ');
            });

            document.getElementById('dropoff-map-btn').addEventListener('click', function() {
                settingDropoff = true;
                settingPickup = false;
                alert('မြေပုံပေါ်ရှိ ဆုံးမည့်နေရာ�ကို နှိပ်ပါ');
            });

            document.getElementById('btn-clear').addEventListener('click', function() {
                // Clear markers and inputs
                if (startMarker) map.removeLayer(startMarker);
                if (endMarker) map.removeLayer(endMarker);
                if (routeLine) map.removeLayer(routeLine);

                document.getElementById('pickup-input').value = '';
                document.getElementById('dropoff-input').value = '';

                // Reset to default view
                map.setView([16.8409, 96.1735], 13);

                // Recreate default markers
                startMarker = L.marker(defaultStart, {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(map).bindPopup('စတင်�မည့်နေရာ');

                endMarker = L.marker(defaultEnd, {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(map).bindPopup('ဆုံး�မည့်နေရာ');

                routeLine = L.polyline([defaultStart, defaultEnd], {
                    color: 'blue'
                }).addTo(map);

                updateRouteDetails(defaultStart, defaultEnd);
                updateConfirmButton();

                settingPickup = false;
                settingDropoff = false;
            });

            // Click on map to set location
            map.on('click', function(e) {
                if (settingPickup) {
                    // Use the Nominatim reverse geocoding service to get address
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
                        .then(response => response.json())
                        .then(data => {
                            const address = data.display_name || 'Unknown location';
                            document.getElementById('pickup-input').value = address;
                            updateMarker(startMarker, e.latlng, 'စတင်မည့်နေရာ');
                            updateRoute();
                            updateConfirmButton();
                            settingPickup = false;
                        })
                        .catch(error => {
                            console.error('Error getting address:', error);
                            alert('လိပ်စာရယူရာတွင် ပြဿနာတစ်ခုဖြစ်�နေပါသည်။');
                        });
                } else if (settingDropoff) {
                    // Use the Nominatim reverse geocoding service to get address
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
                        .then(response => response.json())
                        .then(data => {
                            const address = data.display_name || 'Unknown location';
                            document.getElementById('dropoff-input').value = address;
                            updateMarker(endMarker, e.latlng, 'ဆုံးမည့်နေရာ');
                            updateRoute();
                            updateConfirmButton();
                            settingDropoff = false;
                        })
                        .catch(error => {
                            console.error('Error getting address:', error);
                            alert('လိပ်စာရယူရာတွင် ပြဿနာတစ်ခုဖြစ်နေပါသည်။');
                        });
                }
            });

            // Function to update a marker position
            function updateMarker(marker, latlng, popupText) {
                marker.setLatLng(latlng);
                marker.bindPopup(popupText).openPopup();
            }

            // Function to update the route line and details
            function updateRoute() {
                if (startMarker && endMarker) {
                    const startLatLng = startMarker.getLatLng();
                    const endLatLng = endMarker.getLatLng();

                    // Remove existing route line
                    if (routeLine) {
                        map.removeLayer(routeLine);
                    }

                    // Draw new route line
                    routeLine = L.polyline([startLatLng, endLatLng], {
                        color: 'blue'
                    }).addTo(map);

                    // Update map view to show both markers
                    map.fitBounds(L.latLngBounds(startLatLng, endLatLng));

                    // Update route details
                    updateRouteDetails(startLatLng, endLatLng);
                }
            }

            // Function to calculate and update route details
            // Function to calculate and update route details
            function updateRouteDetails(start, end) {
                // Calculate distance using Haversine formula
                const distance = calculateDistance(start.lat, start.lng, end.lat, end.lng);
                currentDistance = distance; // Update global variable

                // Estimate duration based on distance (assuming average speed of 30 km/h)
                const durationMinutes = Math.round((distance / 30) * 60);
                currentDuration = durationMinutes; // Update global variable

                // Calculate fare based on distance and vehicle type
                const vehicleType = document.getElementById('vehicle-type').value;
                const fare = calculateFare(distance, vehicleType);

                // Update UI
                document.getElementById('distance-value').textContent = distance.toFixed(1) + ' km';
                document.getElementById('duration-value').textContent = durationMinutes + ' min';
                document.getElementById('fare-value').textContent = fare.toLocaleString() + ' Ks';
            }

            // Haversine formula to calculate distance between two coordinates
            function calculateDistance(lat1, lon1, lat2, lon2) {
                const R = 6371; // Earth's radius in km
                const dLat = deg2rad(lat2 - lat1);
                const dLon = deg2rad(lon2 - lon1);
                const a =
                    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
                    Math.sin(dLon / 2) * Math.sin(dLon / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return R * c; // Distance in km
            }

            function deg2rad(deg) {
                return deg * (Math.PI / 180);
            }

            // Calculate fare based on distance and vehicle type
            function calculateFare(distance, vehicleType) {
                let baseFare, ratePerKm;

                switch (vehicleType) {
                    case 'car':
                        baseFare = 1500;
                        ratePerKm = 300;
                        break;
                    case 'premium':
                        baseFare = 2500;
                        ratePerKm = 500;
                        break;
                    case 'xl':
                        baseFare = 3000;
                        ratePerKm = 600;
                        break;
                    default:
                        baseFare = 1500;
                        ratePerKm = 300;
                }

                return baseFare + (distance * ratePerKm);
            }

            // Update confirm button state based on inputs
            function updateConfirmButton() {
                const pickupValue = document.getElementById('pickup-input').value;
                const dropoffValue = document.getElementById('dropoff-input').value;
                const confirmButton = document.getElementById('confirm-ride');

                confirmButton.disabled = !(pickupValue && dropoffValue);
            }

            // Event listeners for input changes
            document.getElementById('pickup-input').addEventListener('input', function() {
                updateConfirmButton();
                searchLocation(this.value, 'pickup');
            });

            document.getElementById('dropoff-input').addEventListener('input', function() {
                updateConfirmButton();
                searchLocation(this.value, 'dropoff');
            });

            // Event listener for vehicle type changes
            document.getElementById('vehicle-type').addEventListener('change', function() {
                if (startMarker && endMarker) {
                    const startLatLng = startMarker.getLatLng();
                    const endLatLng = endMarker.getLatLng();
                    updateRouteDetails(startLatLng, endLatLng);
                }
            });

            // In your confirm ride button event listener
            document.getElementById('confirm-ride').addEventListener('click', function() {
                const pickup = document.getElementById('pickup-input').value;
                const dropoff = document.getElementById('dropoff-input').value;
                const vehicleTypeUi = document.getElementById('vehicle-type').value;
                const vehicleTypeDb = mapVehicleTypeToDb(vehicleTypeUi);

                if (!pickup || !dropoff) {
                    alert('ကျေးဇူးပြု၍ စတင်မည့်နေရာနှင့် ဆုံးမည့်နေရာကို ထည့်သွင်းပါ');
                    return;
                }

                // Show driver modal
                const driverModal = document.getElementById('driver-modal');
                driverModal.style.display = 'block';

                // Show searching animation
                document.getElementById('searching-drivers').style.display = 'block';
                document.getElementById('no-drivers').style.display = 'none';
                document.getElementById('drivers-list').style.display = 'none';

                // Fetch available drivers from server with the mapped vehicle type
                fetchAvailableDrivers(vehicleTypeDb);
            });


                // Add validation for passenger count and vehicle type
    function validatePassengerVehicle() {
        const passengerCount = parseInt(document.getElementById('passenger-count').value);
        const vehicleType = document.getElementById('vehicle-type').value;
        const confirmButton = document.getElementById('confirm-ride');
        
        if (passengerCount > 2 && vehicleType === 'Motorcycle') {
            showNotification('ဆိုင်ကယ်ကို ခရီးသည် ၂ ယောက်ထက်ပိုပါက မရွေးချယ်နိုင်ပါ', 'warning');
            document.getElementById('vehicle-type').value = 'Car'; // Default to Car
            // Recalculate fare
            if (startMarker && endMarker) {
                const startLatLng = startMarker.getLatLng();
                const endLatLng = endMarker.getLatLng();
                updateRouteDetails(startLatLng, endLatLng);
            }
        }
        
        updateConfirmButton();
    }
    
    // Add event listeners
    document.getElementById('passenger-count').addEventListener('change', validatePassengerVehicle);
    document.getElementById('vehicle-type').addEventListener('change', validatePassengerVehicle);
    
    // Call initially to set correct state
    validatePassengerVehicle();

            // Function to fetch available drivers from server
            function fetchAvailableDrivers(vehicleType) {
                const pickupLocation = document.getElementById('pickup-input').value;

                // Create FormData object
                const formData = new FormData();
                formData.append('vehicle_type', vehicleType);
                formData.append('pickup_location', pickupLocation);

                // Make AJAX request to PHP backend
                fetch('get_available_drivers.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Hide searching animation
                        document.getElementById('searching-drivers').style.display = 'none';

                        if (data.success && data.drivers.length > 0) {
                            // Display drivers
                            displayDrivers(data.drivers);
                            document.getElementById('drivers-list').style.display = 'block';
                            document.getElementById('drivers-count').textContent = data.drivers.length;
                        } else {
                            // Show no drivers message
                            document.getElementById('no-drivers').style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching drivers:', error);
                        document.getElementById('searching-drivers').style.display = 'none';
                        document.getElementById('no-drivers').style.display = 'block';
                    });
            }
            // Function to display drivers in the modal
            function displayDrivers(drivers) {
                const driversContainer = document.getElementById('drivers-container');
                driversContainer.innerHTML = '';

                drivers.forEach(driver => {
                    const driverCard = document.createElement('div');
                    driverCard.className = 'driver-card';

                    // Generate initials for avatar
                    const names = driver.name.split(' ');
                    let initials = '';
                    if (names.length > 0) {
                        initials = names[0].charAt(0);
                        if (names.length > 1) {
                            initials += names[names.length - 1].charAt(0);
                        }
                    }

                    // Map database vehicle type to UI
                    const vehicleTypeMap = {
                        'Car': 'တက်(စ်)ီ',
                        'Oway': 'အိုးဝေး',
                        'Motorcycle': 'ဆိုင်ကယ်'
                    };
                    const vehicleTypeUi = vehicleTypeMap[driver.vehicle_type] || driver.vehicle_type;

                    driverCard.innerHTML = `
            <div class="driver-avatar">
                ${initials}
            </div>
            <div class="driver-info">
                <div class="driver-name">${driver.name}</div>
                <div class="driver-car">${vehicleTypeUi} • ${driver.vehicle_model} • ${driver.registration_no}</div>
                <div class="driver-distance"><i class="fas fa-car"></i> ${driver.eta} မိနစ်အတွင်း ရောက်ရှိမည်</div>
            </div>
            <div class="driver-actions">
                <button class="btn-select-driver" data-driver-id="${driver.id}">ဤကားကိုရွေးရန်</button>
            </div>
        `;

                    driversContainer.appendChild(driverCard);
                });

                // Add event listeners to select buttons
                document.querySelectorAll('.btn-select-driver').forEach(button => {
                    button.addEventListener('click', function() {
                        const driverId = this.getAttribute('data-driver-id');
                        selectDriver(driverId);
                    });
                });

                // Update ETA time
                const now = new Date();
                now.setMinutes(now.getMinutes() + 5);
                const hours = now.getHours();
                const minutes = now.getMinutes();
                const ampm = hours >= 12 ? 'PM' : 'AM';
                const formattedHours = hours % 12 || 12;
                const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
                document.getElementById('eta-time').textContent = `${formattedHours}:${formattedMinutes} ${ampm}`;
            }

            // Function to generate star rating HTML
            function generateStarRating(rating) {
                let stars = '';
                const fullStars = Math.floor(rating);
                const hasHalfStar = rating % 1 >= 0.5;

                for (let i = 0; i < fullStars; i++) {
                    stars += '<i class="fas fa-star"></i>';
                }

                if (hasHalfStar) {
                    stars += '<i class="fas fa-star-half-alt"></i>';
                }

                const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
                for (let i = 0; i < emptyStars; i++) {
                    stars += '<i class="far fa-star"></i>';
                }

                return stars;
            }

            // Function to get vehicle type name in Burmese
            function getVehicleTypeName(type) {
                switch (type) {
                    case 'Car':
                        return 'တက်(စ်)ီ';
                    case 'Oway':
                        return 'အိုးဝေ';
                    case 'Motorcycle':
                        return 'ဆိုင်ကယ်';
                    default:
                        return type;
                }
            }

            // Function to search for location suggestions
            function searchLocation(query, type) {
                if (query.length < 3) {
                    document.getElementById(`${type}-suggestions`).style.display = 'none';
                    return;
                }

                // Use Nominatim search API
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=mm&limit=5`)
                    .then(response => response.json())
                    .then(data => {
                        const suggestionsContainer = document.getElementById(`${type}-suggestions`);
                        suggestionsContainer.innerHTML = '';

                        if (data.length > 0) {
                            data.forEach(place => {
                                const suggestionItem = document.createElement('div');
                                suggestionItem.className = 'suggestion-item';
                                suggestionItem.textContent = place.display_name;
                                suggestionItem.addEventListener('click', function() {
                                    document.getElementById(`${type}-input`).value = place.display_name;
                                    suggestionsContainer.style.display = 'none';

                                    // Update marker position
                                    const latlng = [parseFloat(place.lat), parseFloat(place.lon)];
                                    if (type === 'pickup') {
                                        updateMarker(startMarker, latlng, 'စတင်�မည့်နေရာ');
                                    } else {
                                        updateMarker(endMarker, latlng, 'ဆုံးမည့်နေရာ');
                                    }

                                    updateRoute();
                                    updateConfirmButton();
                                });

                                suggestionsContainer.appendChild(suggestionItem);
                            });

                            suggestionsContainer.style.display = 'block';
                        } else {
                            suggestionsContainer.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching location suggestions:', error);
                    });
            }

            // Close suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.search-box')) {
                    document.getElementById('pickup-suggestions').style.display = 'none';
                    document.getElementById('dropoff-suggestions').style.display = 'none';
                }
            });

            // Close modal when clicking on X
            document.querySelector('.close-modal').addEventListener('click', function() {
                document.getElementById('driver-modal').style.display = 'none';
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target == document.getElementById('driver-modal')) {
                    document.getElementById('driver-modal').style.display = 'none';
                }
            });

            // Retry search button
            document.getElementById('retry-search').addEventListener('click', function() {
                const vehicleType = document.getElementById('vehicle-type').value;

                document.getElementById('no-drivers').style.display = 'none';
                document.getElementById('searching-drivers').style.display = 'block';

                // Fetch available drivers again
                fetchAvailableDrivers(vehicleType);
            });

            // Add click handlers for suggested locations
            document.querySelectorAll('.location-chip').forEach(chip => {
                chip.addEventListener('click', function() {
                    const location = this.getAttribute('data-location');

                    // Determine which input is active or use pickup by default
                    const activeInput = document.activeElement.id;
                    const inputId = (activeInput === 'pickup-input' || activeInput === 'dropoff-input') ?
                        activeInput : 'pickup-input';

                    document.getElementById(inputId).value = location;

                    // Trigger search to get coordinates
                    searchLocation(location, inputId === 'pickup-input' ? 'pickup' : 'dropoff');

                    updateConfirmButton();
                });
            });
        });

        //real time route information
        // Function to fetch available drivers from server
        function fetchAvailableDrivers(vehicleType) {
            const pickupLocation = document.getElementById('pickup-input').value;

            // Create FormData object
            const formData = new FormData();
            formData.append('vehicle_type', vehicleType);
            formData.append('pickup_location', pickupLocation);

            // Show searching animation
            document.getElementById('searching-drivers').style.display = 'block';
            document.getElementById('no-drivers').style.display = 'none';
            document.getElementById('drivers-list').style.display = 'none';

            // Make AJAX request to PHP backend
            fetch('get_available_drivers.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Hide searching animation
                    document.getElementById('searching-drivers').style.display = 'none';

                    if (data.success && data.drivers.length > 0) {
                        // Display drivers
                        displayDrivers(data.drivers);
                        document.getElementById('drivers-list').style.display = 'block';
                        document.getElementById('drivers-count').textContent = data.drivers.length;
                    } else {
                        // Show no drivers message
                        document.getElementById('no-drivers').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error fetching drivers:', error);
                    document.getElementById('searching-drivers').style.display = 'none';
                    document.getElementById('no-drivers').style.display = 'block';
                });
        }

        // Function to map UI vehicle type to database values
        function mapVehicleTypeToDb(uiType) {
            const mapping = {
                'တက်(စ်)ီ': 'Car',
                'အိုးဝေ': 'Oway',
                'ဆိုင်ကယ်': 'Motorcycle'
            };
            return mapping[uiType] || uiType;
        }


        function selectDriver(driverId) {
    const pickup = document.getElementById('pickup-input').value;
    const dropoff = document.getElementById('dropoff-input').value;
    const vehicleType = document.getElementById('vehicle-type').value;
    const passengers = document.getElementById('passenger-count').value;
    const fareText = document.getElementById('fare-value').textContent;

    // Extract numeric fare
    const fare = parseFloat(fareText.replace(' Ks', '').replace(/,/g, ''));

    if (!pickup || !dropoff) {
        showNotification('ကျေးဇူးပြု၍ စတင်မည့်နေရာနှင့် ဆုံးမည့်နေရာကို ထည့်သွင်းပါ', 'error');
        return;
    }

    if (isNaN(fare) || fare <= 0) {
        showNotification('ကျေးဇူးပြု၍ မှန်ကန်သောကုန်ကျငွေကို ထည့်သွင်းပါ', 'error');
        return;
    }

    // Current time as start_time
    const now = new Date();
    const start_time = now.toISOString().slice(0, 19).replace('T', ' ');

    // No end_time yet
    const end_time = null;

    // Build ride data with all required fields
    const rideData = {
        driver_id: driverId,
        pickup_location: pickup,
        dropoff_location: dropoff,
        vehicle_type: vehicleType,
        passengers: passengers,
        fare: fare,
        distance: currentDistance.toFixed(2),
        duration: currentDuration,
        start_time: start_time,
        end_time: end_time
    };

    // Send ride request
    fetch('create_ride_request.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(rideData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('ကား #${driverId} ကို အောင်မြင်စွာ ရွေးချယ်ပြီးပါပြီ။', 'success');
            document.getElementById('driver-modal').style.display = 'none';

            if (data.ride_id) {
                checkRideStatus(data.ride_id);
            }
        } else {
            
        }
    })
    .catch(error => {
        console.error('Error creating ride:', error);
       
    });
}
        // Function to show notifications
        // Function to check for new notifications
        function checkForNotifications() {
            fetch('check_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.notifications.length > 0) {
                        // Display each notification
                        data.notifications.forEach(notification => {
                            showNotification(notification.message);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error checking notifications:', error);
                });
        }

        // Function to display a notification
        function showNotification(message) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'notification alert alert-info alert-dismissible fade show';
            notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
            notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

            // Add to page
            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        // Check for notifications every 10 seconds
        setInterval(checkForNotifications, 10000);

        // Also check on page load
        document.addEventListener('DOMContentLoaded', checkForNotifications);
    </script>
</body>

</html>