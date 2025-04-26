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
        <li><a href="#home" style="font-size: 16px;">氣候永續</a></li>
        <li><a href="#land" style="font-size: 16px; ">陸域永續</a></li>
        <li><a href="ocean.php" style="font-size: 16px;">海洋永續</a></li>
        <?php if (isset($_SESSION['login_session']) && $_SESSION['login_session'] === true): ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Teacher'): ?>
                <li><a href="crawler.php">文章編輯區</a></li>
                <li><a href="view-all-qusetion.php">查看所有編輯中題目</a></li>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Student'): ?>
                <li><a href="#land">商城</a></li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
    <div class="nav-icons">
        <a href="#"><img src="../img/achv.png" alt="成就"></a>
        <a href="user.php"><img src="../img/user.png" alt="用戶"></a>
    </div>
</nav>