                        <!-- Error Messages -->
                        <?php
                        session_start();

                        // Display signup errors if any
                        if (isset($_SESSION['signup_errors'])): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($_SESSION['signup_errors'] as $error): ?>
                                    <p class="mb-1"><?php echo $error; ?></p>
                                <?php endforeach; ?>
                                <?php unset($_SESSION['signup_errors']); ?>
                            </div>
                        <?php endif; ?>

                        <?php
                        // Display login errors if any
                        if (isset($_SESSION['login_errors'])): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($_SESSION['login_errors'] as $error): ?>
                                    <p class="mb-1"><?php echo $error; ?></p>
                                <?php endforeach; ?>
                                <?php unset($_SESSION['login_errors']); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Pre-fill form data if available -->
                        <?php
                        $signup_phone = isset($_SESSION['signup_data']['phone']) ? $_SESSION['signup_data']['phone'] : '';
                        $signup_name = isset($_SESSION['signup_data']['name']) ? $_SESSION['signup_data']['name'] : '';
                        $login_phone = isset($_SESSION['login_data']['phone']) ? $_SESSION['login_data']['phone'] : '';

                        unset($_SESSION['signup_data']);
                        unset($_SESSION['login_data']);
                        ?>

                        <?php include('navbar.php') ?>
                        <link href="assests/css/maincontent.css" rel="stylesheet">
                        <link href="assests/css/driverMultiBar.css" rel="stylesheet">
                        <link href="/assests/css/routeselect.css" rel="stylesheet">
                        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                        <script src="./assests/js/driverMultiBar.js"></script>
                        <script src="/assests/js/maincontent.js"></script>
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
                                                <a href="#" class="btn btn-primary btn-lg px-4 py-2" style="border-radius: 10px;">
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



                            <section class="authentication-section py-5 bg-light" id="driverForm">
                                <div class="container">
                                    <h2 class="section-title text-center mb-5">စတင်အသုံးပြုရန်</h2>

                                    <!-- Auth Tabs -->
                                    <div class="row justify-content-center">
                                        <div class="col-lg-10">
                                            <ul class="nav nav-pills mb-4 justify-content-center" id="authTab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="rider-tab" data-bs-toggle="pill" data-bs-target="#rider-auth" type="button">
                                                        <div style="color: black;"><i class="fas fa-user me-2"></i> ခရီးသည်အတွက်</div>
                                                    </button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="driver-tab" data-bs-toggle="pill" data-bs-target="#driver-auth" type="button">
                                                        <div style="color: black;"><i class="fas fa-car me-2"></i> ယာဉ်မောင်းအတွက်</div>
                                                    </button>
                                                </li>
                                            </ul>

                                            <!-- Tab Content -->
                                            <div class="tab-pane fade show active" id="rider-auth">
                                                <div class="text-center mb-4">
                                                    <h4><i class="fas fa-user me-2"></i>ခရီးသည်အတွက် အကောင့်ဝင်ရန်</h4>
                                                    <div class="location-badge">
                                                        <i class="fas fa-map-marker-alt me-2"></i>ပုသိမ်မြို့
                                                    </div>
                                                </div>

                                                <!-- Sign up form -->
                                                <form id="riderSignupForm" action="/auth/rider_signup.php" method="POST">
                                                    <div class="mb-3">
                                                        <label class="form-label">ဖုန်းနံပါတ်</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">+95</span>
                                                            <input type="tel" name="phone" class="form-control" placeholder="9xxxxxxxx"
                                                                pattern="[9]{1}[0-9]{8,9}" required value="<?php echo htmlspecialchars($signup_phone); ?>">
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">အမည်</label>
                                                        <div class="input-group">
                                                            <input type="text" name="name" class="form-control" placeholder="သင့်အမည်"
                                                                required value="<?php echo htmlspecialchars($signup_name); ?>">
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">လျို့ဝှက်နံပါတ်</label>
                                                        <div class="input-group">
                                                            <input type="password" name="password" class="form-control" placeholder="********" minlength="6" required>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <div class="input-group">
                                                            <button type="submit" class="btn w-100 py-2" style="background-color: goldenrod; color:white;">
                                                                <i class="fas fa-sign-in-alt me-2"></i> အကောင့်ဖွင့်ရန်
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3 text-center">
                                                        <div class="input-group">
                                                            <small>အကောင့်ရှိပြီးသားလား?
                                                                <a href="#" class="auth-toggle" id="showLogin">အကောင့်ဝင်ရန်</a>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </form>

                                                <!-- Login form (initially hidden) -->
                                                <form id="riderLoginForm" style="display:none;" action="/auth/rider_login.php" method="POST">
                                                    <div class="mb-3">
                                                        <label class="form-label">ဖုန်းနံပါတ်</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">+95</span>
                                                            <input type="tel" name="phone" class="form-control" placeholder="9xxxxxxxx"
                                                                pattern="[9]{1}[0-9]{8,9}" required value="<?php echo htmlspecialchars($login_phone); ?>">
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">လျို့ဝှက်နံပါတ်</label>
                                                        <div class="input-group">
                                                            <input type="password" name="password" class="form-control" placeholder="********" minlength="6" required>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <div class="input-group">
                                                            <button type="submit" class="btn w-100 py-2" style="background-color: goldenrod; color:white;">
                                                                <i class="fas fa-sign-in-alt me-2"></i> အကောင့်ဝင်ရန်
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3 text-center">
                                                        <div class="input-group">
                                                            <small>အကောင့်မရှိဘူးလား?
                                                                <a href="#" class="auth-toggle" id="showSignup">အကောင့်ဖွင့်ရန်</a>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- Driver Auth Tab -->
                                            <div class="tab-pane fade" id="driver-auth">
                                                <div class="container">
                                                    <div class="row justify-content-center">
                                                        <div class="col-md-10 col-lg-8">

                                                            <!-- Inside your driver form in index.php -->
                                                            <div class="alert-container">
                                                                <?php if (isset($_SESSION['driver_signup_errors']) && !empty($_SESSION['driver_signup_errors'])): ?>
                                                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                                        <strong>မှတ်ပုံတင်မှုတွင် အမှားတစ်ခုရှိပါသည်:</strong>
                                                                        <ul class="mb-0 mt-2">
                                                                            <?php foreach ($_SESSION['driver_signup_errors'] as $error): ?>
                                                                                <li><?= $error ?></li>
                                                                            <?php endforeach; ?>
                                                                        </ul>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                                    </div>
                                                                    <?php
                                                                    // Keep the form data for repopulation
                                                                    $driver_form_data = $_SESSION['driver_form_data'] ?? [];
                                                                    unset($_SESSION['driver_signup_errors']);
                                                                    ?>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="form-container">
                                                                <div class="header-section">
                                                                    <h4><i class="fas fa-car me-2"></i>ယာဉ်မောင်း မှတ်ပုံတင်ခြင်း</h4>

                                                                </div>

                                                                <!-- Progress Bar -->
                                                                <div class="progress-container">
                                                                    <div class="progress" style="height: 10px;">
                                                                        <div class="progress-bar" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                                                                    </div>
                                                                    <div class="step-indicators">
                                                                        <div class="step-indicator active">1</div>
                                                                        <div class="step-indicator">2</div>
                                                                        <div class="step-indicator">3</div>
                                                                        <div class="step-indicator">4</div>
                                                                        <div class="step-indicator">5</div>
                                                                    </div>
                                                                </div>

                                                                <!-- Form Steps -->
                                                                <form id="driverSignupForm" action="auth/driver_signup.php" method="POST" enctype="multipart/form-data">
                                                                    <!-- Step 1: Basic Information -->
                                                                    <div class="step active" id="step1">
                                                                        <h4 class="form-section-title"><i class="fas fa-user-circle me-2"></i>အခြေခံ အချက်အလက်များ</h4>

                                                                        <div class="mb-4">
                                                                            <label class="form-label">ဖုန်းနံပါတ်</label>
                                                                            <div class="input-group">
                                                                                <span class="input-group-text">+95</span>
                                                                                <input type="tel" name="phone" class="form-control" placeholder="9xxxxxxxx"
                                                                                    pattern="[9]{1}[0-9]{8,9}" required>
                                                                            </div>
                                                                            <small class="form-text text-muted">ဥပမာ - 912345678</small>
                                                                            <div class="invalid-feedback">ဖုန်းနံပါတ် မှန်ကန်စွာ ဖြည့်သွင်းပါ (9xxxxxxxx)</div>
                                                                        </div>

                                                                        <div class="mb-4">
                                                                            <label class="form-label">အမည်</label>
                                                                            <div class="input-group">
                                                                                <input type="text" name="name" class="form-control" placeholder="သင့်အမည် အပြည့်အစုံ" required>
                                                                            </div>
                                                                            <div class="invalid-feedback">ကျေးဇူးပြု၍ သင့်အမည်ကို ဖြည့်သွင်းပါ</div>
                                                                        </div>

                                                                        <div class="mb-4">
                                                                            <label class="form-label">ယာဉ်အမျိုးအစား</label>
                                                                            <div class="input-group">
                                                                                <select class="form-select" name="vehicle_type" required>
                                                                                    <option value="" selected disabled>ရွေးချယ်ပါ</option>
                                                                                    <option value="Car">ကား (Toyota, Suzuki)</option>
                                                                                    <option value="Oway">အိုးဝေ (Oway)</option>
                                                                                    <option value="Motorcycle">ဆိုင်ကယ် (Motorcycle)</option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="invalid-feedback">ယာဉ်အမျိုးအစား ရွေးချယ်ပါ</div>
                                                                        </div>
                                                                        <div class="mb-3 text-center">
                                                                            <div class="input-group">
                                                                                <small>အကောင့်ရှိပြီးသားလား?
                                                                                    <a href="/driver_login.php" class="auth-toggle" id="showLogin">အကောင့်ဝင်ရန်</a>
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end mt-5">
                                                                            <div class="input-group">
                                                                                <button type="button" class="btn btn-next btn-action next-step" data-next="2">
                                                                                    နောက်တစ်ဆင့် <i class="fas fa-arrow-right ms-2"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Step 2: Driver Information -->
                                                                    <div class="step" id="step2">
                                                                        <h4 class="form-section-title"><i class="fas fa-id-card me-2"></i>ယာဉ်မောင်း အချက်အလက်များ</h4>

                                                                        <div class="row">
                                                                            <div class="col-md-6 mb-4">
                                                                                <label class="form-label">မှတ်ပုံတင်အမှတ် (NRC)</label>
                                                                                <div class="input-group">
                                                                                    <input type="text" name="nrc" class="form-control" placeholder="ဥပမာ - ၁၂/ကမန(နိုင်)၁၂၃၄၅၆" required>
                                                                                </div>
                                                                                <div class="invalid-feedback">NRC နံပါတ် ဖြည့်သွင်းပါ</div>
                                                                            </div>

                                                                            <div class="col-md-6 mb-4">
                                                                                <label class="form-label">မွေးသက္ကရာဇ်</label>
                                                                                <div class="input-group">
                                                                                    <input type="date" name="dob" class="form-control" required max="">
                                                                                </div>
                                                                                <div class="invalid-feedback">မွေးနေ့ ရွေးချယ်ပါ</div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="mb-4">
                                                                            <label class="form-label">လိပ်စာ</label>
                                                                            <div class="input-group">
                                                                                <textarea class="form-control" name="address" rows="3" placeholder="လက်ရှိနေထိုင်ရာ လိပ်စာအပြည့်အစုံ" required></textarea>
                                                                            </div>
                                                                            <div class="invalid-feedback">လိပ်စာ ဖြည့်သွင်းပါ</div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-6 mb-4">
                                                                                <label class="form-label">ယာဉ်မောင်းလိုင်စင်အမှတ်</label>
                                                                                <div class="input-group">
                                                                                    <input type="text" name="license_number" class="form-control" placeholder="ဥပမာ - 0000/YGN/12345" required>
                                                                                </div>
                                                                                <div class="invalid-feedback">လိုင်စင်အမှတ် ဖြည့်သွင်းပါ</div>
                                                                            </div>

                                                                            <div class="col-md-6 mb-4">
                                                                                <label class="form-label">လိုင်စင်သက်တမ်း ကုန်ဆုံးရက်</label>
                                                                                <div class="input-group">
                                                                                    <input type="date" name="license_expiry" class="form-control" required min="">
                                                                                </div>
                                                                                <div class="invalid-feedback">ကုန်ဆုံးရက် ရွေးချယ်ပါ</div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="d-flex justify-content-between mt-5">
                                                                            <button type="button" class="btn btn-prev btn-action prev-step" data-prev="1">
                                                                                <i class="fas fa-arrow-left me-2"></i> နောက်သို့
                                                                            </button>
                                                                            <div class="input-group">
                                                                                <button type="button" class="btn btn-next btn-action next-step" data-next="3">
                                                                                    နောက်တစ်ဆင့် <i class="fas fa-arrow-right ms-2"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Step 3: Vehicle Information -->
                                                                    <div class="step" id="step3">
                                                                        <h4 class="form-section-title"><i class="fas fa-car me-2"></i>ယာဉ် အချက်အလက်များ</h4>

                                                                        <div class="mb-4">
                                                                            <label class="form-label">ယာဉ်မှတ်ပုံတင်အမှတ်</label>
                                                                            <div class="input-group">
                                                                                <input type="text" name="vehicle_registration" class="form-control" placeholder="ဥပမာ - 1Y-2345" required>
                                                                            </div>
                                                                            <div class="invalid-feedback">ယာဉ်မှတ်ပုံတင်အမှတ် ဖြည့်သွင်းပါ</div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-6 mb-4">
                                                                                <label class="form-label">ယာဉ်အမျိုးအစား</label>
                                                                                <div class="input-group">
                                                                                    <input type="text" name="vehicle_model" class="form-control" placeholder="ဥပမာ - Toyota Probox" required>
                                                                                </div>
                                                                                <div class="invalid-feedback">ယာဉ်အမျိုးအစား ဖြည့်သွင်းပါ</div>
                                                                            </div>

                                                                            <div class="col-md-6 mb-4">
                                                                                <label class="form-label">ထုတ်လုပ်သည့်နှစ်</label>
                                                                                <div class="input-group">
                                                                                    <input type="number" name="vehicle_year" class="form-control" placeholder="ဥပမာ - 2018" min="1990" max="2025" required>
                                                                                </div>
                                                                                <div class="invalid-feedback">ထုတ်လုပ်သည့်နှစ် မှန်ကန်စွာ ဖြည့်သွင်းပါ (1990-2025)</div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-6 mb-4">
                                                                                <label class="form-label">ယာဉ်အရောင်</label>
                                                                                <div class="input-group">
                                                                                    <input type="text" name="vehicle_color" class="form-control" placeholder="ဥပမာ - အဖြူရောင်" required>
                                                                                </div>
                                                                                <div class="invalid-feedback">ယာဉ်အရောင် ဖြည့်သွင်းပါ</div>
                                                                            </div>

                                                                            <div class="col-md-6 mb-4">
                                                                                <label class="form-label">အင်ဂျင်အမှတ်</label>
                                                                                <div class="input-group">
                                                                                    <input type="text" name="engine_number" class="form-control" placeholder="ဥပမာ - 2NZ-1234567" required>
                                                                                </div>
                                                                                <div class="invalid-feedback">အင်ဂျင်အမှတ် ဖြည့်သွင်းပါ</div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="d-flex justify-content-between mt-5">
                                                                            <button type="button" class="btn btn-prev btn-action prev-step" data-prev="2">
                                                                                <i class="fas fa-arrow-left me-2"></i> နောက်သို့
                                                                            </button>
                                                                            <div class="input-group">
                                                                                <button type="button" class="btn btn-next btn-action next-step" data-next="4">
                                                                                    နောက်တစ်ဆင့် <i class="fas fa-arrow-right ms-2"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Step 4: Document Upload -->
                                                                    <div class="step" id="step4">
                                                                        <h4 class="form-section-title"><i class="fas fa-file-upload me-2"></i>လိုအပ်သော စာရွက်စာတမ်းများ</h4>
                                                                        <p class="text-muted mb-4">အောက်ပါစာရွက်စာတမ်းများအား ရှင်းလင်းပြတ်သားစွာဖြင့် တင်ပြရန်</p>

                                                                        <div class="row">
                                                                            <div class="col-md-6 mb-4">
                                                                                <div class="document-upload">
                                                                                    <i class="fas fa-id-card"></i>
                                                                                    <h5>နိုင်ငံသားစိစစ်ရေးကတ်ပြား</h5>
                                                                                    <p class="text-muted">ရှေ့ဖက်</p>
                                                                                    <div class="input-group">
                                                                                        <input type="file" name="nrc_front" id="nrc_front" class="d-none" accept="image/*" required>
                                                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('nrc_front').click()">
                                                                                            ဓာတ်ပုံတင်ရန်
                                                                                        </button>
                                                                                    </div>
                                                                                    <div class="file-info" id="nrc_front_info"></div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-6 mb-4">
                                                                                <div class="document-upload">
                                                                                    <i class="fas fa-id-card"></i>
                                                                                    <h5>နိုင်ငံသားစိစစ်ရေးကတ်ပြား</h5>
                                                                                    <p class="text-muted">နောက်ဖက်</p>
                                                                                    <div class="input-group">
                                                                                        <input type="file" name="nrc_back" id="nrc_back" class="d-none" accept="image/*" required>
                                                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('nrc_back').click()">
                                                                                            ဓာတ်ပုံတင်ရန်
                                                                                        </button>
                                                                                    </div>
                                                                                    <div class="file-info" id="nrc_back_info"></div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-6 mb-4">
                                                                                <div class="document-upload">
                                                                                    <i class="fas fa-id-badge"></i>
                                                                                    <h5>ယာဉ်မောင်းလိုင်စင်</h5>
                                                                                    <p class="text-muted">ရှေ့ဖက်</p>
                                                                                    <div class="input-group">
                                                                                        <input type="file" name="license_front" id="license_front" class="d-none" accept="image/*" required>
                                                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('license_front').click()">
                                                                                            ဓာတ်ပုံတင်ရန်
                                                                                        </button>
                                                                                    </div>
                                                                                    <div class="file-info" id="license_front_info"></div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-6 mb-4">
                                                                                <div class="document-upload">
                                                                                    <i class="fas fa-id-badge"></i>
                                                                                    <h5>ယာဉ်မောင်းလိုင်စင်</h5>
                                                                                    <p class="text-muted">နောက်ဖက်</p>
                                                                                    <div class="input-group">
                                                                                        <input type="file" name="license_back" id="license_back" class="d-none" accept="image/*" required>
                                                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('license_back').click()">
                                                                                            ဓာတ်ပုံတင်ရန်
                                                                                        </button>
                                                                                    </div>
                                                                                    <div class="file-info" id="license_back_info"></div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-6 mb-4">
                                                                                <div class="document-upload">
                                                                                    <i class="fas fa-file-contract"></i>
                                                                                    <h5>ယာဉ်မှတ်ပုံတင်စာအုပ်</h5>
                                                                                    <p class="text-muted">(Blue Book) ဓာတ်ပုံ</p>
                                                                                    <div class="input-group">
                                                                                        <input type="file" name="bluebook" id="bluebook" class="d-none" accept="image/*" required>
                                                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('bluebook').click()">
                                                                                            ဓာတ်ပုံတင်ရန်
                                                                                        </button>
                                                                                    </div>
                                                                                    <div class="file-info" id="bluebook_info"></div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-md-6 mb-4">
                                                                                <div class="document-upload">
                                                                                    <i class="fas fa-car"></i>
                                                                                    <h5>ယာဉ်၏ ဓာတ်ပုံ</h5>
                                                                                    <p class="text-muted">အရှေ့၊ အနောက်၊ ဘယ်၊ ညာ</p>
                                                                                    <div class="input-group">
                                                                                        <input type="file" name="vehicle_photos[]" id="vehicle_photos" class="d-none" accept="image/*" multiple required>
                                                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('vehicle_photos').click()">
                                                                                            ဓာတ်ပုံတင်ရန်
                                                                                        </button>
                                                                                    </div>
                                                                                    <div class="file-info" id="vehicle_photos_info"></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="d-flex justify-content-between mt-4">
                                                                            <button type="button" class="btn btn-prev btn-action prev-step" data-prev="3">
                                                                                <i class="fas fa-arrow-left me-2"></i> နောက်သို့
                                                                            </button>
                                                                            <div class="input-group">
                                                                                <button type="button" class="btn btn-next btn-action next-step" data-next="5">
                                                                                    နောက်တစ်ဆင့် <i class="fas fa-arrow-right ms-2"></i>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Step 5: Review and Submit -->
                                                                    <div class="step" id="step5">
                                                                        <h4 class="form-section-title"><i class="fas fa-check-circle me-2"></i>အတည်ပြုခြင်းနှင့် လက်မှတ်ရေးထိုးခြင်း</h4>

                                                                        <div class="alert alert-info">
                                                                            <i class="fas fa-info-circle me-2"></i>ကျေးဇူးပြု၍ သင် ဖြည့်သွင်းထားသော အချက်အလက်များကို ပြန်လည်စစ်ဆေးပါ။
                                                                        </div>

                                                                        <div class="review-container mt-4 mb-4">
                                                                            <div class="review-item d-flex">
                                                                                <div class="review-label">ဖုန်းနံပါတ်:</div>
                                                                                <div class="review-value" id="review-phone"></div>
                                                                            </div>

                                                                            <div class="review-item d-flex">
                                                                                <div class="review-label">ယာဉ်အမျိုးအစား:</div>
                                                                                <div class="review-value" id="review-vehicle-type"></div>
                                                                            </div>
                                                                            <div class="review-item d-flex">
                                                                                <div class="review-label">NRC အမှတ်:</div>
                                                                                <div class="review-value" id="review-nrc"></div>
                                                                            </div>
                                                                            <div class="review-item d-flex">
                                                                                <div class="review-label">လိုင်စင်အမှတ်:</div>
                                                                                <div class="review-value" id="review-license"></div>
                                                                            </div>
                                                                            <div class="review-item d-flex">
                                                                                <div class="review-label">ယာဉ်မှတ်ပုံတင်အမှတ်:</div>
                                                                                <div class="review-value" id="review-registration"></div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="form-check mt-4 mb-4">
                                                                            <div class="input-group">
                                                                                <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                                                            </div>
                                                                            <label class="form-check-label" for="terms">
                                                                                ကျွန်ုပ်သည် RideShare ၏ <a href="#" class="text-primary">ဝန်ဆောင်မှုစည်းမျဉ်းများ</a> နှင့်
                                                                                <a href="#" class="text-primary">ကိုယ်ရေးကိုယ်တာမူဝါဒ</a> တို့ကို ဖတ်ရှုပြီး သဘောတူပါသည်။
                                                                            </label>
                                                                            <div class="invalid-feedback">ကျေးဇူးပြု၍ စည်းမျဉ်းများကို သဘောတူညီပါ</div>
                                                                        </div>

                                                                        <div class="d-flex justify-content-between mt-5">
                                                                            <button type="button" class="btn btn-prev btn-action prev-step" data-prev="4">
                                                                                <i class="fas fa-arrow-left me-2"></i> နောက်သို့
                                                                            </button>
                                                                            <div class="input-group">
                                                                                <button type="submit" class="btn btn-submit btn-action">
                                                                                    <i class="fas fa-paper-plane me-2"></i> မှတ်ပုံတင်ရန်
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                                </div>
                            </section>

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

                            <!-- Stats Section -->
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
                                                <p>"RideShare သည် မြို့တွင်းသွားလာရေးကို အပြောင်းအလဲဖြစ်စေပါသည်။ ဆိုက်ကားမောင်းများသည် အမြဲပညာရှိပြီး အက်ပ်အသုံးပြုရန် အလွန်လွယ်ကူပါသည်။"</p>
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
                                                <p>"နေ့စဉ်သွားလာရန်အတွက် RideShare ကိုအသုံးပြုပါသည်။ ယုံကြည်စိတ်ချရပြီး ကားရပ်နားရန်ထက် ပိုမိုကောင်းမွန်ပါသည်။"</p>
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