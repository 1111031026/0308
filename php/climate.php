<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>氣候行動 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/nav2.css">
    <link rel="stylesheet" href="../css/climate.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <!-- 引入 Noto Sans 思源黑體 -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <div class="page-container">
        <!-- 第一區塊：背景圖片與標題 -->
        <section class="section section-1">
            <div class="ocean-banner">
                <img src="../img/Climate-change.jpg" alt="氣候變遷背景">
            </div>
            <h1>氣候行動</h1>
            <div class="scroll-down-arrow"><span>⇩</span></div>
        </section>

        <!-- 第二區塊：關於海洋永續 -->
        <section class="section section-2">
            <div class="intro-section">
                <h2>關於氣候行動</h2>
                <p>氣候變遷是當今人類面臨的最大環境挑戰之一，它影響著地球上的每個生態系統和人類社會。在永續小站，我們致力於提供氣候變遷的科學知識，分享減緩與適應策略，鼓勵每個人採取行動，共同守護我們的地球家園。</p>
            </div>
        </section>

        <!-- 第三區塊：最新貼文 -->
        <section class="section section-3">
            <h3>最新貼文</h3>
            <div class="posts-grid">
                <div class="post-card">
                    <img src="../img/eco.png" alt="生態">
                    <h4>減碳行動</h4>
                    <p>探索個人和企業的減碳方案。</p>
                </div>
                <div class="post-card">
                    <img src="../img/recycle.png" alt="循環">
                    <h4>綠色能源</h4>
                    <p>認識再生能源發展與應用。</p>
                </div>
                <div class="post-card">
                    <img src="../img/earth.png" alt="地球">
                    <h4>氣候調適</h4>
                    <p>因應氣候變遷的適應策略。</p>
                </div>
                <div class="post-card">
                    <img src="../img/city.png" alt="城市">
                    <h4>低碳城市</h4>
                    <p>建構氣候韌性城市發展。</p>
                </div>
            </div>
        </section>
    </div>
    <script>
        $(document).ready(function() {
            let navbar = $('.navbar');
            let timer;

            // 監聽滑鼠移動事件
            $(document).mousemove(function(e) {
                if (e.clientY <= 100) {
                    navbar.css('transform', 'translateY(100px)');
                } else {
                    navbar.css('transform', 'translateY(-100px)');
                }
            });
        });
    </script>
</body>
</html>