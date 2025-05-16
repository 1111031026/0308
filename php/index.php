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
                <div class="text-content">
                    <h1>關於海洋永續</h1>
                    <p class="description">海洋處境岌岌可危。我們苛索海洋太多了，破壞性捕撈、污染及氣候變遷，對獨特的海洋生態構成嚴重威脅。可幸的是，只要我們同心協力，就能保護海洋的蔚藍，守護所有仰賴海洋為生的生命與人們...</p>
                    <a href="ocean.php" class="read-more">閱讀更多 ⇨</a>
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
        </section>
        <section class="content">
            <div class="green-section">
                <div class="climate-slider swiper climate-slider-container">
                    <div class="swiper-wrapper">
                        <?php
                        // 查詢SDG13分類的文章（氣候行動），按創建時間排序
                        $sql = "SELECT * FROM article WHERE Category = 'sdg13' ORDER BY created_at DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<div class="swiper-slide climate-card">';
                                // 右側內容（原本是左側圖片）
                                echo '<div class="climate-card-content">';
                                echo '<h4 class="climate-card-title">' . htmlspecialchars($row['Title']) . '</h4>';
                                echo '<a href="article.php?id=' . $row['ArticleID'] . '" class="climate-card-button">閱讀更多</a>';
                                echo '</div>'; // 結束 climate-card-content

                                // 左側圖片（原本是右側內容）
                                if ($row['ImageURL']) {
                                    echo '<img src="../' . htmlspecialchars($row['ImageURL']) . '" alt="文章圖片" class="climate-card-image">';
                                } else {
                                    echo '<img src="../img/climate.jpg" alt="預設圖片" class="climate-card-image">';
                                }
                                echo '</div>'; // 結束 swiper-slide
                            }
                        }
                        ?>
                    </div>
                    <!-- 分頁器 -->
                    <div class="swiper-pagination-climate"></div>
                    <!-- 導航按鈕 -->
                    <div class="swiper-button-prev climate-prev"></div>
                    <div class="swiper-button-next climate-next"></div>
                </div>
                <h1>氣候永續的文章</h1>
            </div>
        </section>
        <!-- 陸域永續文章 -->
        <section class="content">
            <div class="brown-section">
                <h1>陸域永續的文章</h1>
                <div class="land-slider swiper land-slider-container">
                    <div class="swiper-wrapper">
                        <?php
                        // 查詢SDG15分類的文章（陸域生態），按創建時間排序
                        $sql = "SELECT * FROM article WHERE Category = 'sdg15' ORDER BY created_at DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<div class="swiper-slide land-card">';
                                // 左側圖片
                                if ($row['ImageURL']) {
                                    echo '<img src="../' . htmlspecialchars($row['ImageURL']) . '" alt="文章圖片" class="land-card-image">';
                                } else {
                                    echo '<img src="../img/forest.png" alt="預設圖片" class="land-card-image">';
                                }
                                // 右側內容
                                echo '<div class="land-card-content">';
                                echo '<h4 class="land-card-title">' . htmlspecialchars($row['Title']) . '</h4>';
                                echo '<a href="article.php?id=' . $row['ArticleID'] . '" class="land-card-button">閱讀更多</a>';
                                echo '</div>'; // 結束 land-card-content
                                echo '</div>'; // 結束 swiper-slide
                            }
                        }
                        ?>
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
            effect: "coverflow",
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: 3, // 一次顯示三張幻燈片
            coverflowEffect: {
                rotate: 0,
                stretch: 0,
                depth: 100,
                modifier: 2,
                slideShadows: true,
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
            slidesPerView: 1.5,
            spaceBetween: 30,
            centeredSlides: false,
            loop: true,
            pagination: {
                el: '.swiper-pagination-climate',
                clickable: true,
            },
            navigation: {
                nextEl: '.climate-next',
                prevEl: '.climate-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 1.5,
                    spaceBetween: 40
                }
            }
        });

        // 初始化陸域永續文章輪播
        var landSwiper = new Swiper('.land-slider', {
            slidesPerView: 1.5,
            spaceBetween: 30,
            centeredSlides: false,
            loop: true,
            pagination: {
                el: '.swiper-pagination-land',
                clickable: true,
            },
            navigation: {
                nextEl: '.land-next',
                prevEl: '.land-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 1.5,
                    spaceBetween: 40
                }
            }
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