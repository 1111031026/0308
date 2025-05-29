<?php
session_start();
require_once 'db_connect.php';

// --- 更新文章瀏覽次數邏輯 ---
if (
    isset($_GET['id']) &&
    isset($_SESSION['login_session']) && $_SESSION['login_session'] === true &&
    isset($_SESSION['user_id']) &&
    isset($_SESSION['role']) && $_SESSION['role'] !== 'Teacher'
) {
    $articleID = intval($_GET['id']);
    $userID = $_SESSION['user_id'];

    // 檢查是否已有觀看紀錄
    $stmtCheck = $conn->prepare("SELECT 1 FROM user_article_views WHERE UserID = ? AND ArticleID = ?");
    $stmtCheck->bind_param("ii", $userID, $articleID);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 0) {
        $stmtInsertView = $conn->prepare("INSERT INTO user_article_views (UserID, ArticleID) VALUES (?, ?)");
        $stmtInsertView->bind_param("ii", $userID, $articleID);
        $inserted = $stmtInsertView->execute();
        $stmtInsertView->close();

        if ($inserted) {
            $stmtUpdateAchieve = $conn->prepare(
                "INSERT INTO achievement (UserID, ArticlesViewed, TotalPoints, ChoiceQuestionsCorrect, TFQuestionsCorrect, FillinQuestionsCorrect)
                 VALUES (?, 1, 0, 0, 0, 0)
                 ON DUPLICATE KEY UPDATE ArticlesViewed = ArticlesViewed + 1"
            );
            $stmtUpdateAchieve->bind_param("i", $userID);
            $stmtUpdateAchieve->execute();
            $stmtUpdateAchieve->close();
        } else {
            error_log("Failed to insert view record: UserID $userID, ArticleID $articleID. Error: " . $conn->error);
        }
    }
    $stmtCheck->close();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/1.css">
    <link rel="stylesheet" href="../css/nav3.css">
    <link rel="stylesheet" href="../css/article.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* 基礎樣式重置 */
        * {
            box-sizing: border-box;
        }

        /* 背景和主體樣式 */
        body {
            background: linear-gradient(135deg, #f0f4f1 0%, #dfeeea 50%, #cde2d6 100%);
            min-height: 100vh;
            font-family: 'Noto Sans TC', 'Microsoft JhengHei', Arial, sans-serif;
            color: #4a4a4a;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.05"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.03"/><circle cx="50" cy="10" r="0.5" fill="%23ffffff" opacity="0.08"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
            z-index: 0;
        }

        /* 內容區域調整 */
        .content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            margin-left: auto;
            margin-right: auto;
        }

        /* 文章標題區域 */
        .article-header {
            background: linear-gradient(135deg,rgb(149, 202, 225) 0%,rgb(227, 155, 189) 50%,rgb(145, 210, 156) 100%);
            padding: 60px 40px 40px 40px;
            border-radius: 32px;
            box-shadow:
                0 20px 60px rgba(5, 150, 105, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            margin-bottom: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            animation: float 6s ease-in-out infinite;
        }

        .article-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 3s linear infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            33% {
                transform: translateY(-10px) rotate(0.5deg);
            }

            66% {
                transform: translateY(5px) rotate(-0.5deg);
            }
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        .article-title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 700;
            color: #fff;
            margin-bottom: 20px;
            letter-spacing: 2px;
            text-align: center;
            position: relative;
            z-index: 2;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            }

            to {
                text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3), 0 0 30px rgba(255, 255, 255, 0.3);
            }
        }

        .article-description {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.95);
            margin: 0 auto 20px auto;
            max-width: 700px;
            line-height: 1.8;
            text-align: center;
            position: relative;
            z-index: 2;
            font-weight: 300;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* AI 分析區域 */
        .article-ai-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 28px;
            box-shadow:
                0 25px 80px rgba(0, 0, 0, 0.12),
                0 0 0 1px rgba(255, 255, 255, 0.8);
            padding: 40px 36px 32px 36px;
            margin: 0 auto 40px auto;
            max-width: 900px;
            border: none;
            text-align: left;
            position: relative;
            backdrop-filter: blur(20px);
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .article-ai-section:hover {
            transform: translateY(-8px);
            box-shadow:
                0 35px 100px rgba(0, 0, 0, 0.18),
                0 0 0 1px rgba(255, 255, 255, 0.9);
        }

        .ai-title {
            background: linear-gradient(135deg, #065f46 0%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 24px;
            letter-spacing: 1px;
            position: relative;
        }

        .ai-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, #065f46 0%, #059669 100%);
            border-radius: 2px;
        }

        .ai-analysis {
            font-size: 1.1rem;
            color: #4a5568;
            line-height: 2;
            padding: 24px 28px;
            border-left: 4px solid;
            border-image: linear-gradient(135deg, #059669 0%, #10b981 100%) 1;
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.08) 0%, rgba(16, 185, 129, 0.08) 100%);
            border-radius: 0 16px 16px 0;
            margin-bottom: 20px;
            position: relative;
            transition: all 0.3s ease;
        }

        .ai-analysis:hover {
            transform: translateX(8px);
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.12) 0%, rgba(16, 185, 129, 0.12) 100%);
        }

        /* 文章來源區域 */
        .article-source {
            background: linear-gradient(90deg, #6aafc7, #a0d8ef);
            padding: 28px 36px;
            border-radius: 24px;
            border-left: 6px solid rgb(169, 227, 205); /* 修正：加上空格 */
            color: #1f2937; /* 柔和深灰，較好閱讀 */
            margin: 0 auto 40px auto;
            max-width: 900px;
            box-shadow:
                0 20px 60px rgba(0, 150, 136, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.15);
            font-size: 16px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
        }

        .article-source::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .article-source:hover::before {
            left: 100%;
        }

        .article-source:hover {
            transform: translateY(-4px);
            box-shadow:
                0 30px 80px rgba(129, 155, 239, 0.35),
                0 0 0 1px rgba(255, 255, 255, 0.2);
        }

        .article-source a {
            color: #5a7be0; /* 淺藍紫，較柔和 */
            text-decoration: none;
            font-weight: 500;
            position: relative;
            transition: all 0.3s ease;
        }

        .article-source a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #a7f3d0; /* 舒服的薄荷綠底線 */
            transition: width 0.3s ease;
        }

        .article-source a:hover::after {
            width: 100%;
        }

        .article-source a:hover {
            color: #6ee7b7;
        }

        .article-source a:hover::after {
            width: 100%;
        }

        /* 裝飾性元素 */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float-random 8s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            top: 20%;
            left: 10%;
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #059669, #10b981);
            border-radius: 50%;
            animation-delay: -2s;
        }

        .shape:nth-child(2) {
            top: 60%;
            right: 15%;
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #10b981, #34d399);
            border-radius: 30%;
            animation-delay: -4s;
        }

        .shape:nth-child(3) {
            bottom: 30%;
            left: 20%;
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #065f46, #047857);
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            animation-delay: -6s;
        }

        @keyframes float-random {

            0%,
            100% {
                transform: translateY(0px) translateX(0px) rotate(0deg);
            }

            25% {
                transform: translateY(-20px) translateX(10px) rotate(90deg);
            }

            50% {
                transform: translateY(10px) translateX(-5px) rotate(180deg);
            }

            75% {
                transform: translateY(-5px) translateX(15px) rotate(270deg);
            }
        }

        /* 側邊欄樣式調整 */
        .sidebar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-links a {
            transition: all 0.3s ease;
            border-radius: 12px;
        }

        .sidebar-links a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        /* 響應式設計 */
        @media (max-width: 768px) {
            .content {
                padding: 15px;
                margin-left: 0;
            }

            .article-header {
                padding: 40px 24px 30px 24px;
                margin-bottom: 30px;
            }

            .article-ai-section,
            .article-source {
                padding: 24px 20px;
                max-width: 100%;
                margin-bottom: 30px;
            }

            .article-title {
                font-size: 2rem;
                margin-bottom: 16px;
            }

            .article-description,
            .ai-analysis {
                font-size: 1.05rem;
                line-height: 1.7;
            }

            .ai-title {
                font-size: 1.3rem;
            }
        }

        /* 滾動動畫 */
        .scroll-reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.23, 1, 0.32, 1);
        }

        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        /* 沒有內容時的樣式 */
        .ai-analysis[style*="color:#888;"] {
            color: #9ca3af !important;
            font-style: italic;
            background: linear-gradient(135deg, rgba(156, 163, 175, 0.08) 0%, rgba(156, 163, 175, 0.08) 100%);
        }

        /* 文章作者區塊樣式 */
        .article-author {
            padding: 0;
            margin: 0;
            position: relative;
            display: flex;
            align-items: center;
            background: none;
            border: none;
            box-shadow: none;
        }

        .author-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #6aafc7;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            overflow: hidden;
        }

        .author-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .author-icon {
            font-size: 0.9rem;
            color: white;
        }

        .author-info {
            display: flex;
            flex-direction: column;
        }

        .author-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0;
        }

        .author-name {
            font-weight: 500;
            color: #3c6478;
            font-size: 0.9rem;
        }
        
        .author-id {
            font-size: 0.8rem;
            color: #6c757d;
            margin-left: 5px;
        }

        /* AI分析區塊中的作者位置 */
        .ai-section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .ai-title-container {
            flex-grow: 1;
        }
    </style>
