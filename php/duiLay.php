<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/DiuLay.css">
    <!-- 引入 Noto Sans 思源黑體 -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;700&display=swap" rel="stylesheet">
    <!-- 引入 Font Awesome 圖標 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>導覽頁</title>
</head>
<body>
    <div class="page-container">
        <!-- 第一區塊：導覽頁 -->
        <section class="section section-1">
            <div class="background-carousel"></div>
            <h1>歡迎來到SustainHub 永續小站</h1>
            <h2>
                <span>S</span><span>u</span><span>s</span><span>t</span><span>a</span><span>i</span><span>n</span><span>H</span><span>u</span><span>b</span>
            </h2>
            <h2>
                <span>F</span><span>o</span><span>r</span>
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
            <div class="scroll-down-arrow"><h2>⇩</h2></div>
        </section>

        <!-- 第二區塊：功能介紹（報導文章、影片學習、小測驗） -->
        <section class="section section-2">
            <h1>永續生活的第一步</h1>
            <p>在SustainHub，我們提供多樣化的功能，幫助你了解並實踐永續發展！</p>
            <div class="feature-container">
                <div class="feature-card">
                    <i class="fas fa-newspaper"></i>
                    <h3>報導文章展示與查詢</h3>
                    <p>瀏覽環保報導、研究文章及SDGs解讀，按主題分類（如SDGs 13、14、15）或關鍵字搜尋，快速找到感興趣的內容。</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-video"></i>
                    <h3>影片展示與學習</h3>
                    <p>觀看環保教育影片，了解可持續發展的實踐方法，按分類（如氣候變遷、環保技巧）選擇，隨時播放與學習。</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-question-circle"></i>
                    <h3>互動式小測驗</h3>
                    <p>參與小測驗，測試你的環保知識，測驗後查看得分與答案解析，並獲得個人化學習建議。</p>
                </div>
            </div>
        </section>

        <!-- 第三區塊：功能介紹（使用者社群、點數獎勳）+聯絡資訊 -->
        <section class="section section-3">
            <h1>加入我們的永續社群</h1>
            <div class="feature-container">
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <h3>使用者社群</h3>
                    <p>加入社群，參與討論、分享減碳經驗，支持關注、留言與點讚功能，與其他永續愛好者互動。</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-medal"></i>
                    <h3>點數獎勳機制</h3>
                    <p>完成學習、測驗或分享等行為可獲得點數，兌換虛擬獎勳（如專屬桌布），讓永續行動更有成就感！</p>
                </div>
            </div>
            <p class="contact-info">有任何問題？歡迎聯繫：support@sustainhub.com</p>
        </section>
    </div>

    <script>
    const text = "歡迎來到SustainHub 永續小站";
    let index = 0;
    const h1 = document.querySelector('.section-1 h1');

    function type() {
        try {
            if (index < text.length) {
                h1.textContent = text.slice(0, index + 1);
                index++;
                setTimeout(type, 150);
            }
        } catch (e) {
            console.error("Typing error:", e);
        }
    }

    window.onload = function() {
        h1.textContent = "";
        index = 0;
        type();
    };
    </script>
</body>
</html>