<?php
session_start();

// 清除所有session變數
$_SESSION = array();

// 銷毀session
session_destroy();

// 重定向到登入頁面
header("Location: user_login.php");
exit();
?>