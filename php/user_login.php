<?php
session_start();

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST["Username"] ?? "");
    $password = $_POST["Password"] ?? "";

    if ($username && $password) {
        $sql = "SELECT * FROM user WHERE Username='$username'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['Password'])) {
                // Store user details in session
                $_SESSION["login_session"] = true;
                $_SESSION["username"] = $username;
                $_SESSION["user_id"] = $user['UserID'];
                $_SESSION["role"] = $user['Status'];
                
                // 根據用戶身份重定向到不同頁面
                if ($user['Status'] === 'Admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error_message = "密碼錯誤！";
            }
        } else {
            $error_message = "使用者名稱不存在！";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用戶登入</title>
    <link rel="stylesheet" href="../css/register.css" media="all">
    <link rel="icon" type="image/png" href="../img/icon.png">
    <style>
        .toast {
            position: fixed;
            top: 20px;
            right: -100%;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            color: white;
            font-size: 16px;
            transition: right 0.5s ease;
            z-index: 1000;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 200px;
            box-sizing: border-box;
        }
        .toast.success {
            border-left: 4px solid #2ecc71;
        }
        .toast.error {
            border-left: 4px solid #e74c3c;
        }
        .toast.show {
            right: 20px;
        }
        .toast-icon {
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="particles">
        <?php
        for ($i = 0; $i < 50; $i++) {
            $size = rand(8, 12);
            $left = rand(0, 100);
            $animationDelay = (rand(0, 1000) / 100);
            echo "<div class='particle' style='width: {$size}px; height: {$size}px; left: {$left}%; animation-delay: {$animationDelay}s;'></div>";
        }
        ?>
    </div>

    <form action="user_login.php" method="post">
        <h2>登入 SustainHub</h2>
        <table>
            <tr>
                <td style="font-size: 14px;">使用者名稱：</td>
                <td><input type="text" name="Username" size="15" maxlength="50" required /></td>
            </tr>
            <tr>
                <td style="font-size: 14px;">使用者密碼：</td>
                <td><input type="password" name="Password" size="15" maxlength="50" required /></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <input type="submit" value="登入" />
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <a href="forgot_password.php" style="color: #fff; text-decoration: none; font-size: 14px;">忘記密碼？</a>
                </td>
            </tr>
            <tr>
            <td colspan="2" style="text-align: center;">
                    <a href="register.php" style="color: #fff; text-decoration: none; font-size: 14px;">未有帳號，立即註冊</a>
                </td>
            </tr>
        </table>
    </form>

    <?php
    if (!empty($error_message)) {
        echo "<div class='toast error'><span class='toast-icon'>❌</span>$error_message</div>";
    }
    ?>

    <script>
    function showToast() {
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(toast => {
            setTimeout(() => {
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        toast.remove();
                    }, 500);
                }, 5000);
            }, 100);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const toasts = document.querySelectorAll('.toast');
        if (toasts.length > 0) {
            showToast();
        }
    });
    </script>
</body>
</html>