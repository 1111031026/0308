<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/nav.css">
    <!-- 引入 Slick.js 和 jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
</head>

<body>
    <header>
        <?php
        include "nav.html";
        ?>
    </header>

    <main>
        <section class="hero">
            <h2>最新報導</h2>
            <div class="hero-slider">
                <div class="hero-content">
                    <div><img src="../img/desert.jpg" alt="沙漠圖片" class="hero-item"></div>
                    <div><img src="../img/turtle.jpg" alt="海龜圖片" class="hero-item"></div>
                    <div><img src="../img/forest.jpg" alt="森林圖片" class="hero-item"></div>
                    <div><img src="../img/mountain.jpg" alt="山脈圖片" class="hero-item"></div>
                    <div><img src="../img/ocean.jpg" alt="海洋圖片" class="hero-item"></div>
                </div>
                <!-- 左右按鈕 -->
                <button class="slider-btn prev-btn"><</button>
                <button class="slider-btn next-btn">></button>
            </div>
        </section>

        <section class="content">
            <div class="blue-section">

                <h3>關於海洋的評論和文章</h3>
                <div class="container">
                    <!-- 海洋圖片區塊 -->
                    <div class="ocean-image-container">
                        <img src="../img/ocean1.jpg" alt="海洋圖片" class="ocean-image">
                    </div>

                    <!-- 滾動文章區域 -->
                    <div class="article-slider">
                        <div class="article-content">
                            <div class="article-card">
                                <h4>海洋污染的現況</h4>
                                <p>海洋污染問題日益嚴重，塑料垃圾成為主要威脅。本文探討如何透過個人行動減少污染。</p>
                            </div>
                            <div class="article-card">
                                <h4>珊瑚礁的保護</h4>
                                <p>全球珊瑚礁面臨白化危機，了解科學家們的保護策略與未來展望。</p>
                            </div>
                            <div class="article-card">
                                <h4>海龜的生存挑戰</h4>
                                <p>海龜因棲地破壞和漁網纏繞面臨生存危機，探討可能的救援方法。</p>
                            </div>
                            <div class="article-card">
                                <h4>海洋能源的未來</h4>
                                <p>潮汐與波浪能或成為可再生能源的重要來源，分析其發展潛力。</p>
                            </div>
                        </div>
                        <!-- 左右按鈕 -->
                        <button class="slider-btn2 prev-btn"><</button>
                        <button class="slider-btn2 next-btn">></button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- 初始化 Slick.js -->
    <script>
        // 初始化 hero-slider
        $('.hero-content').slick({
            dots: true,
            infinite: true,
            speed: 500,
            slidesToShow: 1,
            slidesToScroll: 1,
            prevArrow: $('.prev-btn'),
            nextArrow: $('.next-btn'),
            autoplay: true,
            autoplaySpeed: 2500
        });

        // 初始化 article-slider
        $('.article-content').slick({
            dots: true,
            infinite: true,
            speed: 500,
            slidesToShow: 3, // 根據螢幕大小調整顯示卡片數
            slidesToScroll: 1,
            prevArrow: $('.article-slider .prev-btn'),
            nextArrow: $('.article-slider .next-btn'),
            responsive: [{
                breakpoint: 768,
                settings: {
                    slidesToShow: 1
                }
            }]
        });

        $(document).ready(function() {
            $('.hero-content').slick({
                dots: true,
                infinite: true,
                speed: 500,
                slidesToShow: 1,
                slidesToScroll: 1,
                prevArrow: $('.prev-btn'),
                nextArrow: $('.next-btn')
            });
        });
    </script>
</body>

</html>