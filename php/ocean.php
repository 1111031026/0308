<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>海洋永續 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav2.css">
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
            <div class="container">
                <!-- 滾動文章區域 -->
                <div class="article-slider">
                    <div class="article-content">
                        <?php
                        require_once 'db_connect.php';
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
                    <button class="slider-btn2 prev-btn"><</button>
                    <button class="slider-btn2 next-btn">></button>
                </div>
            </div>
        </section>

        <!-- 第四區塊：搜尋文章 -->
        <section class="section section-4">
            <h3>搜尋海洋文章</h3>
            <div class="search-container">
                <form method="GET" action="ocean.php">
                    <input type="text" name="search" placeholder="輸入關鍵字搜尋..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">搜尋</button>
                </form>
            </div>
            <div class="posts-grid">
                <?php
                require_once 'db_connect.php';
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                
                if (!empty($search)) {
                    $sql = "SELECT * FROM article WHERE Category = 'sdg14' AND Title LIKE '%" . $conn->real_escape_string($search) . "%' ORDER BY created_at DESC";
                } else {
                    $sql = "SELECT * FROM article WHERE Category = 'sdg14' ORDER BY created_at DESC";
                }
                
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="post-card">';
                        if ($row['ImageURL']) {
                            echo '<img src="../' . htmlspecialchars($row['ImageURL']) . '" alt="文章圖片">';
                        } else {
                            echo '<img src="../img/ocean.jpg" alt="預設圖片">';
                        }
                        echo '<h4>' . htmlspecialchars($row['Title']) . '</h4>';
                        echo '<p>' . htmlspecialchars($row['Description']) . '</p>';
                        echo '<a href="article.php?id=' . $row['ArticleID'] . '" class="read-more">閱讀更多</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="post-card">';
                    echo '<h4>暫無相關文章</h4>';
                    echo '<p>沒有找到符合條件的文章。</p>';
                    echo '</div>';
                }
                ?>
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

    // 初始化Slick輪播
    $('.article-content').slick({
        infinite: true,
        slidesToShow: 3,
        slidesToScroll: 1,
        prevArrow: $('.prev-btn'),
        nextArrow: $('.next-btn'),
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 2
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 1
                }
            }
        ]
    });
});
</script>
</body>
</html>