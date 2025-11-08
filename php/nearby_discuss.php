<?php
session_start();
require_once 'db_connect.php';

// 驗證貼文ID
$post_id = isset($_GET['post_id']) && filter_var($_GET['post_id'], FILTER_VALIDATE_INT) ? (int)$_GET['post_id'] : 0;
if ($post_id <= 0) {
    header('Location: nearby_discussion.php');
    exit();
}

// 查詢貼文詳情（從 nearby_post 表）
$sql = "SELECT np.PostID, np.Title, np.Content, np.PostDate, np.ImageURL, np.Latitude, np.Longitude, np.LocationName, u.Username, u.Status, u.AvatarURL 
        FROM nearby_post np 
        JOIN user u ON np.UserID = u.UserID 
        WHERE np.PostID = ?";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    die('貼文查詢準備失敗: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'i', $post_id);
if (!mysqli_stmt_execute($stmt)) {
    die('貼文查詢執行失敗: ' . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// 檢查貼文是否存在
if (!$post) {
    header('Location: nearby_discussion.php');
    exit();
}

// 處理評論提交（使用 commentarea 表，但需要確認 PostID 是否會衝突）
// 為了避免衝突，我們可以創建一個新的 nearby_comment 表，或者使用現有的 commentarea
// 這裡先使用 commentarea，但需要確保 PostID 不會與 communitypost 衝突
// 更好的做法是創建新的 nearby_comment 表，但為了簡化，這裡先使用 commentarea
$comment_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $comment = trim($_POST['comment'] ?? '');
    
    if (empty($comment)) {
        $comment_error = '評論內容不能為空';
    } elseif (mb_strlen($comment, 'UTF-8') > 1000) {
        $comment_error = '評論內容不能超過1000字元';
    } else {
        $user_id = $_SESSION['user_id'];
        // 注意：這裡使用 commentarea 表，但 PostID 可能會與 communitypost 衝突
        // 建議未來創建 nearby_comment 表
        $sql = "INSERT INTO commentarea (PostID, UserID, Content, CommentTime) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt === false) {
            $comment_error = '評論插入準備失敗: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 'iis', $post_id, $user_id, $comment);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: nearby_discuss.php?post_id=$post_id");
                exit();
            } else {
                $comment_error = '評論插入失敗: ' . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// 查詢評論列表
$sql = "SELECT ca.CommentID, ca.Content, ca.CommentTime, u.UserID, u.Username, u.Status, u.AvatarURL 
        FROM commentarea ca 
        JOIN user u ON ca.UserID = u.UserID 
        WHERE ca.PostID = ? 
        ORDER BY ca.CommentTime ASC";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    die('評論查詢準備失敗: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'i', $post_id);
if (!mysqli_stmt_execute($stmt)) {
    die('評論查詢執行失敗: ' . mysqli_stmt_error($stmt));
}

$comments = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['Title']); ?> - 附近討論</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav3.css">
    <link rel="stylesheet" href="../css/discuss.css">
    <link rel="stylesheet" href="../css/nearby_discuss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Leaflet.js -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div style="height: 70px;"></div>
    
    <div class="forum-container">
        <div class="navigation-links">
            <a href="nearby_discussion.php" class="back-link">
                <i class="fas fa-arrow-left"></i> 返回附近討論
            </a>
        </div>

        <div class="post-container">
            <!-- 貼文詳情 -->
            <div class="post-detail">
                <h1 class="post-title"><?php echo htmlspecialchars($post['Title']); ?></h1>
                
                <div class="post-meta">
                    <div class="author-info">
                        <?php if (!empty($post['AvatarURL'])): ?>
                            <img src="../<?php echo htmlspecialchars($post['AvatarURL']); ?>" alt="用戶頭像" class="author-avatar" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 8px;">
                        <?php else: ?>
                            <div class="default-avatar" style="width: 30px; height: 30px; border-radius: 50%; background: #6aafc7; display: flex; align-items: center; justify-content: center; margin-right: 8px; overflow: hidden;">
                                <span style="font-size: 0.9rem; color: white;">👤</span>
                            </div>
                        <?php endif; ?>
                        <span class="author-name"><?php echo htmlspecialchars($post['Username']); ?></span>
                        <?php if (!empty($post['Status'])): ?>
                            <span class="author-status"><?php echo htmlspecialchars($post['Status']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="post-time">
                        <i class="far fa-clock"></i>
                        <span><?php echo date('Y-m-d H:i', strtotime($post['PostDate'])); ?></span>
                    </div>
                </div>

                <!-- 位置資訊 -->
                <?php if (!empty($post['Latitude']) && !empty($post['Longitude'])): ?>
                    <div class="location-section">
                        <div class="location-header">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="location-name">
                                <?php echo !empty($post['LocationName']) ? htmlspecialchars($post['LocationName']) : '已標記位置'; ?>
                            </span>
                        </div>
                        <div id="postMap" style="width: 100%; height: 300px; border-radius: 8px; margin-top: 10px;"></div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($post['ImageURL'])): ?>
                    <div class="post-image-container">
                        <img src="<?php echo htmlspecialchars($post['ImageURL']); ?>" alt="貼文圖片" class="post-image">
                    </div>
                <?php endif; ?>
                
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['Content'])); ?>
                </div>
            </div>

            <!-- 評論區 -->
            <div class="comments-section">
                <h2 class="section-title">留言區 <span class="comment-count"><?php echo mysqli_num_rows($comments); ?></span></h2>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="comment-form-container">
                        <form method="POST" action="" class="comment-form">
                            <?php if ($comment_error): ?>
                                <div class="error-message"><?php echo htmlspecialchars($comment_error); ?></div>
                            <?php endif; ?>
                            <div class="form-group">
                                <textarea name="comment" placeholder="發表你的留言..." required></textarea>
                                <button type="submit" class="submit-btn">留言</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>請<a href="user_login.php">登入</a>後發表留言</p>
                    </div>
                <?php endif; ?>

                <div class="comments-list">
                    <?php if (mysqli_num_rows($comments) > 0): ?>
                        <?php while ($comment = mysqli_fetch_assoc($comments)): ?>
                            <div class="comment-card">
                                <div class="comment-header">
                                    <div class="commenter-info">
                                        <?php
                                        $achievement_page = $comment['Status'] === 'teacher' ? 'teacher_achievement.php' : 'achievement.php';
                                        ?>
                                        <?php if (!empty($comment['AvatarURL'])): ?>
                                            <a href="<?php echo $achievement_page; ?>?user_id=<?php echo htmlspecialchars($comment['UserID']); ?>" class="avatar-link">
                                                <img src="../<?php echo htmlspecialchars($comment['AvatarURL']); ?>" alt="用戶頭像" class="commenter-avatar" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 8px;">
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo $achievement_page; ?>?user_id=<?php echo htmlspecialchars($comment['UserID']); ?>" class="avatar-link">
                                                <div class="default-avatar commenter-avatar" style="width: 30px; height: 30px; border-radius: 50%; background: #6aafc7; display: flex; align-items: center; justify-content: center; margin-right: 8px; overflow: hidden;">
                                                    <span style="font-size: 0.9rem; color: white;">👤</span>
                                                </div>
                                            </a>
                                        <?php endif; ?>
                                        <span class="commenter-name"><?php echo htmlspecialchars($comment['Username']); ?></span>
                                        <?php if (!empty($comment['Status'])): ?>
                                            <span class="commenter-status"><?php echo htmlspecialchars($comment['Status']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-time">
                                        <i class="far fa-clock"></i>
                                        <span><?php echo date('Y-m-d H:i', strtotime($comment['CommentTime'])); ?></span>
                                    </div>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialchars($comment['Content'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-comments">
                            <p>目前還沒有留言，快來發表第一條吧！</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($post['Latitude']) && !empty($post['Longitude'])): ?>
    <script>
        // 初始化地圖（使用 Geoapify + Leaflet）
        function initMap() {
            const lat = <?php echo floatval($post['Latitude']); ?>;
            const lng = <?php echo floatval($post['Longitude']); ?>;
            
            // 建立地圖
            const map = L.map('postMap').setView([lat, lng], 15);
            
            // 取得地圖樣式（從 Cookie 或預設值）
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
            
            const geoapifyApiKey = '909bbe471da94f1a8eee1bd450c5c4bf';
            const mapStyle = getCookie('map_style') || 'osm-bright';
            L.tileLayer(`https://maps.geoapify.com/v1/tile/${mapStyle}/{z}/{x}/{y}.png?apiKey=${geoapifyApiKey}`, {
                attribution: 'Powered by <a href="https://www.geoapify.com/" target="_blank">Geoapify</a>',
                maxZoom: 19
            }).addTo(map);
            
            // 建立標記
            const marker = L.marker([lat, lng]).addTo(map);
            
            // 建立彈出視窗內容
            let popupContent = `
                <div style="padding: 10px;">
                    <h4 style="margin: 0 0 5px 0;"><?php echo addslashes($post['Title']); ?></h4>
            `;
            <?php if (!empty($post['LocationName'])): ?>
                popupContent += `<p style="margin: 0; color: #666;"><?php echo addslashes($post['LocationName']); ?></p>`;
            <?php endif; ?>
            popupContent += `</div>`;
            
            marker.bindPopup(popupContent).openPopup();
        }
        
        // 等待頁面載入完成後初始化地圖
        window.addEventListener('DOMContentLoaded', function() {
            initMap();
        });
    </script>
    <?php endif; ?>
</body>
</html>

