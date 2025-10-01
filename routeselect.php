<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RideShare - Complete Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Padauk:wght@400;700&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color), #0d96ad);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
        }
        
        .map-container {
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            position: relative;
        }
        
        #route-map {
            height: 100%;
            width: 100%;
        }
        
        .location-inputs {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
        }
        
        .route-summary {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--card-shadow);
        }
        
        .input-group-icon {
            background-color: #f8f9fa;
            border-right: none;
        }
        
        .form-control:focus {
            box-shadow: none;
            border-color: var(--primary-color);
        }
        
        .location-marker {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .marker-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: white;
            font-size: 14px;
        }
        
        .marker-start {
            background-color: var(--accent-color);
        }
        
        .marker-end {
            background-color: #e74c3c;
        }
        
        .route-details {
            margin-top: 20px;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #0d96ad;
        }
        
        .suggested-locations {
            margin-top: 20px;
        }
        
        .location-chip {
            display: inline-block;
            background-color: #f1f1f1;
            padding: 5px 15px;
            border-radius: 20px;
            margin-right: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .location-chip:hover {
            background-color: var(--primary-color);
            color: white;
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
        
        .map-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .instruction {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary-color);
        }
        
        .btn-map-control {
            margin-bottom: 5px;
            width: 100%;
            text-align: left;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        
        .suggestion-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .suggestion-item:hover {
            background-color: #f0f0f0;
        }
        
        .input-with-button {
            display: flex;
            gap: 10px;
        }
        
        .input-with-button input {
            flex: 1;
        }
        
        .input-with-button button {
            width: auto;
        }
        
        /* Driver Modal Styles */
        .driver-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            overflow-y: auto;
        }
        
        .driver-modal-content {
            background-color: white;
            margin: 50px auto;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            animation: modalFadeIn 0.3s;
        }
        
        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        .modal-header {
            padding: 15px 20px;
            background: linear-gradient(135deg, var(--primary-color), #0d96ad);
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
        }
        
        .close-modal {
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .searching-drivers {
            text-align: center;
            padding: 30px;
        }
        
        .searching-animation {
            font-size: 40px;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .drivers-list {
            display: none;
        }
        
        .driver-card {
            display: flex;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .driver-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .driver-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
            border: 3px solid var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f1f1f1;
            color: var(--primary-color);
            font-size: 24px;
        }
        
        .driver-info {
            flex-grow: 1;
        }
        
        .driver-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .driver-rating {
            color: #f8b400;
            margin-bottom: 5px;
        }
        
        .driver-car {
            color: #666;
            margin-bottom: 5px;
        }
        
        .driver-distance {
            color: #666;
        }
        
        .driver-actions {
            display: flex;
            align-items: center;
        }
        
        .btn-select-driver {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-select-driver:hover {
            background-color: #0d96ad;
        }
        
        .estimated-arrival {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            text-align: center;
            font-weight: bold;
        }
        
        .no-drivers {
            text-align: center;
            padding: 30px;
            display: none;
        }
        
        .pulse {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-car"></i> RideShare</h1>
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
                                    <option value="car">တက်(စ်)ီ</option>
                                    <option value="premium">ပရီမီယံ</option>
                                    <option value="xl">အိုးဝေး</option>
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
    
    <!-- Leaflet JS -->
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
            routeLine = L.polyline([defaultStart, defaultEnd], {color: 'blue'}).addTo(map);
            
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
                
                routeLine = L.polyline([defaultStart, defaultEnd], {color: 'blue'}).addTo(map);
                
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
                    routeLine = L.polyline([startLatLng, endLatLng], {color: 'blue'}).addTo(map);
                    
                    // Update map view to show both markers
                    map.fitBounds(L.latLngBounds(startLatLng, endLatLng));
                    
                    // Update route details
                    updateRouteDetails(startLatLng, endLatLng);
                }
            }
            
            // Function to calculate and update route details
            function updateRouteDetails(start, end) {
                // Calculate distance using Haversine formula
                const distance = calculateDistance(start.lat, start.lng, end.lat, end.lng);
                
                // Estimate duration based on distance (assuming average speed of 30 km/h)
                const durationMinutes = Math.round((distance / 30) * 60);
                
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
                    Math.sin(dLat/2) * Math.sin(dLat/2) +
                    Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
                    Math.sin(dLon/2) * Math.sin(dLon/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
                return R * c; // Distance in km
            }
            
            function deg2rad(deg) {
                return deg * (Math.PI/180);
            }
            
            // Calculate fare based on distance and vehicle type
            function calculateFare(distance, vehicleType) {
                let baseFare, ratePerKm;
                
                switch(vehicleType) {
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
            
            // Event listener for confirm ride button
            document.getElementById('confirm-ride').addEventListener('click', function() {
                const pickup = document.getElementById('pickup-input').value;
                const dropoff = document.getElementById('dropoff-input').value;
                const vehicleType = document.getElementById('vehicle-type').value;
                
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
                
                // Fetch available drivers from server
                fetchAvailableDrivers(vehicleType);
            });
            
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
                    
                    driverCard.innerHTML = `
                        <div class="driver-avatar">
                            ${initials}
                        </div>
                        <div class="driver-info">
                            <div class="driver-name">${driver.name}</div>
                            <div class="driver-rating">
                                ${generateStarRating(driver.avg_rating)} 
                                ${driver.avg_rating} (သုံးသပ်ချက် ${driver.completed_rides})
                            </div>
                            <div class="driver-car">${getVehicleTypeName(driver.vehicle_type)} • ${driver.vehicle_model} • ${driver.registration_no}</div>
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
                switch(type) {
                    case 'car': return 'တက်(စ်)ီ';
                    case 'premium': return 'ကား';
                    case 'xl': return 'အိုးဝေး';
                    default: return type;
                }
            }
            
            // Function to handle driver selection
            function selectDriver(driverId) {
                const pickup = document.getElementById('pickup-input').value;
                const dropoff = document.getElementById('dropoff-input').value;
                const vehicleType = document.getElementById('vehicle-type').value;
                const passengers = document.getElementById('passenger-count').value;
                const fare = document.getElementById('fare-value').textContent;
                
                // In a real application, you would send this data to your server
                alert(`ကား #${driverId} ကို အောင်မြင်စွာ ရွေးချယ်ပြီးပါပြီ။ ကားရောက်ရှိရန် စောင့်ဆိုင်းပေးပါ။`);
                document.getElementById('driver-modal').style.display = 'none';
                
                // Here you would typically:
                // 1. Create a ride record in the database
                // 2. Send notification to the driver
                // 3. Update the UI to show ride status
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
function fetchAvailableDrivers(vehicleType, pickupLocation) {
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

// Function to handle driver selection
function selectDriver(driverId) {
    const pickup = document.getElementById('pickup-input').value;
    const dropoff = document.getElementById('dropoff-input').value;
    const vehicleType = document.getElementById('vehicle-type').value;
    const passengers = document.getElementById('passenger-count').value;
    const fare = document.getElementById('fare-value').textContent.replace(' Ks', '').replace(/,/g, '');
    
    // Validate inputs
    if (!pickup || !dropoff) {
        showNotification('ကျေးဇူးပြု၍ စတင်မည့်နေရာနှင့် ဆုံးမည့်နေရာကို ထည့်သွင်းပါ', 'error');
        return;
    }
    
    // Create ride request
    const rideData = {
        driver_id: driverId,
        pickup_location: pickup,
        dropoff_location: dropoff,
        vehicle_type: vehicleType,
        passengers: passengers,
        fare: fare
    };
    
    console.log("Sending ride request:", rideData);
    
    // Send ride request to server
    fetch('create_ride_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(rideData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log("Response from server:", data);
        if (data.success) {
            showNotification(`ဆိုက်ကား #${driverId} ကို အောင်မြင်စွာ ရွေးချယ်ပြီးပါပြီ။ ကားရောက်ရှိရန် စောင့်ဆိုင်းပေးပါ။`, 'success');
            document.getElementById('driver-modal').style.display = 'none';
            
            // Start checking ride status
            if (data.ride_id) {
                checkRideStatus(data.ride_id);
            }
        } else {
            showNotification('ခရီးစဉ်တောင်းဆိုရာတွင် ပြဿနာတစ်ခုဖြစ်နေပါသည်: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error creating ride:', error);
        showNotification('ခရီးစဉ်တောင်းဆိုရာတွင် ပြဿနာတစ်ခုဖြစ်နေပါသည်။', 'error');
    });
}


//---------------------------------------------------------------
// Function to handle driver selection
function selectDriver(driverId) {
    const pickup = document.getElementById('pickup-input').value;
    const dropoff = document.getElementById('dropoff-input').value;
    const vehicleType = document.getElementById('vehicle-type').value;
    const passengers = document.getElementById('passenger-count').value;
    const fare = document.getElementById('fare-value').textContent.replace(' Ks', '');
    
    // Create ride request
    const rideData = {
        driver_id: driverId,
        pickup_location: pickup,
        dropoff_location: dropoff,
        vehicle_type: vehicleType,
        passengers: passengers,
        fare: fare
    };
    
    // Send ride request to server
    fetch('create_ride_request.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(rideData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`ဆိုက်ကား #${driverId} ကို အောင်မြင်စွာ ရွေးချယ်ပြီးပါပြီ။ ကားရောက်ရှိရန် စောင့်ဆိုင်းပေးပါ။`, 'success');
            document.getElementById('driver-modal').style.display = 'none';
            
            // Start checking ride status
            if (data.ride_id) {
                checkRideStatus(data.ride_id);
            }
        } else {
            showNotification('ခရီးစဉ်တောင်းဆိုရာတွင် ပြဿနာတစ်ခုဖြစ်နေပါသည်။', 'error');
        }
    })
    .catch(error => {
        console.error('Error creating ride:', error);
        showNotification('ခရီးစဉ်တောင်းဆိုရာတွင် ပြဿနာတစ်ခုဖြစ်နေပါသည်။', 'error');
    });
}

// Function to show notifications
function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px;
        border-radius: 5px;
        color: white;
        z-index: 10000;
        font-weight: bold;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#27ae60';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#e74c3c';
    } else {
        notification.style.backgroundColor = '#3498db';
    }
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
    </script>
</body>
</html>