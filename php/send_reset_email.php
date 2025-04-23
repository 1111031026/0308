<?php
date_default_timezone_set("Asia/Taipei");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 載入 PHPMailer 類
require '../phpmailer/PHPMailer.php';
require '../phpmailer/SMTP.php';
require '../phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 資料庫連接設定
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sustain";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 取得表單資料
    $email = $conn->real_escape_string($_POST["email"]);

    // 檢查是否存在該 email 的使用者
    $sql = "SELECT * FROM user WHERE Email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(16)); // 產生安全的 token
        $userId = $user['UserID'];

        // 將 token 和有效期限存入資料庫（請先新增 `reset_token` 和 `token_expire` 欄位）
        $expire = date("Y-m-d H:i:s", time() + 3600); // 1小時後過期
        $updateSql = "UPDATE user SET reset_token='$token', token_expire='$expire' WHERE UserID='$userId'";
        $conn->query($updateSql);

        // 寄送信件
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'noreplysustainhub@gmail.com'; // ⚠️ 請填你自己的
            $mail->Password = 'afbx zncc drvv rktm';  // ⚠️ 填入應用程式密碼
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('noreplysustainhub@gmail.com', 'SustainHub 團隊');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'SustainHub 密碼重設連結';
            $mail->Body = "
                <p>親愛的使用者您好，</p>
                <p>請點選以下連結來重設您的密碼（1 小時內有效）：</p>
                <p><a href='http://localhost/小專/php/reset_password.php?token=$token'>重設密碼連結</a></p>
                <p>如果您沒有申請重設，請忽略此信件。</p>
            ";

            $mail->send();
            echo "<script>alert('重設密碼連結已寄出！請查看您的信箱。'); window.location.href='user_login.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('信件發送失敗：{$mail->ErrorInfo}'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('此 Email 尚未註冊。'); window.history.back();</script>";
    }
}

$conn->close();
?>
