<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站</title>
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
                    <div><img src="../img/desert.jpg" alt="沙漠圖片" class="hero-item"></div>
                    <div><img src="../img/turtle.jpeg" alt="海龜圖片" class="hero-item"></div>
                    <div><img src="../img/forest.png" alt="森林圖片" class="hero-item"></div>
                    <div><img src="../img/mountain.png" alt="山脈圖片" class="hero-item"></div>
                    <div><img src="../img/ocean.jpg" alt="海洋圖片" class="hero-item"></div>
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
                        require_once 'db_connect.php';
                        
                        // 查詢SDG14分類的文章，按創建時間排序
                        $sql = "SELECT * FROM article WHERE Category = 'sdg14' ORDER BY created_at DESC";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
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
                        
                        $conn->close();
                        ?>
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