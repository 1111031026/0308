<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>地景保育 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/nav2.css">
    <link rel="stylesheet" href="../css/landscape.css">
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
                <img src="../img/mountain.jpg" alt="地景保育背景">
            </div>
            <h1>地景保育</h1>
            <div class="scroll-down-arrow"><span>⇩</span></div>
        </section>

        <!-- 第二區塊：關於海洋永續 -->
        <section class="section section-2">
            <div class="intro-section">
                <h2>關於地景保育</h2>
                <p>地景保育是保護自然與人文景觀的重要環境議題，它涉及山脈、森林、濕地等多樣化的地理環境。在永續小站，我們致力於推廣地景保育的重要性，分享保護策略與行動方案，邀請每個人一同參與，守護這片美麗的土地。</p>
            </div>
        </section>

        <!-- 第三區塊：最新貼文 -->
        <section class="section section-3">
            <h3>最新貼文</h3>
            <div class="posts-grid">
                <div class="post-card">
                    <img src="../img/mountain.png" alt="山脈">
                    <h4>山脈保育</h4>
                    <p>探索山地生態系統的保護策略。</p>
                </div>
                <div class="post-card">
                    <img src="../img/forest.png" alt="森林">
                    <h4>森林保護</h4>
                    <p>認識森林生態與永續管理。</p>
                </div>
                <div class="post-card">
                    <img src="../img/desert.jpg" alt="沙漠">
                    <h4>沙漠防治</h4>
                    <p>對抗沙漠化的環境行動。</p>
                </div>
                <div class="post-card">
                    <img src="../img/earth.png" alt="地球">
                    <h4>地景教育</h4>
                    <p>推廣地景保育的環境教育。</p>
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