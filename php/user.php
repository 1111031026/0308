<?php
session_start();

// 檢查用戶是否登入
if (!isset($_SESSION['login_session'])) {
    header("Location: user_login.php");
    exit();
}

$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "sustain";

// 創建資料庫連接
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 獲取用戶信息
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'] ?? 0;
$sql = "SELECT * FROM user WHERE Username='$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}

// 處理頭像和背景更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_avatar']) && !empty($_POST['avatar_id'])) {
        $avatar_id = intval($_POST['avatar_id']);
        
        // 檢查用戶是否已購買該頭像
        // 檢查用戶是否已購買該頭像
        $check_sql = "SELECT * FROM purchase p JOIN merchandise m ON p.ItemID = m.ItemID 
                      WHERE p.UserID = ? AND m.ItemID = ? AND m.Category = 'head'";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $avatar_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // 獲取頭像圖片URL
            $item = $check_result->fetch_assoc();
            $avatar_url = $item['ImageURL'];
            
            // 更新用戶頭像
            $update_sql = "UPDATE user SET AvatarURL = ? WHERE UserID = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $avatar_url, $user_id);
            $update_stmt->execute();
            // 通知前端更新导航栏头像
            echo json_encode(['avatar_url' => $avatar_url]);
            $update_stmt->close();
            
            // 重新載入頁面以顯示更新
            header("Location: user.php?success=avatar");
            exit();
        }
        $check_stmt->close();
    }
    
    if (isset($_POST['update_background']) && !empty($_POST['background_id'])) {
        $background_id = intval($_POST['background_id']);
        
        // 檢查用戶是否已購買該背景
        $check_sql = "SELECT * FROM purchase p JOIN merchandise m ON p.ItemID = m.ItemID 
                      WHERE p.UserID = ? AND m.ItemID = ? AND m.Category = 'background'";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $background_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // 獲取背景圖片URL
            $item = $check_result->fetch_assoc();
            $background_url = $item['ImageURL'];
            
            // 更新用戶背景
            $update_sql = "UPDATE user SET BackgroundURL = ? WHERE UserID = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $background_url, $user_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            // 重新載入頁面以顯示更新
            header("Location: user.php?success=background");
            exit();
        }
        $check_stmt->close();
    }
}

// 獲取用戶已購買的頭像
$avatars = [];
$avatar_sql = "SELECT m.* FROM purchase p JOIN merchandise m ON p.ItemID = m.ItemID 
               WHERE p.UserID = ? AND m.Category = 'head'";
$avatar_stmt = $conn->prepare($avatar_sql);
$avatar_stmt->bind_param("i", $user_id);
$avatar_stmt->execute();
$avatar_result = $avatar_stmt->get_result();
while ($row = $avatar_result->fetch_assoc()) {
    $avatars[] = $row;
}
$avatar_stmt->close();

// 獲取用戶已購買的背景
$backgrounds = [];
$background_sql = "SELECT m.* FROM purchase p JOIN merchandise m ON p.ItemID = m.ItemID 
                   WHERE p.UserID = ? AND m.Category = 'background'";
$background_stmt = $conn->prepare($background_sql);
$background_stmt->bind_param("i", $user_id);
$background_stmt->execute();
$background_result = $background_stmt->get_result();
while ($row = $background_result->fetch_assoc()) {
    $backgrounds[] = $row;
}
$background_stmt->close();

// 獲取當前用戶的頭像和背景
$current_avatar = $user['AvatarURL'] ?? '../img/user.png';
$current_background = $user['BackgroundURL'] ?? '';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/user.css">
    <link rel="stylesheet" href="../css/nav3.css">
    <title>用戶資料</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <style>
        .user-profile-container {
            position: relative;
            padding: 20px;
        }
        
        .user-profile-card {
            position: relative;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            padding: 40px 30px;
            max-width: 800px;
            margin: 0 auto;
            z-index: 1;
            overflow: visible;
        }
        
        .profile-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 220px;
            background-size: cover;
            background-position: center;
            border-radius: 10px 10px 0 0;
            opacity: 0.8;
            z-index: 0;
        }
        
        .customization-section {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
            display: none; /* 預設隱藏自訂區域 */
        }
        
        .customization-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }
        
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .item-card {
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .item-card.selected {
            border-color: #4CAF50;
        }
        
        .item-image {
            width: 100%;
            height: 100px;
            object-fit: cover;
        }
        
        .item-name {
            padding: 5px;
            font-size: 12px;
            text-align: center;
            background-color: #f5f5f5;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .update-button, .customize-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            margin: 10px 0;
        }
        
        
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .user-profile-header {
            text-align: center;
            margin-bottom: 40px;
            padding-top: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        
        .avatar-container {
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }
        
        .user-name {
            font-size: 32px;
            margin: 15px 0 8px;
            color: #333;
            font-weight: 600;
        }
        
        .user-status {
            font-size: 20px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .user-avatar {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .default-avatar {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: #6aafc7;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 5px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .default-avatar-icon {
            font-size: 5.5rem;
            color: white;
        }
        
        .customization-container {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <header>
        <?php
        include "nav.php";
        ?>
    </header>
    <div class="user-profile-container">
        <div class="user-profile-card" style="padding: 50px 35px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);">
            <?php if (!empty($current_background)): ?>
            <div class="profile-background" style="height: 240px; opacity: 0.85; background-image: url('../<?php echo htmlspecialchars($current_background); ?>');">
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    <?php if ($_GET['success'] === 'avatar'): ?>
                        頭像更新成功！
                    <?php elseif ($_GET['success'] === 'background'): ?>
                        背景更新成功！
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="user-profile-header" style="padding-top: 50px; margin-bottom: 40px;">
                <div class="avatar-container" style="margin-bottom: 20px;">
                    <?php if (!empty($user['AvatarURL'])): ?>
                        <img src="../<?php echo htmlspecialchars($user['AvatarURL']); ?>" alt="用戶頭像" class="user-avatar" style="width: 160px !important; height: 160px !important; border: 5px solid white; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);">
                    <?php else: ?>
                        <div class="default-avatar" style="width: 160px !important; height: 160px !important; border: 5px solid white; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);">
                            <span class="default-avatar-icon" style="font-size: 5rem !important;">👤</span>
                        </div>
                    <?php endif; ?>
                </div>
                <h2 class="user-name" style="font-size: 32px; margin: 15px 0 8px;"><?php echo htmlspecialchars($user['Username']); ?></h2>
                <p class="user-status" style="font-size: 20px;"><?php echo htmlspecialchars($user['Status']); ?></p>
            </div>

            <div class="user-info-section">
                <p class="user-info-item">
                    <strong class="user-info-label">Email:</strong>
                    <?php echo htmlspecialchars($user['Email'] ?? '未設置'); ?>
                </p>
                <p class="user-info-item">
                    <strong class="user-info-label">註冊日期:</strong>
                    <?php echo htmlspecialchars(isset($user['JoinDate']) ? date('Y-m-d', strtotime($user['JoinDate'])) : '未知'); ?>
                </p>
            </div>
            
            <!-- 將自訂按鈕和登出按鈕放在同一行 -->
            <div style="text-align: center; margin-top: 30px; display: flex; justify-content: center; gap: 20px;">
                <a href="profile_customize.php" class="custom-button">自訂個人檔案</a>
                <form action="logout.php" method="post" style="margin: 0;">
                    <button type="submit" class="logout-button">登出</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>