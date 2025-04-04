<?php
// Ensure session is started (if not already started in the including file)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar">
    <div class="logo">
        <img src="logo.png" alt="永續小站 Logo">
        <h1 style="font-size: 24px;"><a href="index.php" class="logo-title">永續小站</a></h1>
    </div>
    <ul class="nav-links">
        <li><a href="#home" style="font-size: 16px;">氣候永續</a></li>
        <li><a href="#ocean">陸域永續</a></li>
        <li><a href="#energy">海洋能源</a></li>
        <?php if (isset($_SESSION['login_session']) && $_SESSION['login_session'] === true): ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Teacher'): ?>
                <li><a href="#edit">題目編輯區</a></li>
                <li><a href="crawler.php">文章編輯區</a></li>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Student'): ?>
                <li><a href="#land">商城</a></li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
    <div class="nav-icons">
        <a href="#"><img src="ache-icon.png" alt="成就"></a>
        <a href="#"><img src="user-icon.png" alt="用戶"></a>
    </div>
</nav>