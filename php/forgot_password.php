<link rel="stylesheet" href="../css/register.css" media="all">

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

<form action="send_reset_email.php" method="POST">
    <h2>忘記密碼</h2>
    <p>請輸入您的Email，我們會寄送重設密碼的連結給您。</p>
    <table>
        <tr>
            <td style="font-size: 14px;">Email：</td>
            <td><input type="email" name="email" required></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                <input type="submit" value="送出重設連結">
            </td>
        </tr>
    </table>
</form>
</body>
</html>
