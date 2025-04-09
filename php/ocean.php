<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>海洋永續 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/ocean.css">
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
                <img src="../img/ocean.gif" alt="海洋背景">
            </div>
            <h1>海洋永續</h1>
            <div class="scroll-down-arrow"><span>⇩</span></div>
        </section>

        <!-- 第二區塊：關於海洋永續 -->
        <section class="section section-2">
            <div class="intro-section">
                <h2>關於海洋永續</h2>
                <p>海洋覆蓋地球70%以上的表面，是生命的搖籃，卻正面臨污染、過度捕撈與氣候變遷的威脅。在永續小站，我們致力於推廣海洋保護意識，提供實用的行動方案，讓每個人成為改變的一部分。</p>
            </div>
        </section>

        <!-- 第三區塊：最新貼文 -->
        <section class="section section-3">
            <h3>最新貼文</h3>
            <div class="posts-grid">
                <div class="post-card">
                    <img src="../img/eco.png" alt="生態">
                    <h4>生態保育</h4>
                    <p>探索如何保護海洋生物多樣性。</p>
                </div>
                <div class="post-card">
                    <img src="../img/recycle.png" alt="循環">
                    <h4>資源循環</h4>
                    <p>減少海洋塑膠污染的實用方法。</p>
                </div>
                <div class="post-card">
                    <img src="../img/earth.png" alt="地球">
                    <h4>環境保護</h4>
                    <p>氣候變遷如何影響海洋生態。</p>
                </div>
                <div class="post-card">
                    <img src="../img/city.png" alt="城市">
                    <h4>永續城市</h4>
                    <p>沿海城市的永續發展策略。</p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>