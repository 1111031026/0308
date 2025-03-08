<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/oc.css">
    <!-- 引入 Slick.js 和 jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <style>
        .hero-slider {
            position: relative;
        }

        .slider-controls {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .slider-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            margin: 0 10px;
        }

        .slick-dots {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }

        .slick-dots li {
            margin: 0 5px;
        }

        .slick-dots button {
            background: #ccc;
            border: none;
            border-radius: 50%;
            width: 10px;
            height: 10px;
            cursor: pointer;
        }

        .slick-dots .slick-active button {
            background: #333;
        }
    </style>
</head>

<body>
    <header>
        <?php
        include "nav.html";
        ?>
    </header>

    <main>
        <section class="hero">
            <div class="hero-slider">
                <div class="hero-content">
                    <!-- 簡介區 -->
                    <div class="intro-section">
                        <h3>關於 SDGs</h3>
                        <p>這裡是 SDGs 的簡介內容...</p>
                        <img src="sdg14.png" alt="SDG 14: Life Below Water">
                    </div>
                    <!-- 發文區 -->
                    <div class="post-section">
                        <h3>留下一些心得吧</h3>
                        <form action="submit_post.php" method="post">
                            <textarea name="content" placeholder="請輸入您的貼文內容..."></textarea>
                            <button type="submit">發佈</button>
                        </form>
                    </div>
                </div>
                <!-- 左右按鈕 -->
                <div class="slider-controls">
                    <button class="slider-btn prev-btn">◄</button>
                    <button class="slider-btn next-btn">►</button>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="blue-section">
                <img src="clouds-sea.jpg" alt="雲和海圖片" class="blue-bg">
                <h3>關於海洋的評論和文章</h3>
                <div class="latest-posts">
                    <h4>最新貼文</h4>
                    <div class="posts-grid">
                        <div class="post-thumbnail">
                            <img src="post1.jpg" alt="貼文1">
                            <p>貼文標題1</p>
                        </div>
                        <div class="post-thumbnail">
                            <img src="post2.jpg" alt="貼文2">
                            <p>貼文標題2</p>
                        </div>
                        <div class="post-thumbnail">
                            <img src="post3.jpg" alt="貼文3">
                            <p>貼文標題3</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- 初始化 Slick.js -->
    <script>
        $(document).ready(function() {
            $('.hero-content').slick({
                dots: true,
                infinite: true,
                speed: 500,
                slidesToShow: 1,
                slidesToScroll: 1,
                prevArrow: $('.prev-btn'),
                nextArrow: $('.next-btn'),
                autoplay: false
            });
        });
    </script>
</body>

</html>