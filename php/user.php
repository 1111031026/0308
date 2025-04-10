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
$sql = "SELECT * FROM user WHERE Username='$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/user.css">
    <title>用戶資料</title>
</head>

<body>
    <header>
        <?php
        include "nav.php";
        ?>
    </header>
    <div class="user-profile-container">
        <div class="user-profile-card">
            <div class="user-profile-header">
                <img src="../img/user.png" alt="用戶頭像" class="user-avatar">
                <h2 class="user-name"><?php echo htmlspecialchars($user['Username']); ?></h2>
                <p class="user-status"><?php echo htmlspecialchars($user['Status']); ?></p>
            </div>

            <div class="user-info-section">
                <p class="user-info-item">
                    <strong class="user-info-label">Email:</strong>
                    <?php echo htmlspecialchars($user['Email'] ?? '未設置'); ?>
                </p>
                <p class="user-info-item">
                    <strong class="user-info-label">註冊日期:</strong>
                    <?php echo htmlspecialchars($user['RegistrationDate'] ?? '未知'); ?>
                </p>
            </div>

            <form action="logout.php" method="post" style="text-align: center; margin-top: 30px;">
                <button type="submit" class="logout-button">登出</button>
            </form>
        </div>
    </div>
</body>

</html>