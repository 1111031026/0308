<?php
date_default_timezone_set("Asia/Taipei");
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sustain";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["token"] ?? "";
    $new_password = $_POST["new_password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if (empty($token) || empty($new_password) || empty($confirm_password)) {
        die("請填寫所有欄位！");
    }

    if ($new_password !== $confirm_password) {
        die("兩次輸入的密碼不一致！");
    }

    $sql = "SELECT * FROM user WHERE reset_token = '$token' AND token_expire >= NOW()";
    $result = $conn->query($sql);

    if ($result->num_rows === 0) {
        echo "除錯資訊：<br>";
        echo "輸入的 token: " . $token . "<br>";
        $check_sql = "SELECT * FROM user WHERE reset_token = '$token'";
        $check_result = $conn->query($check_sql);
        if ($check_result->num_rows === 0) {
            echo "❌ 找不到對應的 token";
        } else {
            echo "✅ 有找到 token，但可能過期了！";
        }
        exit;
    }
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE user SET Password = '$hashed_password', reset_token = NULL, token_expire = NULL WHERE UserID = '{$user['UserID']}'";

        if ($conn->query($update_sql)) {
            echo "<script>alert('密碼重設成功！請重新登入'); window.location.href='user_login.php';</script>";
        } else {
            echo "密碼更新失敗：" . $conn->error;
        }
    } else {
        echo "無效或過期的重設連結！";
    }
}

$conn->close();
?>
