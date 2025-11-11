<?php
session_start();
require_once 'db_connect.php';

$error_message = '';
$success_message = '';
$location_permission = false;
$user_lat = null;
$user_lng = null;

// 檢查是否有成功訊息
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = "貼文已成功發佈！ 獲得5點數！";
}

// 只從 Cookie 取得位置（不在 URL 顯示）
if (isset($_COOKIE['user_lat']) && isset($_COOKIE['user_lng'])) {
    $user_lat = floatval($_COOKIE['user_lat']);
    $user_lng = floatval($_COOKIE['user_lng']);
    $location_permission = true;
}

// 如果有 GET 參數（首次允許位置時），儲存到 Cookie 但不顯示在 URL
if (isset($_GET['lat']) && isset($_GET['lng'])) {
    $user_lat = floatval($_GET['lat']);
    $user_lng = floatval($_GET['lng']);
    $location_permission = true;
    
    // 儲存到 cookie（30天有效）
    setcookie('user_lat', $user_lat, time() + (30 * 24 * 60 * 60), '/');
    setcookie('user_lng', $user_lng, time() + (30 * 24 * 60 * 60), '/');
    
    // 重定向到沒有參數的 URL
    header('Location: nearby_discussion.php');
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>附近討論 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nearby_discussion.css">
    <link rel="stylesheet" href="../css/nav3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Leaflet.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="nearby-container">
        <div class="nearby-header">
            <h2><i class="fas fa-map-marker-alt"></i> 附近討論</h2>
            <div class="header-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="nearby_post.php" class="new-post-btn">
                        <i class="fas fa-plus"></i> 發表貼文
                    </a>
                <?php endif; ?>
                <button class="refresh-location-btn" id="refreshLocationBtn">
                    <i class="fas fa-sync-alt"></i> 重新定位
                </button>
            </div>
        </div>

        <div class="location-prompt" id="locationPrompt" style="display: <?php echo $location_permission ? 'none' : 'block'; ?>;">
            <div class="prompt-content">
                <i class="fas fa-map-marked-alt"></i>
                <h3>啟用位置服務</h3>
                <p>為了顯示附近的討論，我們需要取得您的位置資訊</p>
                <button class="enable-location-btn" id="enableLocationBtn">
                    <i class="fas fa-location-arrow"></i> 允許位置存取
                </button>
                <p class="privacy-note">您的位置資訊僅用於顯示附近討論，不會被儲存或分享</p>
            </div>
        </div>

        <div class="content-wrapper" id="contentWrapper" style="display: <?php echo $location_permission ? 'block' : 'none'; ?>;">
            <div class="map-section">
                <div id="map"></div>
                <div class="map-controls">
                    <div class="control-group">
                        <label>
                            搜尋範圍：
                            <select id="radiusSelect">
                                <option value="1">1 公里</option>
                                <option value="3" selected>3 公里</option>
                                <option value="5">5 公里</option>
                                <option value="10">10 公里</option>
                                <option value="20">20 公里</option>
                            </select>
                        </label>
                    </div>
                    <div class="control-group">
                        <label>
                            地圖樣式：
                            <select id="mapStyleSelect">
                                <option value="osm-bright">明亮風格</option>
                                <option value="dark-matter">深色風格</option>
                                <option value="klokantech-basic">簡潔風格</option>
                                <option value="positron">淺色風格</option>
                                <option value="positron-blue">藍色風格</option>
                                <option value="toner">黑白風格</option>
                            </select>
                        </label>
                    </div>
                </div>
            </div>

            <div class="posts-section">
                <div class="posts-header">
                    <h3 id="postsCount">附近討論 (0)</h3>
                </div>
                
                <div class="posts-container" id="postsContainer">
                    <!-- 貼文將透過 AJAX 載入 -->
                </div>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
    </div>

    <script src="../js/nearby_discussion.js"></script>
    <script>
        // 等待 DOM 完全載入後再初始化
        document.addEventListener('DOMContentLoaded', function() {
            // 檢查是否有 Cookie 中的位置資訊
            const cookieLat = getCookie('user_lat');
            const cookieLng = getCookie('user_lng');
            
            <?php if ($location_permission): ?>
                // 有位置資訊，使用 AJAX 載入貼文（不顯示在 URL）
                const userLocation = {
                    lat: <?php echo $user_lat; ?>,
                    lng: <?php echo $user_lng; ?>
                };
                
                // 確保地圖容器存在後再初始化
                if (document.getElementById('map')) {
                    // 延遲一點時間確保地圖容器已完全渲染
                    setTimeout(function() {
                        initMap(userLocation);
                        
                        // 透過 AJAX 載入貼文
                        const radiusSelect = document.getElementById('radiusSelect');
                        const radius = radiusSelect ? parseFloat(radiusSelect.value) : 5;
                        fetchNearbyPosts(userLocation.lat, userLocation.lng, radius);
                    }, 100);
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>

