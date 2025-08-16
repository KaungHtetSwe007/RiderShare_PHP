<?php
session_start();

// Check if rider is logged in
if (!isset($_SESSION['rider_id'])) {
    header("Location: index.php");
    exit;
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
    <link href="assests/css/maincontent.css" rel="stylesheet">
</head>
<body>
    <main>
        <section class="py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="mb-0"><i class="fas fa-user me-2"></i>ရှင့်အကောင့်</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                                        <?php echo substr($_SESSION['rider_name'], 0, 1); ?>
                                    </div>
                                    <div class="ms-4">
                                        <h4 class="mb-1"><?php echo htmlspecialchars($_SESSION['rider_name']); ?></h4>
                                        <p class="mb-0 text-muted"><?php echo htmlspecialchars($_SESSION['rider_phone']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="card border-primary h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-car fa-3x text-primary mb-3"></i>
                                                <h5>ယာဉ်ခေါ်ရန်</h5>
                                                <p class="text-muted">သင့်လက်ရှိတည်နေရာမှ ယာဉ်ခေါ်ယူနိုင်ပါသည်</p>
                                                <a href="index.php" class="btn btn-primary">ယာဉ်ခေါ်ရန်</a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <div class="card border-success h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-history fa-3x text-success mb-3"></i>
                                                <h5>ခရီးစဉ်များ</h5>
                                                <p class="text-muted">သင်ယခင်စီးနင်းခဲ့သော ခရီးစဉ်များ</p>
                                                <a href="rider_history.php" class="btn btn-success">ကြည့်ရှုရန်</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <h5 class="mb-3"><i class="fas fa-cog me-2"></i>အကောင့် ပြင်ဆင်ခြင်း</h5>
                                    <div class="list-group">
                                        <a href="edit_profile.php" class="list-group-item list-group-item-action">
                                            <i class="fas fa-user-edit me-2"></i>ကိုယ်ရေးအချက်အလက်များ ပြင်ဆင်ရန်
                                        </a>
                                        <a href="change_password.php" class="list-group-item list-group-item-action">
                                            <i class="fas fa-lock me-2"></i>လျို့ဝှက်နံပါတ် ပြောင်းရန်
                                        </a>
                                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>အကောင့်မှ ထွက်ရန်
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <?php include('footer.php') ?>
</body>
</html>