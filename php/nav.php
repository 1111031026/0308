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
        <?php if (isset($_SESSION['login_session']) && $_SESSION['login_session'] === true): ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Teacher'): ?>
                <li><a href="crawler.php">文章編輯區</a></li>
                <li><a href="view-all-qusetion.php">查看所有編輯中題目</a></li>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'):?>
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
            <?php endif; ?>
        <?php endif; ?>

        <a href="user.php"><img src="../img/user.png" alt="用戶"></a>
    </div>
</nav>