</head>

<body>
    <!-- 裝飾性浮動形狀 -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <header>
        <?php include "nav.php"; ?>
    </header>

    <!-- 側邊選單 -->
    <nav class="sidebar">
        <ul class="sidebar-links">
            <?php if (isset($_SESSION['login_session']) && $_SESSION['login_session'] === true): ?>
                <?php if ($_SESSION['role'] === 'Teacher'): ?>
                    <?php
                    $articleID = intval($_GET['id']);
                    $currentUserID = $_SESSION['user_id'];
                    $isAuthor = false;

                    $authorStmt = $conn->prepare("SELECT UserID FROM article WHERE ArticleID = ?");
                    $authorStmt->bind_param("i", $articleID);
                    $authorStmt->execute();
                    $authorResult = $authorStmt->get_result();

                    if ($authorRow = $authorResult->fetch_assoc()) {
                        $isAuthor = ($authorRow['UserID'] == $currentUserID);
                    }
                    $authorStmt->close();
                    ?>
                    <li><a href="deepseek-test.php?article_id=<?= $articleID; ?>"><img src="../img/new-quiz.svg" alt="新增測驗" title="新增測驗"></a></li>
                    <?php if ($isAuthor): ?>
                        <li><a href="ai_summary_editor.php?id=<?= $articleID; ?>"><img src="../img/edit.svg" alt="編輯文章" title="編輯文章"></a></li>
                    <?php endif; ?>
                    <li><a href="luntan.php?article_id=<?= $articleID; ?>"><img src="../img/share.svg" alt="貼文區" title="貼文區"></a></li>
                <?php elseif ($_SESSION['role'] === 'Admin'): ?>
                    <?php
                    $articleID = intval($_GET['id']);
                    ?>
                    <li><a href="deepseek-test.php?article_id=<?= $articleID; ?>"><img src="../img/new-quiz.svg" alt="新增測驗" title="新增測驗"></a></li>
                    <li><a href="quiz.php?article_id=<?= $articleID; ?>"><img src="../img/quiz.svg" alt="測驗" title="測驗"></a></li>
                    <li><a href="ai_summary_editor.php?id=<?= $articleID; ?>"><img src="../img/edit.svg" alt="編輯文章" title="編輯文章"></a></li>
                    <li><a href="luntan.php?article_id=<?= $articleID; ?>"><img src="../img/share.svg" alt="貼文區" title="貼文區"></a></li>
                <?php elseif ($_SESSION['role'] === 'Student'): ?>
                    <li><a href="quiz.php?article_id=<?= $_GET['id']; ?>"><img src="../img/quiz.svg" alt="測驗" title="測驗"></a></li>
                    <li><a href="luntan.php?article_id=<?= $_GET['id']; ?>"><img src="../img/share.svg" alt="貼文區" title="貼文區"></a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- 文章內容 -->
    <div class="content">
        <?php
        require_once 'article_analyzer.php';

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT a.Title, a.Description, a.teacher_summary, a.ArticleURL, a.UserID, u.Username, u.AvatarURL 
                                   FROM article a 
                                   LEFT JOIN user u ON a.UserID = u.UserID 
                                   WHERE a.ArticleID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                echo '<div class="article-header">';
                echo '<h1 class="article-title">' . htmlspecialchars($row['Title']) . '</h1>';
                echo '<p class="article-description">' . nl2br(htmlspecialchars($row['Description'])) . '</p>';
                echo '</div>';

                echo '<div class="article-source scroll-reveal"><span style="color:white;">📖 文章來源：</span><a href="' . htmlspecialchars($row['ArticleURL']) . '" target="_blank">' . htmlspecialchars($row['ArticleURL']) . '</a></div>';

                if (!empty($row['teacher_summary'])) {
                    echo '<div class="article-ai-section scroll-reveal">';
                    echo '<div class="ai-section-header">';
                    echo '<div class="ai-title-container">';
                    echo '<h3 class="ai-title">🤖 AI重點整理</h3>';
                    echo '</div>';
                    echo '<div class="article-author">';
                    
                    // 檢查用戶是否有頭像
                    if (!empty($row['AvatarURL'])) {
                        echo '<div class="author-avatar"><img src="../' . htmlspecialchars($row['AvatarURL']) . '" alt="作者頭像" class="author-img"></div>';
                    } else {
                        echo '<div class="author-avatar"><span class="author-icon">👤</span></div>';
                    }
                    
                    echo '<div class="author-info">';
                    echo '<span class="author-label">文章發布者</span>';
                    echo '<span class="author-name">';
                    if (!empty($row['Username'])) {
                        echo htmlspecialchars($row['Username']) . ' <span class="author-id">ID: ' . $row['UserID'] . '</span>';
                    } else {
                        echo '未知用戶 <span class="author-id">ID: ' . $row['UserID'] . '</span>';
                    }
                    echo '</span>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="ai-analysis">' . nl2br(htmlspecialchars($row['teacher_summary'])) . '</div>';
                    echo '</div>';
                } else {
                    echo '<div class="article-ai-section scroll-reveal">';
                    echo '<div class="ai-section-header">';
                    echo '<div class="ai-title-container">';
                    echo '<h3 class="ai-title">🤖 重點整理</h3>';
                    echo '</div>';
                    echo '<div class="article-author">';
                    
                    // 檢查用戶是否有頭像
                    if (!empty($row['AvatarURL'])) {
                        echo '<div class="author-avatar"><img src="../' . htmlspecialchars($row['AvatarURL']) . '" alt="作者頭像" class="author-img"></div>';
                    } else {
                        echo '<div class="author-avatar"><span class="author-icon">👤</span></div>';
                    }
                    
                    echo '<div class="author-info">';
                    echo '<span class="author-label">文章發布者</span>';
                    echo '<span class="author-name">';
                    if (!empty($row['Username'])) {
                        echo htmlspecialchars($row['Username']) . ' <span class="author-id">ID: ' . $row['UserID'] . '</span>';
                    } else {
                        echo '未知用戶 <span class="author-id">ID: ' . $row['UserID'] . '</span>';
                    }
                    echo '</span>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="ai-analysis" style="color:#888;">尚未有重點整理</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="article-ai-section">';
                echo '<p style="text-align: center; color: #666;">找不到該文章</p>';
                echo '</div>';
            }
            $stmt->close();
        } else {
            echo '<div class="article-ai-section">';
            echo '<p style="text-align: center; color: #666;">請選擇要查看的文章</p>';
            echo '</div>';
        }

        $conn->close();
        ?>
    </div>

    <script>
        // 滾動顯示動畫
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.scroll-reveal').forEach(el => {
            observer.observe(el);
        });

        // 滑鼠移動視差效果
        document.addEventListener('mousemove', (e) => {
            const shapes = document.querySelectorAll('.shape');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;

            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 0.02;
                const xOffset = (x - 0.5) * 50 * speed;
                const yOffset = (y - 0.5) * 50 * speed;
                shape.style.transform += ` translate(${xOffset}px, ${yOffset}px)`;
            });
        });

        // 頁面載入動畫
        window.addEventListener('load', () => {
            document.body.style.opacity = '1';
        });
    </script>
</body>

</html>