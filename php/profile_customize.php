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

// 處理用戶名稱更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_username'])) {
    $new_username = trim($_POST['new_username']);
    
    // 檢查用戶名是否為空
    if (empty($new_username)) {
        $username_error = "用戶名不能為空";
    } 
    // 檢查用戶名是否已存在
    else {
        $check_sql = "SELECT * FROM user WHERE Username = ? AND UserID != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $new_username, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $username_error = "該用戶名已被使用";
        } else {
            // 更新用戶名
            $update_sql = "UPDATE user SET Username = ? WHERE UserID = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_username, $user_id);
            
            if ($update_stmt->execute()) {
                // 更新 session 中的用戶名
                $_SESSION['username'] = $new_username;
                $success_message = "用戶名更新成功！";
                // 重新獲取用戶信息
                $username = $new_username;
                $sql = "SELECT * FROM user WHERE Username='$username'";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                }
            } else {
                $username_error = "更新失敗，請稍後再試";
            }
            
            $update_stmt->close();
        }
        
        $check_stmt->close();
    }
}

// 處理頭像更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_avatar']) && !empty($_POST['avatar_id'])) {
    $avatar_id = intval($_POST['avatar_id']);
    
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
        
        if ($update_stmt->execute()) {
            $success_message = "頭像更新成功！";
            // 重新獲取用戶信息以顯示新頭像
            $sql = "SELECT * FROM user WHERE Username='$username'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
            }
        } else {
            $avatar_error = "頭像更新失敗，請稍後再試";
        }
        
        $update_stmt->close();
    } else {
        $avatar_error = "您尚未購買此頭像";
    }
    
    $check_stmt->close();
}

// 處理背景更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_background']) && !empty($_POST['background_id'])) {
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
        
        if ($update_stmt->execute()) {
            $success_message = "背景更新成功！";
            // 重新獲取用戶信息以顯示新背景
            $sql = "SELECT * FROM user WHERE Username='$username'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
            }
        } else {
            $background_error = "背景更新失敗，請稍後再試";
        }
        
        $update_stmt->close();
    } else {
        $background_error = "您尚未購買此背景";
    }
    
    $check_stmt->close();
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

// 處理重置頭像為預設
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_avatar'])) {
    // 更新用戶頭像為 NULL（使用預設頭像）
    $update_sql = "UPDATE user SET AvatarURL = NULL WHERE UserID = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    
    if ($update_stmt->execute()) {
        $success_message = "已重置為預設頭像！";
        // 重新獲取用戶信息以顯示預設頭像
        $sql = "SELECT * FROM user WHERE Username='$username'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $current_avatar = $user['AvatarURL'] ?? '../img/user.png';
        }
    } else {
        $avatar_error = "重置頭像失敗，請稍後再試";
    }
    
    $update_stmt->close();
}

// 處理重置背景為預設
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_background'])) {
    // 更新用戶背景為 NULL（使用預設背景）
    $update_sql = "UPDATE user SET BackgroundURL = NULL WHERE UserID = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    
    if ($update_stmt->execute()) {
        $success_message = "已重置為預設背景！";
        // 重新獲取用戶信息以顯示預設背景
        $sql = "SELECT * FROM user WHERE Username='$username'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $current_background = $user['BackgroundURL'] ?? '';
        }
    } else {
        $background_error = "重置背景失敗，請稍後再試";
    }
    
    $update_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/user_cust.css">
    <link rel="stylesheet" href="../css/nav3.css">
    <title>自訂個人檔案</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
</head>

