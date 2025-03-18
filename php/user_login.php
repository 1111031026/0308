<?php
session_start();

$username = "";
$password = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["Username"])) {
        $username = $_POST["Username"];
    }

    if (isset($_POST["Password"])) {
        $password = $_POST["Password"];
    }

    if ($username != "" && $password != "") {
        require_once("DB_open.php");

        // 檢查用戶名是否存在
        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = mysqli_query($link, $sql);
        $total_records = mysqli_num_rows($result);

        if ($total_records > 0) {
            // 取得用戶資料
            $user = mysqli_fetch_assoc($result);
            // 使用 password_verify() 檢查密碼是否正確
            if (password_verify($password, $user['password'])) {
                $_SESSION["login_session"] = true;
                $_SESSION["username"] = $username;
                $_SESSION['user_id'] = $user['username'];  // 儲存 username 進 session

                header("Location: lobby.php");  // 登入成功後轉向首頁
                exit();  // 確保執行後不再繼續處理
            } else {
                $error_message = "密碼錯誤！";
            }
        } else {
            $error_message = "使用者名稱不存在！";
        }

        require_once("DB_close.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用戶登入</title>
    <link rel="stylesheet" href="css/register.css" media="all">
</head>

<body>
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
        </table>
    </form>

    <?php
    // 顯示錯誤訊息（如果有）
    if (!empty($error_message)) {
        echo "<div class='center'><p class='error'>$error_message</p></div>";
    }
    ?>
</body>

</html>
