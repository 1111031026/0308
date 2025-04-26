<?php
session_start();
$token = $_GET['token'] ?? '';
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>重設密碼</title>
    <link rel="stylesheet" href="../css/register.css">
</head>
<body>
    <form action="process_reset_password.php" method="post">
        <h2>設定新密碼</h2>
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <table>
            <tr>
                <td style="font-size: 14px;">新密碼：</td>
                <td><input type="password" name="new_password" required minlength="6"></td>
            </tr>
            <tr>
                <td style="font-size: 14px;">確認新密碼：</td>
                <td><input type="password" name="confirm_password" required minlength="6"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <input type="submit" value="重設密碼">
                </td>
            </tr>
        </table>
    </form>
</body>
</html>