<body>
    <header>
        <?php include "nav.php"; ?>
    </header>
    
    <div class="customize-container">
        <!-- 頂部個人資料預覽 -->
        <div class="profile-header">
            <?php if (!empty($current_background)): ?>
            <div class="profile-background" style="background-image: url('../<?php echo htmlspecialchars($current_background); ?>');"></div>
            <?php else: ?>
            <div class="profile-background" style="background-color: #e9e9e9;"></div>
            <?php endif; ?>
            
            <img src="../<?php echo htmlspecialchars($current_avatar); ?>" alt="用戶頭像" class="profile-avatar">
            <h2 class="profile-name"><?php echo htmlspecialchars($user['Username']); ?></h2>
            <p class="profile-status"><?php echo htmlspecialchars($user['Status']); ?></p>
        </div>
        
        <!-- 成功或錯誤訊息 -->
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <!-- 更改用戶名稱區域 -->
        <div class="customize-section">
            <h3 class="section-title">更改用戶名稱</h3>
            
            <?php if (isset($username_error)): ?>
                <div class="error-message"><?php echo $username_error; ?></div>
            <?php endif; ?>
            
            <form method="post" action="" class="username-form">
                <input type="text" name="new_username" placeholder="輸入新的用戶名稱" value="<?php echo htmlspecialchars($user['Username']); ?>">
                <button type="submit" name="update_username">更新名稱</button>
            </form>
        </div>
        
        <!-- 更換頭像區域 -->
        <div class="customize-section">
            <h3 class="section-title">更換頭像</h3>
            
            <?php if (isset($avatar_error)): ?>
                <div class="error-message"><?php echo $avatar_error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($avatars)): ?>
                <form method="post" action="">
                    <div class="items-grid">
                        <?php foreach ($avatars as $avatar): ?>
                        <div class="item-card <?php echo ($current_avatar === $avatar['ImageURL']) ? 'selected' : ''; ?>" onclick="selectAvatar(<?php echo $avatar['ItemID']; ?>)">
                            <img src="../<?php echo htmlspecialchars($avatar['ImageURL']); ?>" alt="<?php echo htmlspecialchars($avatar['Name']); ?>" class="item-image">
                            <div class="item-name"><?php echo htmlspecialchars($avatar['Name']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
                        <input type="hidden" name="avatar_id" id="selected_avatar_id" value="">
                        <button type="submit" name="update_avatar" class="update-button">更新頭像</button>
                        <button type="submit" name="reset_avatar" class="reset-button">使用預設頭像</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="no-items-message">
                    您尚未購買任何頭像。前往<a href="shop.php" class="shop-link">商店</a>購買頭像吧！
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 更換背景區域 -->
        <div class="customize-section">
            <h3 class="section-title">更換個人檔案背景</h3>
            
            <?php if (isset($background_error)): ?>
                <div class="error-message"><?php echo $background_error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($backgrounds)): ?>
                <form method="post" action="">
                    <div class="items-grid">
                        <?php foreach ($backgrounds as $background): ?>
                        <div class="item-card <?php echo ($current_background === $background['ImageURL']) ? 'selected' : ''; ?>" onclick="selectBackground(<?php echo $background['ItemID']; ?>)">
                            <img src="../<?php echo htmlspecialchars($background['ImageURL']); ?>" alt="<?php echo htmlspecialchars($background['Name']); ?>" class="item-image">
                            <div class="item-name"><?php echo htmlspecialchars($background['Name']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
                        <input type="hidden" name="background_id" id="selected_background_id" value="">
                        <button type="submit" name="update_background" class="update-button">更新背景</button>
                        <button type="submit" name="reset_background" class="reset-button">使用預設背景</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="no-items-message">
                    您尚未購買任何背景。前往<a href="shop.php" class="shop-link">商店</a>購買背景吧！
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 返回按鈕 -->
        <a href="user.php" class="back-button">返回個人檔案</a>
    </div>
    
    <script>
        function selectAvatar(id) {
            document.getElementById('selected_avatar_id').value = id;
            // 移除所有頭像卡片的選中狀態
            const avatarCards = document.querySelectorAll('.customize-section:nth-of-type(2) .item-card');
            avatarCards.forEach(card => card.classList.remove('selected'));
            // 為選中的卡片添加選中狀態
            event.currentTarget.classList.add('selected');
        }
        
        function selectBackground(id) {
            document.getElementById('selected_background_id').value = id;
            // 移除所有背景卡片的選中狀態
            const backgroundCards = document.querySelectorAll('.customize-section:nth-of-type(3) .item-card');
            backgroundCards.forEach(card => card.classList.remove('selected'));
            // 為選中的卡片添加選中狀態
            event.currentTarget.classList.add('selected');
        }
    </script>
</body>

</html>