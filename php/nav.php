<?php
// Ensure session is started (if not already started in the including file)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar">
    <div class="logo">
        <img src="../img/icon.png" style="color: white;" alt="永續小站 Logo">
        <h1 style="font-size: 24px;"><a href="index.php" class="logo-title">永續小站</a></h1>
    </div>
    <ul class="nav-links">
        <li><a href="climate.php" style="font-size: 16px;">氣候永續</a></li>
        <li><a href="landscape.php" style="font-size: 16px; ">陸域永續</a></li>
        <li><a href="ocean.php" style="font-size: 16px;">海洋永續</a></li>
        <li><a href="all_forums.php" style="font-size: 16px;">論壇</a></li>
        <?php if (isset($_SESSION['login_session']) && $_SESSION['login_session'] === true): ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Teacher'): ?>
                <li><a href="crawler.php">文章編輯區</a></li>
                <li><a href="view-all-qusetion.php">查看所有編輯中題目</a></li>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <li><a href="crawler.php">文章編輯區</a></li>
                <li><a href="view-all-qusetion.php">查看所有編輯中題目</a></li>
                <li><a href="merchandise_manage.php">商品管理</a></li>
            <?php endif; ?>
            <li><a href="shop.php">商城</a></li>
        <?php endif; ?>
    </ul>
    <div class="nav-icons">
        <?php if (isset($_SESSION['login_session']) && $_SESSION['login_session'] === true): ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Teacher'): ?>
                <span class="user-info">user: <?php echo $_SESSION['username']; ?> <?php echo $_SESSION['role']; ?></span>
                <a href="teacher_achievement.php"><img src="../img/achievement.svg" alt="成就"></a>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Student'): ?>
                <span class="user-info">user: <?php echo $_SESSION['username']; ?> <?php echo $_SESSION['role']; ?></span>
                <a href="achievement.php"><img src="../img/achievement.svg" alt="成就"></a>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <span class="user-info">user: <?php echo $_SESSION['username']; ?> <?php echo $_SESSION['role']; ?></span>
                <a href="teacher_achievement.php"><img src="../img/achievement.svg" alt="成就"></a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['login_session']) && $_SESSION['login_session'] === true): ?>
            <?php
            // 獲取用戶頭像 URL
            $avatarURL = $_SESSION['AvatarURL'] ?? '';

            // 如果 AvatarURL 為空，從資料庫中獲取
            if (empty($avatarURL)) {
                $servername = "localhost";
                $db_username = "root";
                $db_password = "";
                $dbname = "sustain";

                $conn = new mysqli($servername, $db_username, $db_password, $dbname);
                $conn->set_charset("utf8mb4");

                if ($conn->connect_error) {
                    die("連接失敗: " . $conn->connect_error);
                }

                $username = $_SESSION['username'];
                $sql = "SELECT AvatarURL FROM user WHERE Username='$username'";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $avatarURL = $row['AvatarURL'] ?? '';
                }
            }
            
            if (!empty($avatarURL)) {
                echo '<a href="user.php"><img src="../' . htmlspecialchars($avatarURL) . '" alt="用戶" style="border-radius: 50%; width: 32px; height: 32px; object-fit: cover;"></a>';
            } else {
                echo '<a href="user.php" style="text-decoration: none;"><div class="default-avatar"><span class="default-avatar-icon">👤</span></div></a>';
            }
            ?>
        <?php endif; ?>
    </div>
</nav>

<style>
.nav-icons {
    display: flex;
    align-items: center;
}
.user-info {
    font-size: 13px;
    margin-right: 8px;
    color: #ffffff;
    font-style: italic;
    opacity: 0.9;
    padding: 3px 6px;
}
.default-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #6aafc7;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.default-avatar-icon {
    font-size: 0.9rem;
    color: white;
}
</style>