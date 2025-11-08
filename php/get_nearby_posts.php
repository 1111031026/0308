<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json; charset=utf-8');

// 檢查是否為 AJAX 請求
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// 取得用戶位置（優先從 GET 參數，其次從 Cookie）
$user_lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$user_lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;

// 如果沒有 GET 參數，從 Cookie 讀取
if (!$user_lat || !$user_lng) {
    if (isset($_COOKIE['user_lat']) && isset($_COOKIE['user_lng'])) {
        $user_lat = floatval($_COOKIE['user_lat']);
        $user_lng = floatval($_COOKIE['user_lng']);
    }
}

$radius = isset($_GET['radius']) ? floatval($_GET['radius']) : 5; // 預設 5 公里

if (!$user_lat || !$user_lng) {
    echo json_encode(['error' => '缺少位置資訊']);
    exit;
}

try {
    // 使用 Haversine 公式計算距離
    // 6371 是地球半徑（公里）
    $sql = "SELECT 
                np.PostID,
                np.Title,
                np.Content,
                np.Latitude,
                np.Longitude,
                np.LocationName,
                np.PostDate,
                np.ImageURL,
                u.Username,
                u.Status,
                u.AvatarURL,
                (
                    6371 * acos(
                        cos(radians(?)) * 
                        cos(radians(np.Latitude)) * 
                        cos(radians(np.Longitude) - radians(?)) + 
                        sin(radians(?)) * 
                        sin(radians(np.Latitude))
                    )
                ) AS distance
            FROM nearby_post np
            JOIN user u ON np.UserID = u.UserID
            WHERE np.Latitude IS NOT NULL 
            AND np.Longitude IS NOT NULL
            HAVING distance <= ?
            ORDER BY distance ASC, np.PostDate DESC
            LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('SQL 準備失敗: ' . $conn->error);
    }
    
    $stmt->bind_param("dddd", $user_lat, $user_lng, $user_lat, $radius);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = [
            'PostID' => $row['PostID'],
            'Title' => $row['Title'],
            'Content' => mb_substr(strip_tags($row['Content']), 0, 150, 'UTF-8') . (mb_strlen(strip_tags($row['Content']), 'UTF-8') > 150 ? '...' : ''),
            'Latitude' => floatval($row['Latitude']),
            'Longitude' => floatval($row['Longitude']),
            'LocationName' => $row['LocationName'],
            'PostDate' => $row['PostDate'],
            'ImageURL' => $row['ImageURL'],
            'Username' => $row['Username'],
            'Status' => $row['Status'],
            'AvatarURL' => $row['AvatarURL'],
            'Distance' => round($row['distance'], 2) // 距離（公里），四捨五入到小數點後 2 位
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'posts' => $posts,
        'count' => count($posts)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

