<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>導覽頁</title>
</head>

<body>
    <div class="page-container">
        <!-- 第一區塊：導覽頁 -->
        <section class="section section-1">
            <div class="background-carousel"></div>
            <h1>
                歡迎來到SustainHub 永續小站
            </h1>
            <h2>
                <span>𝕊</span><span>𝕦</span><span>𝕤</span><span>𝕥</span><span>𝕒</span><span>𝕚</span><span>𝕟</span><span>ℍ</span><span>𝕦</span><span>𝕓</span>
            </h2>
            <h2> <span>F</span><span>o</span><span>r</span>
                <span>a</span>
                <span>T</span><span>h</span><span>r</span><span>i</span><span>v</span><span>i</span><span>n</span><span>g</span>
                <span>P</span><span>l</span><span>a</span><span>n</span><span>e</span><span>t</span><span>,</span>
                <span>S</span><span>t</span><span>a</span><span>r</span><span>t</span>
                <span>H</span><span>e</span><span>r</span><span>e</span>
            </h2>
            <div class="button-container">
                <a href="register.php">
                    <button class="btn btn-register">未有帳號，我要註冊</button>
                </a>
                <a href="user_login.php">
                    <button class="btn btn-login">已有帳號，我要登入</button>
                </a>
            </div>
            <div class="scroll-down-arrow"><h1>⇩</h1></div>
        </section>

        <!-- 第二區塊：永續主題介紹 -->
        <section class="section section-2">
            <h1>永續生活的第一步</h1>
            <p>在SustainHub，我們致力於推廣永續發展，減少碳足跡，讓地球更美好！</p>
        </section>

        <!-- 第三區塊：聯絡資訊 -->
        <section class="section section-3">
            <h1>聯絡我們</h1>
            <p>有任何問題？歡迎聯繫：support@sustainhub.com</p>
        </section>
    </div>

    <script>
    const text = "歡迎來到SustainHub 永續小站";
    let index = 0;
    const h1 = document.querySelector('.section-1 h1');

    function type() {
        try {
            if (index < text.length) {
                h1.textContent = text.slice(0, index + 1); // 從頭顯示到當前字元
                index++;
                setTimeout(type, 150); // 每個字元間隔 150ms
            }
        } catch (e) {
            console.error("Typing error:", e);
        }
    }

    // 當頁面載入時開始打字效果
    window.onload = function() {
        h1.textContent = ""; // 清空初始內容，避免重複
        index = 0; // 重置索引
        type();
    };
</script>
</body>

</html>





