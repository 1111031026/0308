<?php
require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/nav.css">
    <!-- 引入 Slick.js 和 jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
</head>

<body>
    <header>
        <?php
        include "nav.php";
        ?>
    </header>
    <main>
        <section class="hero">
            <h2>最新文章</h2>
            <!-- Swiper -->
            <div class="swiper heroSwiper">
                <div class="swiper-wrapper">
                    <?php
                    // 查詢最新的五篇文章
                    $sql = "SELECT * FROM article ORDER BY created_at DESC LIMIT 5";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="swiper-slide">';
                            echo '<a href="article.php?id=' . $row['ArticleID'] . '">';
                            if ($row['ImageURL']) {
                                echo '<img src="../' . htmlspecialchars($row['ImageURL']) . '" alt="文章圖片" class="hero-item">';
                            } else {
                                echo '<img src="../img/ocean.jpg" alt="預設圖片" class="hero-item">';
                            }
                            echo '</a>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                <!-- 導航按鈕 -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
                <!-- 分頁器 -->
                <div class="swiper-pagination"></div>
            </div>
        </section>
        <section class="content">
            <div class="blue-section">
                <div class="blue-text-content">
                    <h1>關於海洋永續</h1>
                    <p class="ocean-description">海洋處境岌岌可危。我們苛索海洋太多了，破壞性捕撈、污染及氣候變遷，對獨特的海洋生態構成嚴重威脅。可幸的是，只要我們同心協力，就能保護海洋的蔚藍，守護所有仰賴海洋為生的生命與人們...</p>
                    <a href="ocean.php" class="ocean-read-more">閱讀更多 ⇨</a>
                </div>
                <div class="ocean-slider swiper ocean-slider-container">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <img src="../img/inocean1.jpg" alt="海洋生態系統" class="ocean-card-image">
                        </div>
                        <div class="swiper-slide">
                            <img src="../img/inocean2.jpg" alt="海洋威脅" class="ocean-card-image">
                        </div>
                        <div class="swiper-slide">
                            <img src="../img/inocean3.jpg" alt="氣候變遷" class="ocean-card-image">
                        </div>
                    </div>
                    <!-- 分頁器 -->
                    <div class="swiper-pagination-ocean"></div>
                    <!-- 導航按鈕 -->
                    <div class="swiper-button-prev ocean-prev"></div>
                    <div class="swiper-button-next ocean-next"></div>
                </div>
            </div>
            <div class="conservation-actions">
                <h2>1990年 - 2000年的海洋保育行動</h2>
                <div class="action-list">
                    <div class="action-item">
                        <h3>南極海鯨魚保護區</h3>
                        <p>1994年國際捕鯨委員會成立，南極高達5,000萬平方公里禁止捕鯨的保護區。</p>
                    </div>
                    <div class="action-item">
                        <h3>不要油污</h3>
                        <p>多次行動支持與公眾力量，成功促使SHELL放棄向海上丟棄鑽油平台Brent Spar的決定。</p>
                    </div>
                    <div class="action-item">
                        <h3>禁止流刺網</h3>
                        <p>各地團體全力不懈努力，先後推動聯合國及歐盟禁止大面積流刺網捕撈。</p>
                    </div>
                    <div class="action-item">
                        <h3>阻擋商業捕鯨</h3>
                        <p>多年政治遊說工作，成功阻擋商業捕鯨案會否決日本發起的恢復商業捕鯨提案。</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="green-section">
                <div class="climate-slider swiper climate-slider-container">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <img src="../img/climate.jpg" alt="氣候變遷" class="climate-card-image">
                        </div>
                        <div class="swiper-slide">
                            <img src="../img/shore.mp4" alt="海平面上升" class="climate-card-image">
                        </div>
                        <div class="swiper-slide">
                            <img src="../img/shore2.mp4" alt="極端天氣" class="climate-card-image">
                        </div>
                    </div>
                    <!-- 分頁器 -->
                    <div class="swiper-pagination-climate"></div>
                    <!-- 導航按鈕 -->
                    <div class="swiper-button-prev climate-prev"></div>
                    <div class="swiper-button-next climate-next"></div>
                </div>
                <div class="green-text-content">
                    <h1>關於氣候永續</h1>
                    <p class="climate-description">氣候變遷已成為全球最嚴峻的挑戰之一。極端天氣事件頻繁發生，海平面上升，生態系統受到威脅。然而，透過減少碳排放、發展可再生能源和推動永續生活方式，我們仍有機會扭轉局勢，共同守護我們唯一的家園...</p>
                    <a href="climate.php" class="climate-read-more">閱讀更多 ⇨</a>
                </div>
            </div>
        </section>
        <!-- 陸域永續文章 -->
        <section class="content">
            <div class="brown-section">
                <div class="brown-text-content">
                    <h1>關於陸域永續</h1>
                    <p class="land-description">陸地生態系統正面臨前所未有的威脅。森林砍伐、沙漠化、生物多樣性喪失等問題日益嚴重。保護陸域生態系統不僅關乎野生動植物的生存，也與人類的福祉息息相關。透過永續土地管理和生態保育，我們能夠恢復自然平衡，守護地球的綠色未來...</p>
                    <a href="landscape.php" class="land-read-more">閱讀更多 ⇨</a>
                </div>
                <div class="land-slider swiper land-slider-container">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <img src="../img/forest.png" alt="森林生態" class="land-card-image">
                        </div>
                        <div class="swiper-slide">
                            <img src="../img/mountain.jpg" alt="山地生態" class="land-card-image">
                        </div>
                        <div class="swiper-slide">
                            <img src="../img/desert.jpg" alt="沙漠化" class="land-card-image">
                        </div>
                    </div>
                    <!-- 分頁器 -->
                    <div class="swiper-pagination-land"></div>
                    <!-- 導航按鈕 -->
                    <div class="swiper-button-prev land-prev"></div>
                    <div class="swiper-button-next land-next"></div>
                </div>
            </div>
        </section>
    </main>

    <!-- 初始化 Slick.js -->
    <script>
        var heroSwiper = new Swiper(".heroSwiper", {
            effect: "cube", // 改为Cube效果
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: 1, // Cube效果通常使用1个幻灯片视图
            cubeEffect: {
                shadow: true, // 启用阴影效果
                slideShadows: true, // 启用幻灯片阴影
                shadowOffset: 20, // 阴影偏移
                shadowScale: 0.94 // 阴影缩放
            },
            loop: true,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });
        // 初始化海洋永續文章輪播
        var oceanSwiper = new Swiper('.ocean-slider', {
            slidesPerView: 1,
            spaceBetween: 0,
            centeredSlides: true,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination-ocean',
                type: 'progressbar',
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });

        // 初始化氣候永續文章輪播
        var climateSwiper = new Swiper('.climate-slider', {
            slidesPerView: 1,
            spaceBetween: 0,
            centeredSlides: true,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination-climate',
                type: 'progressbar',
            },
            navigation: {
                nextEl: ".climate-next",
                prevEl: ".climate-prev",
            },
        });

        // 初始化陸域永續文章輪播
        var landSwiper = new Swiper('.land-slider', {
            slidesPerView: 1,
            spaceBetween: 0,
            centeredSlides: true,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination-land',
                type: 'progressbar',
            },
            navigation: {
                nextEl: ".land-next",
                prevEl: ".land-prev",
            },
        });
        // 初始化所有 article-slider（海洋、氣候、陸域）
        $('.article-slider .article-content').each(function() {
            $(this).slick({
                dots: true,
                infinite: true,
                speed: 500,
                slidesToShow: 3,
                slidesToScroll: 1,
                prevArrow: $(this).closest('.article-slider').find('.prev-btn'),
                nextArrow: $(this).closest('.article-slider').find('.next-btn'),
                responsive: [{
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 1
                    }
                }]
            });
        });
    </script>
</body>

</html>