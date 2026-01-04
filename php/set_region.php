<?php
session_start();
require_once 'db_connect.php';

// 檢查用戶是否登入
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true) {
    header('Location: user_login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_POST['method'] ?? '';

// 台灣22個縣市列表
$valid_regions = [
    '台北市', '新北市', '桃園市', '台中市', '台南市', '高雄市',
    '基隆市', '新竹市', '嘉義市', '新竹縣', '苗栗縣', '彰化縣',
    '南投縣', '雲林縣', '嘉義縣', '屏東縣', '宜蘭縣', '花蓮縣',
    '台東縣', '澎湖縣', '金門縣', '連江縣'
];

$region = null;
$error = '';

if ($method === 'manual') {
    // 手動選擇地區
    $region = trim($_POST['region'] ?? '');
    
    if (empty($region)) {
        $error = '請選擇地區';
    } elseif (!in_array($region, $valid_regions)) {
        $error = '無效的地區名稱';
    }
} elseif ($method === 'auto') {
    // 自動獲取地區（使用 GPS + Reverse Geocoding）
    $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;
    
    if (!$latitude || !$longitude) {
        // 嘗試從 Cookie 獲取
        if (isset($_COOKIE['user_lat']) && isset($_COOKIE['user_lng'])) {
            $latitude = floatval($_COOKIE['user_lat']);
            $longitude = floatval($_COOKIE['user_lng']);
        } else {
            $error = '無法取得位置資訊，請允許位置存取或使用手動選擇';
        }
    }
    
    if (!$error && $latitude && $longitude) {
        // 使用 Geoapify Reverse Geocoding API 轉換為縣市
        $geoapifyApiKey = '909bbe471da94f1a8eee1bd450c5c4bf';
        $url = "https://api.geoapify.com/v1/geocode/reverse?lat={$latitude}&lon={$longitude}&lang=zh&apiKey={$geoapifyApiKey}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            
            if (isset($data['features'][0]['properties'])) {
                $properties = $data['features'][0]['properties'];
                
                // 嘗試從多個欄位獲取縣市名稱
                $city = trim($properties['city'] ?? '');
                $county = trim($properties['county'] ?? '');
                $state = trim($properties['state'] ?? '');
                $region_name = trim($properties['region'] ?? '');
                $district = trim($properties['district'] ?? '');
                
                // 組合所有可能的地區名稱來源
                $possible_names = array_filter([$city, $county, $state, $region_name, $district]);
                
                // 地區名稱映射表（中文、英文、各種變體）
                $region_mapping = [
                    // 直轄市
                    '台北' => '台北市', 'Taipei' => '台北市', 'Taipei City' => '台北市',
                    '新北' => '新北市', 'New Taipei' => '新北市', 'New Taipei City' => '新北市',
                    '桃園' => '桃園市', 'Taoyuan' => '桃園市', 'Taoyuan City' => '桃園市',
                    '台中' => '台中市', 'Taichung' => '台中市', 'Taichung City' => '台中市',
                    '台南' => '台南市', 'Tainan' => '台南市', 'Tainan City' => '台南市',
                    '高雄' => '高雄市', 'Kaohsiung' => '高雄市', 'Kaohsiung City' => '高雄市',
                    // 省轄市
                    '基隆' => '基隆市', 'Keelung' => '基隆市', 'Keelung City' => '基隆市',
                    '新竹市' => '新竹市', 'Hsinchu' => '新竹市', 'Hsinchu City' => '新竹市',
                    '嘉義市' => '嘉義市', 'Chiayi' => '嘉義市', 'Chiayi City' => '嘉義市',
                    // 縣
                    '新竹縣' => '新竹縣', 'Hsinchu County' => '新竹縣', 'Hsinchu' => '新竹縣',
                    '苗栗' => '苗栗縣', 'Miaoli' => '苗栗縣', 'Miaoli County' => '苗栗縣',
                    '彰化' => '彰化縣', 'Changhua' => '彰化縣', 'Changhua County' => '彰化縣',
                    '南投' => '南投縣', 'Nantou' => '南投縣', 'Nantou County' => '南投縣',
                    '雲林' => '雲林縣', 'Yunlin' => '雲林縣', 'Yunlin County' => '雲林縣',
                    '嘉義縣' => '嘉義縣', 'Chiayi County' => '嘉義縣',
                    '屏東' => '屏東縣', 'Pingtung' => '屏東縣', 'Pingtung County' => '屏東縣',
                    '宜蘭' => '宜蘭縣', 'Yilan' => '宜蘭縣', 'Yilan County' => '宜蘭縣',
                    '花蓮' => '花蓮縣', 'Hualien' => '花蓮縣', 'Hualien County' => '花蓮縣',
                    '台東' => '台東縣', 'Taitung' => '台東縣', 'Taitung County' => '台東縣',
                    '澎湖' => '澎湖縣', 'Penghu' => '澎湖縣', 'Penghu County' => '澎湖縣',
                    '金門' => '金門縣', 'Kinmen' => '金門縣', 'Kinmen County' => '金門縣',
                    '連江' => '連江縣', 'Lienchiang' => '連江縣', 'Lienchiang County' => '連江縣', 'Matsu' => '連江縣'
                ];
                
                $detected_region = null;
                
                // 首先檢查是否直接匹配有效地區
                foreach ($possible_names as $name) {
                    if (in_array($name, $valid_regions)) {
                        $detected_region = $name;
                        break;
                    }
                }
                
                // 如果沒有直接匹配，使用映射表
                if (!$detected_region) {
                    foreach ($possible_names as $name) {
                        $name_lower = strtolower($name);
                        foreach ($region_mapping as $key => $value) {
                            if (stripos($name, $key) !== false || stripos($key, $name) !== false) {
                                $detected_region = $value;
                                break 2; // 跳出兩層循環
                            }
                        }
                    }
                }
                
                // 如果還是沒找到，嘗試部分匹配
                if (!$detected_region) {
                    foreach ($possible_names as $name) {
                        foreach ($valid_regions as $valid_region) {
                            // 移除「市」和「縣」後比較
                            $name_clean = str_replace(['市', '縣'], '', $name);
                            $region_clean = str_replace(['市', '縣'], '', $valid_region);
                            
                            if (stripos($name_clean, $region_clean) !== false || 
                                stripos($region_clean, $name_clean) !== false) {
                                $detected_region = $valid_region;
                                break 2;
                            }
                        }
                    }
                }
                
                // 檢查是否為有效的地區
                if ($detected_region && in_array($detected_region, $valid_regions)) {
                    $region = $detected_region;
                } else {
                    // 如果無法自動判斷，記錄調試信息並讓用戶手動選擇
                    $debug_info = 'API返回: ' . json_encode($possible_names, JSON_UNESCAPED_UNICODE);
                    error_log("地區判斷失敗 - {$debug_info}");
                    $error = '無法自動判斷您的地區（' . implode(', ', array_slice($possible_names, 0, 2)) . '），請使用手動選擇';
                }
            } else {
                $error = '無法從位置資訊判斷地區，請使用手動選擇';
            }
        } else {
            $error = '位置服務暫時無法使用，請使用手動選擇';
        }
    }
} else {
    $error = '無效的設定方式';
}

// 更新用戶地區
if (!$error && $region) {
    $update_sql = "UPDATE user SET Region = ? WHERE UserID = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $region, $user_id);
    
    if ($update_stmt->execute()) {
        $update_stmt->close();
        
        // 更新地區統計
        require_once 'update_region_stats.php';
        updateRegionStats($region);
        
        // 重定向到排行榜頁面
        header('Location: region_leaderboard.php?success=1');
        exit;
    } else {
        $error = '更新地區失敗：' . $update_stmt->error;
        $update_stmt->close();
    }
}

// 如果有錯誤，返回設定頁面並顯示錯誤
if ($error) {
    $_SESSION['region_setup_error'] = $error;
    header('Location: region_leaderboard.php');
    exit;
}

$conn->close();
?>

