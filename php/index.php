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
</head>

<body>
    <header>
        <?php
        include "nav.php";
        ?>
    </header>

    <main>
        <section class="hero">
            <h2>最新報導</h2>
            <div class="hero-slider">
                <div class="hero-content">
                    <?php
                    // 查詢最新的五篇文章
                    $sql = "SELECT * FROM article ORDER BY created_at DESC LIMIT 5";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div>';
                            echo '<a href="article.php?id=' . $row['ArticleID'] . '">';
                            if ($row['ImageURL']) {
                                echo '<img src="../' . htmlspecialchars($row['ImageURL']) . '" alt="文章圖片" class="hero-item">';
                            } else {
                                echo '<img src="../img/ocean.jpg" alt="預設圖片" class="hero-item">';
                            }
                            echo '</a>';
                            echo '</div>';
                        }
                    } else {
                        // 如果沒有文章，顯示預設圖片
                        echo '<div><img src="../img/ocean.jpg" alt="預設圖片" class="hero-item"></div>';
                    }
                    ?>
                </div>
                <!-- 左右按鈕 -->
                <button class="slider-btn prev-btn"><</button>
                <button class="slider-btn next-btn">></button>
            </div>
        </section>

        <section class="content">
            <div class="blue-section">
                <h3>關於海洋永續的文章</h3>
                <div class="container">
                    <!-- 海洋圖片區塊 -->
                    <div class="ocean-image-container">
                        <img src="../img/ocean1.jpg" alt="海洋圖片" class="ocean-image">
                    </div>
                    <!-- 滾動文章區域 -->
                    <div class="article-slider">
                        <div class="article-content">
                            <?php
                            // 查詢SDG14分類的文章，按創建時間排序
                            $sql = "SELECT * FROM article WHERE Category = 'sdg14' ORDER BY created_at DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<div class="article-card">';
                                    echo '<h4>' . htmlspecialchars($row['Title']) . '</h4>';
                                    if ($row['ImageURL']) {
                                        echo '<img src="../' . htmlspecialchars($row['ImageURL']) . '" alt="文章圖片">';
                                    } else {
                                        echo '<img src="../img/ocean.jpg" alt="預設圖片">';
                                    }
                                    echo '<p>' . htmlspecialchars($row['Description']) . '</p>';
                                    echo '<a href="article.php?id=' . $row['ArticleID'] . '" class="read-more">閱讀更多</a>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="article-card">';
                                echo '<h4>暫無相關文章</h4>';
                                echo '<p>目前沒有海洋永續相關的文章。</p>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                        <!-- 左右按鈕 -->
                        <button class="slider-btn2 prev-btn">
                            <</button>
                                <button class="slider-btn2 next-btn">></button>
                    </div>
                </div>
            </div>
        </section>
        <!-- 氣候永續文章 -->
        <section class="content">
            <div class="green-section">
                <h3>關於氣候永續的文章</h3>
                <div class="container">
                    <!-- 氣候圖片區塊 -->
                    <div class="climate-image-container">
                        <img src="../img/climate.jpg" alt="氣候圖片" class="climate-image">
                    </div>
                    <!-- 滾動文章區域 -->
                    <div class="article-slider">
                        <div class="article-content">
                            <?php

                            // 查詢SDG13分類的文章（氣候行動），按創建時間排序
                            $sql = "SELECT * FROM article WHERE Category = 'sdg13' ORDER BY created_at DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<div class="article-card">';
                                    echo '<h4>' . htmlspecialchars($row['Title']) . '</h4>';
                                    if ($row['ImageURL']) {
                                        echo '<img src="../' . htmlspecialchars($row['ImageURL']) . '" alt="文章圖片">';
                                    } else {
                                        echo '<img src="../img/climate.jpg" alt="預設圖片">';
                                    }
                                    echo '<p>' . htmlspecialchars($row['Description']) . '</p>';
                                    echo '<a href="article.php?id=' . $row['ArticleID'] . '" class="read-more">閱讀更多</a>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="article-card">';
                                echo '<h4>暫無相關文章</h4>';
                                echo '<p>目前沒有氣候永續相關的文章。</p>';
                                echo '</div>';
                            }

                            ?>
                        </div>
                        <!-- 左右按鈕 -->
                        <button class="slider-btn2 prev-btn">
                            <</button>
                                <button class="slider-btn2 next-btn">></button>
                    </div>
                </div>
            </div>
        </section>

        <!-- 陸域永續文章 -->
        <section class="content">
            <div class="brown-section">
                <h3>關於陸域永續的文章</h3>
                <div class="container">
                    <!-- 陸域圖片區塊 -->
                    <div class="land-image-container">
                        <img src="../img/forest.png" alt="陸域圖片" class="land-image">
                    </div>
                    <!-- 滾動文章區域 -->
                    <div class="article-slider">
                        <div class="article-content">
                            <?php

                            // 查詢SDG15分類的文章（陸域生態），按創建時間排序
                            $sql = "SELECT * FROM article WHERE Category = 'sdg15' ORDER BY created_at DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<div class="article-card">';
                                    echo '<h4>' . htmlspecialchars($row['Title']) . '</h4>';
                                    if ($row['ImageURL']) {
                                        echo '<img src="../' . htmlspecialchars($row['ImageURL']) . '" alt="文章圖片">';
                                    } else {
                                        echo '<img src="../img/forest.png" alt="預設圖片">';
                                    }
                                    echo '<p>' . htmlspecialchars($row['Description']) . '</p>';
                                    echo '<a href="article.php?id=' . $row['ArticleID'] . '" class="read-more">閱讀更多</a>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="article-card">';
                                echo '<h4>暫無相關文章</h4>';
                                echo '<p>目前沒有陸域永續相關的文章。</p>';
                                echo '</div>';
                            }

                            ?>
                        </div>
                        <?php
                        $conn->close();
                        ?>
                        <!-- 左右按鈕 -->
                        <button class="slider-btn2 prev-btn">
                            <</button>
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
            prevArrow: $('.hero .prev-btn'),
            nextArrow: $('.hero .next-btn'),
            autoplay: true,
            autoplaySpeed: 2500
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