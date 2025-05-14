<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>氣候行動 - 永續小站</title>
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
        <!-- 第一區塊：背景影片與標題 -->
        <section class="section section-1">
            <div class="ocean-banner">
                <video autoplay loop muted playsinline>
                    <source src="../img/climate.mp4" type="video/mp4">
                    您的瀏覽器不支援影片播放
                </video>
            </div>
            <h1>氣候行動</h1>
            <p class="intro-text">氣候變遷影響全球生態與人類社會，我們致力於推廣減緩與適應策略，鼓勵行動，共同守護地球的未來。</p>
            <div class="scroll-down-arrow"><span>⇩</span></div>
        </section>

        <!-- 第二區塊：關於氣候行動 -->
        <section class="section section-2">
            <video autoplay loop muted playsinline>
                <source src="../img/climate.mp4" type="video/mp4">
                您的瀏覽器不支援影片播放
            </video>
            <div class="intro-section">
                <h2>關於氣候行動</h2>
                <div class="ocean-info-grid">
                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <h3>氣候變遷影響</h3>
                            </div>
                            <div class="info-card-back">
                                <img src="../img/climate_ecosystem.jpg" alt="氣候變遷影響" class="info-image">
                                <div class="info-content">
                                    <h3>氣候變遷影響</h3>
                                    <p>氣候變遷導致極端天氣、海平面上升與生態系統崩潰，影響農業、水資源與人類健康，亟需全球合作應對。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <h3>碳排放挑戰</h3>
                            </div>
                            <div class="info-card-back">
                                <img src="../img/emissions.jpg" alt="碳排放" class="info-image">
                                <div class="info-content">
                                    <h3>碳排放挑戰</h3>
                                    <p>工業、交通與能源消耗產生大量溫室氣體，加速全球暖化。減少碳排放是氣候行動的核心挑戰。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <h3>可再生能源</h3>
                            </div>
                            <div class="info-card-back">
                                <img src="../img/renewable.jpg" alt="可再生能源" class="info-image">
                                <div class="info-content">
                                    <h3>可再生能源</h3>
                                    <p>太陽能、風能等可再生能源是減緩氣候變遷的關鍵。推廣清潔能源技術有助於實現低碳未來。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <h3>永續行動</h3>
                            </div>
                            <div class="info-card-back">
                                <img src="../img/climate_action.jpg" alt="永續行動" class="info-image">
                                <div class="info-content">
                                    <h3>永續行動</h3>
                                    <p>永續小站提供氣候教育、碳足跡計算與行動指南，鼓勵個人與企業參與，共同打造永續未來。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 第三區塊：最新貼文 -->
        <section class="section section-3">
            <video autoplay loop muted playsinline>
                <source src="../img/climate2.mp4" type="video/mp4">
                您的瀏覽器不支援影片播放
            </video>
            <h3>最新貼文</h3>
            <div class="container">
                <div class="article-slider">
                    <div class="article-content">
                        <?php
                        require_once 'db_connect.php';
                        $sql = "SELECT * FROM article WHERE Category = 'sdg13' ORDER BY created_at DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<div class="article-card">';
                                echo '<h4>' . htmlspecialchars($row['Title']) . '</h4>';
                                if ($row['ImageURL']) {
                                    echo '<img src="../' . htmlspecialchars($row['ImageURL']) . '" alt="文章圖片">';
                                } else {
                                    echo '<img src="../img/Climate-change.jpg" alt="預設圖片">';
                                }
                                echo '<p>' . htmlspecialchars($row['Description']) . '</p>';
                                echo '<a href="article.php?id=' . $row['ArticleID'] . '" class="read-more">閱讀更多</a>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="article-card">';
                            echo '<h4>暫無相關文章</h4>';
                            echo '<p>目前沒有氣候行動相關的文章。</p>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <button class="slider-btn2 prev-btn"><</button>
                    <button class="slider-btn2 next-btn">></button>
                </div>
            </div>
        </section>

        <!-- 第四區塊：搜尋文章 -->
        <section class="section section-4">
            <video autoplay loop muted playsinline>
                <source src="../img/climate2.mp4" type="video/mp4">
                您的瀏覽器不支援影片播放
            </video>
            <h3>搜尋氣候文章</h3>
            <div class="search-container">
                <form id="search-form">
                    <input type="text" name="search" id="search-input" placeholder="輸入關鍵字搜尋..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">搜尋</button>
                </form>
            </div>
            <div class="posts-grid" id="posts-grid">
            </div>
            <div class="pagination">
                <button class="pagination-btn prev-btn" id="prev-btn" disabled>上一頁</button>
                <div class="page-input-container">
                    <input type="number" id="page-input" min="1" value="1" class="page-input">
                    <span>/ <span id="total-pages">1</span> 頁</span>
                </div>
                <button class="pagination-btn next-btn" id="next-btn">下一頁</button>
            </div>
        </section>
    </div>

    <script>
    $(document).ready(function() {
        let navbar = $('.navbar');
        let currentPage = 1;
        let totalPages = 1;
        let searchQuery = $('#search-input').val();

        $(document).mousemove(function(e) {
            if (e.clientY <= 100) {
                navbar.css('transform', 'translateY(100px)');
            } else {
                navbar.css('transform', 'translateY(-100px)');
            }
        });

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

        function loadArticles(page, search) {
            $('#posts-grid').html('<p>載入中...</p>');
            $.ajax({
                url: 'fetch_climate.php',
                method: 'GET',
                data: { page: page, search: search },
                dataType: 'json',
                success: function(response) {
                    const postsGrid = $('#posts-grid');
                    postsGrid.empty();
                    if (response.articles.length > 0) {
                        response.articles.forEach(article => {
                            postsGrid.append(`
                                <div class="post-card">
                                    <img src="${article.ImageURL}" alt="文章圖片">
                                    <h4>${article.Title}</h4>
                                    <p>${article.Description}</p>
                                    <a href="article.php?id=${article.ArticleID}" class="read-more">閱讀更多</a>
                                </div>
                            `);
                        });
                    } else {
                        postsGrid.append(`
                            <div class="post-card">
                                <h4>暫無相關文章</h4>
                                <p>沒有找到符合條件的文章。</p>
                            </div>
                        `);
                    }
                    totalPages = response.totalPages;
                    $('#total-pages').text(totalPages);
                    $('#page-input').val(page);
                    $('#page-input').attr('max', totalPages); // 修正為 max
                    $('#prev-btn').prop('disabled', page === 1);
                    $('#next-btn').prop('disabled', page >= totalPages);
                },
                error: function() {
                    $('#posts-grid').html('<p>載入文章失敗，請稍後重試。</p>');
                }
            });
        }

        loadArticles(currentPage, searchQuery);

        $('#search-form').on('submit', function(e) {
            e.preventDefault();
            searchQuery = $('#search-input').val();
            currentPage = 1;
            loadArticles(currentPage, searchQuery);
        });

        $('#prev-btn').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                loadArticles(currentPage, searchQuery);
            }
        });

        $('#next-btn').on('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                loadArticles(currentPage, searchQuery);
            }
        });



        $('#page-input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                let pageNum = parseInt($(this).val());
                if (pageNum < 1) { pageNum = 1; }
                else if (pageNum > totalPages) { pageNum = totalPages; }
                if (pageNum !== currentPage) {
                    currentPage = pageNum;
                    loadArticles(currentPage, searchQuery);
                }
            }
        });

        $('#page-input').on('blur', function() {
            let pageNum = parseInt($(this).val());
            if (pageNum < 1) { pageNum = 1; $(this).val(1); }
            else if (pageNum > totalPages) { pageNum = totalPages; $(this).val(totalPages); }
            if (pageNum !== currentPage) {
                currentPage = pageNum;
                loadArticles(currentPage, searchQuery);
            }
        });
    });
    </script>
</body>
</html>