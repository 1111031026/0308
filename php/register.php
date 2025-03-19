<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>註冊頁面</title>
    <link rel="stylesheet" href="../css/register.css" media="all">
</head>

<body>
    <?php
    if (isset($_POST["Username"]) && isset($_POST["Password"]) && isset($_POST["Email"])) {
        $username = $_POST["Username"];
        $password = $_POST["Password"];
        $email = $_POST["Email"];

        require_once("DB_open.php");

        // 確認用戶名是否已經存在
        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = mysqli_query($link, $sql);
        if (mysqli_num_rows($result) > 0) {
            echo "<center><font color='red'>用戶名稱已存在，請選擇其他名稱。</font></center>";
        } else {
            // 插入新用戶資料
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // 加密密碼
            $sql = "INSERT INTO users (username, password, email) VALUES ('$username', '$hashed_password', '$email')";
            if (mysqli_query($link, $sql)) {
                echo "<center><font color='green'>註冊成功！請登入。</font></center>";
                echo "<div class='center'><a class='login-button' href='user_login.php'>登入</a></div>";
            } else {
                echo "<center><font color='red'>註冊失敗，請稍後再試。</font></center>";
            }
        }

        require_once("DB_close.php");
    }
    ?>
    <form action="register.php" method="post">
        <table>
        <h2>註冊一個帳號</h2>
            <tr>
                <td style="font-size: 14px;">使用者名稱：</td>
                <td><input type="text" name="Username" size="15" maxlength="50" required /></td>
            </tr>
            <tr>
                <td style="font-size: 14px;">使用者密碼：</td>
                <td><input type="password" name="Password" size="15" maxlength="50" required /></td>
            </tr>
            <tr>
                <td style="font-size: 14px;">電子郵件：</td>
                <td><input type="email" name="Email" size="15" maxlength="100" required /></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <input type="submit" value="註冊帳戶" />
                </td>
            </tr>
        </table>
    </form>
</body>

</html>