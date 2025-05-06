<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/daolan.css">
    <!-- 引入 Noto Sans 思源黑體 -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;700&display=swap" rel="stylesheet">
    <!-- 引入 Font Awesome 圖標 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>導覽頁</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
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

        <!-- 第二區塊：永續發展主題介紹 -->
        <section class="section section-2">
            <h1>探索永續發展的核心領域</h1>
            <p>在SustainHub，我們專注於三大永續發展目標，攜手創造更美好的地球！</p>
            <div class="feature-container">
                <div class="feature-card">
                    <i class="fas fa-cloud-sun"></i>
                    <h3>氣候行動 (SDG 13)</h3>
                    <p>了解氣候變遷的影響與挑戰，學習減緩與適應策略，從日常生活開始實踐低碳行動，為地球降溫盡一份心力。</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-water"></i>
                    <h3>海洋保育 (SDG 14)</h3>
                    <p>探索海洋生態系統的重要性，認識海洋污染問題，參與海洋保護行動，守護藍色星球的永續未來。</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-mountain"></i>
                    <h3>地景保育 (SDG 15)</h3>
                    <p>關注陸地生態系統，保護森林與生物多樣性，推廣永續土地利用，維護地球的自然資源與生態平衡。</p>
                </div>
            </div>
        </section>

        <!-- 第三區塊：永續學習與社群參與 -->
        <section class="section section-3">
            <h1>永續學習・共創未來</h1>
            <div class="feature-container">
                <div class="feature-card">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>多元學習資源</h3>
                    <p>豐富的環保報導、教育影片和互動式測驗，幫助你深入了解永續議題，培養環境意識，成為地球永續的推動者。</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <h3>永續社群平台</h3>
                    <p>加入充滿熱情的永續發展社群，分享環保心得與實踐經驗，透過討論與互動，集結群眾力量，共同為地球永續努力。</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-award"></i>
                    <h3>永續成就系統</h3>
                    <p>參與學習活動、完成環保任務可獲得積分，解鎖特別獎勵，見證你在永續發展道路上的每一步進展！</p>
                </div>
            </div>
            <div class="button-container">
                <button class="btn btn-register" onclick="document.querySelector('.section-1').scrollIntoView({behavior: 'smooth'})">立即體驗</button>
            </div>
            <br>
            <br>
            <p class="contact-info">攜手邁向永續未來！有任何問題歡迎聯繫：SustainHub0support@gmail.com</p>
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