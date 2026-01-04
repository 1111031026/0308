<?php
session_start();
require_once 'db_connect.php';

// 檢查用戶是否登入
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// 處理表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $latitude = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? floatval($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? floatval($_POST['longitude']) : null;
    $location_name = trim($_POST['location_name'] ?? '');

    if (empty($title) || empty($content)) {
        $error_message = "標題和內容不能為空。";
    } else {
        $post_date = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO nearby_post (Title, Content, UserID, Latitude, Longitude, LocationName, PostDate) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssiddss", $title, $content, $user_id, $latitude, $longitude, $location_name, $post_date);
            if ($stmt->execute()) {
                // 給予點數獎勵
                $user_role = $_SESSION['role'] ?? 'Student';
                $points_table = ($user_role === 'Teacher') ? 'teacher_achievement' : 'achievement';
                
                // 從系統設定讀取發表貼文的點數（預設 5 點）
                $points_per_post = 5; // 預設值
                $settings_stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'points_per_nearby_post'");
                if ($settings_stmt) {
                    $settings_stmt->execute();
                    $settings_result = $settings_stmt->get_result();
                    if ($settings_row = $settings_result->fetch_assoc()) {
                        $points_per_post = intval($settings_row['setting_value']);
                    }
                    $settings_stmt->close();
                }
                
                // 更新用戶點數
                $updatePointsSql = "INSERT INTO {$points_table} (UserID, TotalPoints";
                if ($user_role === 'Student') {
                    $updatePointsSql .= ", ArticlesViewed, ChoiceQuestionsCorrect, TFQuestionsCorrect, FillinQuestionsCorrect) VALUES (?, ?, 0, 0, 0, 0) ON DUPLICATE KEY UPDATE TotalPoints = TotalPoints + ?";
                } else {
                    $updatePointsSql .= ", SDG13ArticlesPublished, SDG14ArticlesPublished, SDG15ArticlesPublished) VALUES (?, ?, 0, 0, 0) ON DUPLICATE KEY UPDATE TotalPoints = TotalPoints + ?";
                }
                
                $updatePointsStmt = $conn->prepare($updatePointsSql);
                if ($updatePointsStmt) {
                    if ($user_role === 'Student') {
                        $updatePointsStmt->bind_param("iii", $user_id, $points_per_post, $points_per_post);
                    } else {
                        $updatePointsStmt->bind_param("iii", $user_id, $points_per_post, $points_per_post);
                    }
                    $updatePointsStmt->execute();
                    $updatePointsStmt->close();
                }
                
                $success_message = "貼文已成功發佈！獲得 {$points_per_post} 點數！";
                // 重定向到附近討論頁面
                header('Location: nearby_discussion.php?success=1');
                exit;
            } else {
                $error_message = "發佈失敗，請稍後再試。錯誤：" . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "資料庫錯誤，請稍後再試。錯誤：" . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>發表附近討論</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/post.css">
    <link rel="stylesheet" href="../css/nearby_post.css">
    <link rel="stylesheet" href="../css/nav3.css">
    <!-- Leaflet.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .location-section {
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .location-toggle {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .location-toggle input[type="checkbox"] {
            margin-right: 10px;
            width: 20px;
            height: 20px;
        }
        .location-inputs {
            display: none;
            margin-top: 15px;
        }
        .location-inputs.active {
            display: block;
        }
        #map {
            width: 100%;
            height: 300px;
            border-radius: 8px;
            margin-top: 15px;
            border: 2px solid #e0e0e0;
        }
        .location-info {
            margin-top: 10px;
            padding: 10px;
            background: #e8f5e9;
            border-radius: 5px;
            font-size: 14px;
            color: #2e7d32;
        }
        .get-location-btn {
            background: #2196F3;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }
        .get-location-btn:hover {
            background: #1976D2;
        }
        .location-options {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .location-option-btn {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        .location-option-btn:hover {
            border-color: #2196F3;
            background: #f5f5f5;
        }
        .location-option-btn.active {
            background: #2196F3;
            color: white;
            border-color: #2196F3;
        }
        .location-section-content {
            margin-bottom: 15px;
        }
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        .search-box input {
            flex: 1;
        }
        .search-btn {
            padding: 8px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .search-btn:hover {
            background: #45a049;
        }
        .search-results {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background: white;
            display: none;
        }
        .search-results.active {
            display: block;
        }
        .search-result-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }
        .search-result-item:hover {
            background: #f5f5f5;
        }
        .search-result-item:last-child {
            border-bottom: none;
        }
        .search-result-item .result-name {
            font-weight: 500;
            color: #333;
            margin-bottom: 3px;
        }
        .search-result-item .result-address {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div style="height: 70px;"></div>

    <div class="post-container">
        <h2>發表附近討論</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form class="post-form" method="POST" action="nearby_post.php" id="postForm">
            <div class="form-group">
                <label for="title">標題</label>
                <input type="text" id="title" name="title" required class="form-control" placeholder="例如：今天在公園看到美麗的鳥類">
            </div>
            
            <div class="form-group">
                <label for="content">內容</label>
                <textarea id="content" name="content" rows="10" required class="form-control" placeholder="分享你的環保行動或觀察..."></textarea>
            </div>

            <div class="location-section">
                <div class="location-toggle">
                    <input type="checkbox" id="shareLocation" name="share_location">
                    <label for="shareLocation">分享位置（讓附近的用戶看到你的貼文）</label>
                </div>
                
                <div class="location-inputs" id="locationInputs">
                    <div class="location-options">
                        <button type="button" class="location-option-btn active" id="useCurrentLocationBtn">
                            <i class="fas fa-map-marker-alt"></i> 使用目前位置
                        </button>
                        <button type="button" class="location-option-btn" id="searchLocationBtn">
                            <i class="fas fa-search"></i> 搜尋其他地點
                        </button>
                    </div>
                    
                    <!-- 目前位置選項 -->
                    <div id="currentLocationSection" class="location-section-content">
                        <button type="button" class="get-location-btn" id="getLocationBtn">
                            <i class="fas fa-crosshairs"></i> 取得目前位置
                        </button>
                    </div>
                    
                    <!-- 搜尋地點選項 -->
                    <div id="searchLocationSection" class="location-section-content" style="display: none;">
                        <div class="search-box">
                            <input type="text" id="locationSearch" class="form-control" placeholder="搜尋地點，例如：小琉球、墾丁、花蓮...">
                            <button type="button" class="search-btn" id="searchBtn">
                                <i class="fas fa-search"></i> 搜尋
                            </button>
                        </div>
                        <div id="searchResults" class="search-results"></div>
                    </div>
                    
                    <div id="map"></div>
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    <div class="form-group" style="margin-top: 15px;">
                        <label for="location_name">地點名稱（選填）</label>
                        <input type="text" id="location_name" name="location_name" class="form-control" placeholder="例如：小琉球海灘、大安森林公園">
                    </div>
                    <div class="location-info" id="locationInfo" style="display: none;">
                        <i class="fas fa-check-circle"></i> 位置已設定
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">發佈貼文</button>
                <a href="nearby_discussion.php" class="cancel-btn">取消並返回附近討論</a>
            </div>
        </form>
    </div>

    <script>
        // Geoapify API Key
        const geoapifyApiKey = '909bbe471da94f1a8eee1bd450c5c4bf';
        
        let map;
        let marker;
        let userLocation = null;

        // Cookie 工具函數
        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        // 初始化地圖（使用 Geoapify + Leaflet）
        function initMap() {
            // 預設位置（台北）
            const defaultLocation = [25.0330, 121.5654];
            
            // 建立地圖
            map = L.map('map').setView(defaultLocation, 15);
            
            // 取得地圖樣式（從 Cookie 或預設值）
            const mapStyle = getCookie('map_style') || 'osm-bright';
            L.tileLayer(`https://maps.geoapify.com/v1/tile/${mapStyle}/{z}/{x}/{y}.png?apiKey=${geoapifyApiKey}`, {
                attribution: 'Powered by <a href="https://www.geoapify.com/" target="_blank">Geoapify</a>',
                maxZoom: 19
            }).addTo(map);

            // 點擊地圖設置位置
            map.on('click', function(e) {
                setLocation(e.latlng.lat, e.latlng.lng);
            });
        }

        // 設置位置
        function setLocation(lat, lng) {
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(map);
                
                // 拖動標記時更新位置
                marker.on('dragend', function(e) {
                    const pos = marker.getLatLng();
                    document.getElementById('latitude').value = pos.lat;
                    document.getElementById('longitude').value = pos.lng;
                });
            }
            
            map.setView([lat, lng], 15);
            document.getElementById('locationInfo').style.display = 'block';
        }

        // 切換位置選項（目前位置 vs 搜尋地點）
        document.getElementById('useCurrentLocationBtn').addEventListener('click', function() {
            this.classList.add('active');
            document.getElementById('searchLocationBtn').classList.remove('active');
            document.getElementById('currentLocationSection').style.display = 'block';
            document.getElementById('searchLocationSection').style.display = 'none';
            document.getElementById('searchResults').classList.remove('active');
        });
        
        document.getElementById('searchLocationBtn').addEventListener('click', function() {
            this.classList.add('active');
            document.getElementById('useCurrentLocationBtn').classList.remove('active');
            document.getElementById('currentLocationSection').style.display = 'none';
            document.getElementById('searchLocationSection').style.display = 'block';
        });
        
        // 取得目前位置
        document.getElementById('getLocationBtn').addEventListener('click', function() {
            if (navigator.geolocation) {
                this.textContent = '定位中...';
                this.disabled = true;
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        setLocation(lat, lng);
                        
                        document.getElementById('getLocationBtn').innerHTML = '<i class="fas fa-check-circle"></i> 位置已取得';
                        document.getElementById('getLocationBtn').disabled = false;
                    },
                    function(error) {
                        alert('無法取得位置：' + error.message);
                        document.getElementById('getLocationBtn').innerHTML = '<i class="fas fa-crosshairs"></i> 取得目前位置';
                        document.getElementById('getLocationBtn').disabled = false;
                    }
                );
            } else {
                alert('您的瀏覽器不支援地理定位功能');
            }
        });
        
        // 地點搜尋功能（使用 Geoapify Geocoding API）
        function searchLocation(query) {
            if (!query || query.trim() === '') {
                return;
            }
            
            const searchBtn = document.getElementById('searchBtn');
            const searchResults = document.getElementById('searchResults');
            
            searchBtn.disabled = true;
            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 搜尋中...';
            searchResults.innerHTML = '';
            searchResults.classList.remove('active');
            
            // 使用 Geoapify Geocoding API
            const url = `https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(query)}&lang=zh&limit=5&apiKey=${geoapifyApiKey}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    searchBtn.disabled = false;
                    searchBtn.innerHTML = '<i class="fas fa-search"></i> 搜尋';
                    
                    if (data.features && data.features.length > 0) {
                        searchResults.innerHTML = '';
                        data.features.forEach(feature => {
                            const resultItem = document.createElement('div');
                            resultItem.className = 'search-result-item';
                            
                            const name = feature.properties.name || feature.properties.formatted;
                            const address = feature.properties.formatted || '';
                            
                            resultItem.innerHTML = `
                                <div class="result-name">${name}</div>
                                <div class="result-address">${address}</div>
                            `;
                            
                            resultItem.addEventListener('click', function() {
                                const lat = feature.geometry.coordinates[1];
                                const lng = feature.geometry.coordinates[0];
                                setLocation(lat, lng);
                                
                                // 自動填入地點名稱
                                document.getElementById('location_name').value = name;
                                
                                // 隱藏搜尋結果
                                searchResults.classList.remove('active');
                                document.getElementById('locationSearch').value = name;
                            });
                            
                            searchResults.appendChild(resultItem);
                        });
                        searchResults.classList.add('active');
                    } else {
                        searchResults.innerHTML = '<div class="search-result-item" style="color: #999; text-align: center;">找不到相關地點</div>';
                        searchResults.classList.add('active');
                    }
                })
                .catch(error => {
                    console.error('搜尋錯誤:', error);
                    searchBtn.disabled = false;
                    searchBtn.innerHTML = '<i class="fas fa-search"></i> 搜尋';
                    alert('搜尋失敗，請稍後再試');
                });
        }
        
        // 搜尋按鈕點擊事件
        document.getElementById('searchBtn').addEventListener('click', function() {
            const query = document.getElementById('locationSearch').value.trim();
            searchLocation(query);
        });
        
        // 按 Enter 鍵搜尋
        document.getElementById('locationSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = this.value.trim();
                searchLocation(query);
            }
        });

        // 切換位置分享
        document.getElementById('shareLocation').addEventListener('change', function() {
            const locationInputs = document.getElementById('locationInputs');
            if (this.checked) {
                locationInputs.classList.add('active');
                if (!map) {
                    initMap();
                }
                
                // 檢查是否有 Cookie 中的位置資訊
                const cookieLat = getCookie('user_lat');
                const cookieLng = getCookie('user_lng');
                
                if (cookieLat && cookieLng) {
                    // 如果有 Cookie，自動設定位置（使用目前位置模式）
                    const lat = parseFloat(cookieLat);
                    const lng = parseFloat(cookieLng);
                    setLocation(lat, lng);
                    
                    // 切換到「使用目前位置」模式
                    document.getElementById('useCurrentLocationBtn').click();
                    document.getElementById('getLocationBtn').innerHTML = '<i class="fas fa-check-circle"></i> 位置已取得';
                }
            } else {
                locationInputs.classList.remove('active');
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';
                document.getElementById('location_name').value = '';
                document.getElementById('locationInfo').style.display = 'none';
                document.getElementById('getLocationBtn').style.display = 'inline-block';
            }
        });

        // 表單提交驗證
        document.getElementById('postForm').addEventListener('submit', function(e) {
            const shareLocation = document.getElementById('shareLocation').checked;
            if (shareLocation) {
                const lat = document.getElementById('latitude').value;
                const lng = document.getElementById('longitude').value;
                if (!lat || !lng) {
                    e.preventDefault();
                    alert('請先設定位置');
                    return false;
                }
            }
        });
    </script>
</body>
</html>

