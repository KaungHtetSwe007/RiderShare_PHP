<?php include('navbar.php') ?>
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Registration Success - RideShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Padauk', 'Noto Sans Myanmar', sans-serif;
        }
        
        .success-card {
            max-width: 600px;
            margin: 100px auto;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .success-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .highlight {
            color: #1fbad6;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card success-card">
            <div class="card-body text-center p-5">
                <i class="fas fa-check-circle success-icon"></i>
                <h2 class="mb-4">ယာဉ်မောင်း မှတ်ပုံတင်မှု အောင်မြင်ပါသည်!</h2>
                <p class="lead">
                    သင်၏ မှတ်ပုံတင်မှုကို လက်ခံရရှိပါသည်။ 
                    <span class="highlight">24-48 နာရီအတွင်း</span> ကျွန်ုပ်တို့၏ အဖွဲ့မှ ဆက်သွယ်ပေးပါမည်။
                </p>
                <p class="mb-4">
                    အတည်ပြုချက်အတွက် စောင့်ဆိုင်းနေစဉ် သင်၏ အကောင့်ကို 
                    <span class="highlight">ဆက်တင်များ</span> ကဏ္ဍတွင် စစ်ဆေးနိုင်ပါသည်။
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i> ပင်မစာမျက်နှာ
                    </a>
                    <a href="driver_dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-cog me-2"></i> ဆက်တင်များ
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php include('footer.php') ?>
</body>
</html>