<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>忘記密碼</title>
</head>
<body>
    <h2>忘記密碼</h2>
    <p>請輸入您的註冊 Email，我們會寄送重設密碼的連結給您。</p>
    
    <!-- 密碼重設表單 -->
    <form action="send_reset_email.php" method="POST">
        <label for="email">Email：</label>
        <input type="email" name="email" required>
        <button type="submit">送出重設連結</button>
    </form>
    
</body>
</html>
