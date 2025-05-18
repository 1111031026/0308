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
                <a href="teacher_achievement.php"><img src="../img/achv.png" alt="成就"></a>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Student'): ?>
                <a href="achievement.php"><img src="../img/achv.png" alt="成就"></a>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <a href="teacher_achievement.php"><img src="../img/achv.png" alt="成就"></a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['login_session']) && $_SESSION['login_session'] === true): ?>
            <?php
            // 獲取用戶頭像 URL
            $avatarURL = $_SESSION['AvatarURL'] ?? 'img/user.png';

            // 如果 AvatarURL 為 NULL，從資料庫中獲取
            if ($avatarURL === 'img/user.png') {
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
                    $avatarURL = $row['AvatarURL'] ?? 'img/user.png';
                }
            }
            ?>
            <a href="user.php"><img src="../<?php echo htmlspecialchars($avatarURL); ?>" alt="用戶" style="border-radius: 50%;"></a>
        <?php endif; ?>
    </div>
</nav>