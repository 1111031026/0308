<?php
// 確保啟動 session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 驗證用戶是否登入且是老師
if (!isset($_SESSION['login_session']) || $_SESSION['login_session'] !== true || $_SESSION['role'] !== 'Teacher') {
    header("Location: index.php");
    exit();
}

// 資料庫連接
require_once 'db_connect.php';

// 獲取文章ID
$article_id = isset($_GET['article_id']) ? $_GET['article_id'] : null;
if (!$article_id) {
    header("Location: teacher_articles.php");
    exit();
}

// 檢查文章是否屬於當前老師
$user_id = $_SESSION['user_id'] ?? null;
$check_sql = "SELECT * FROM article WHERE ArticleID = ? AND UserID = ?";
$check_stmt = $conn->prepare($check_sql);
if ($check_stmt) {
    $check_stmt->bind_param("ss", $article_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        // 文章不存在或不屬於當前老師
        header("Location: teacher_articles.php");
        exit();
    }
    
    $article = $check_result->fetch_assoc();
    $check_stmt->close();
} else {
    header("Location: teacher_articles.php");
    exit();
}

// 處理刪除題目請求
if (isset($_GET['delete_question']) && !empty($_GET['delete_question'])) {
    $question_id = $_GET['delete_question'];
    $question_type = $_GET['question_type'] ?? '';
    
    if (!empty($question_type)) {
        switch ($question_type) {
            case 'choice':
                $table = 'choicequiz';
                $id_field = 'choiceID';
                break;
            case 'fill':
                $table = 'fillquiz';
                $id_field = 'fillID';
                break;
            case 'tf':
                $table = 'tfquiz';
                $id_field = 'tfID';
                break;
            default:
                $table = '';
                $id_field = '';
        }
        
        if (!empty($table) && !empty($id_field)) {
            $delete_sql = "DELETE FROM $table WHERE $id_field = ? AND UserID = ? AND ArticleID = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            if ($delete_stmt) {
                $delete_stmt->bind_param("sss", $question_id, $user_id, $article_id);
                $delete_stmt->execute();
                $delete_stmt->close();
                
                // 重定向以避免重複刪除
                header("Location: article_questions.php?article_id=$article_id&deleted=1");
                exit();
            }
        }
    }
}

// 篩選條件
$question_type = isset($_GET['type']) ? $_GET['type'] : 'all';

// 獲取選擇題
$choice_questions = [];
if ($question_type == 'all' || $question_type == 'choice') {
    $choice_sql = "SELECT * FROM choicequiz WHERE ArticleID = ? AND UserID = ?";
    $choice_stmt = $conn->prepare($choice_sql);
    if ($choice_stmt) {
        $choice_stmt->bind_param("ss", $article_id, $user_id);
        $choice_stmt->execute();
        $choice_result = $choice_stmt->get_result();
        while ($row = $choice_result->fetch_assoc()) {
            $row['type'] = 'choice';
            $choice_questions[] = $row;
        }
        $choice_stmt->close();
    }
}

// 獲取填空題
$fill_questions = [];
if ($question_type == 'all' || $question_type == 'fill') {
    $fill_sql = "SELECT * FROM fillquiz WHERE ArticleID = ? AND UserID = ?";
    $fill_stmt = $conn->prepare($fill_sql);
    if ($fill_stmt) {
        $fill_stmt->bind_param("ss", $article_id, $user_id);
        $fill_stmt->execute();
        $fill_result = $fill_stmt->get_result();
        while ($row = $fill_result->fetch_assoc()) {
            $row['type'] = 'fill';
            $fill_questions[] = $row;
        }
        $fill_stmt->close();
    }
}

// 獲取是非題
$tf_questions = [];
if ($question_type == 'all' || $question_type == 'tf') {
    $tf_sql = "SELECT * FROM tfquiz WHERE ArticleID = ? AND UserID = ?";
    $tf_stmt = $conn->prepare($tf_sql);
    if ($tf_stmt) {
        $tf_stmt->bind_param("ss", $article_id, $user_id);
        $tf_stmt->execute();
        $tf_result = $tf_stmt->get_result();
        while ($row = $tf_result->fetch_assoc()) {
            $row['type'] = 'tf';
            $tf_questions[] = $row;
        }
        $tf_stmt->close();
    }
}

// 合併所有題目
$all_questions = array_merge($choice_questions, $fill_questions, $tf_questions);

// 使用 PHP 手動排序（如果需要，可以根據任何存在的時間欄位排序）
// 先嘗試尋找可能的時間欄位
$time_field = '';
if (!empty($all_questions)) {
    $first_question = reset($all_questions);
    foreach (['created_at', 'CreatedAt', 'createdAt', 'CreateTime', 'createTime', 'CreateDate', 'createDate'] as $field) {
        if (isset($first_question[$field])) {
            $time_field = $field;
            break;
        }
    }
}

// 按時間排序（如果找到時間欄位）
if (!empty($time_field)) {
    usort($all_questions, function($a, $b) use ($time_field) {
        return strtotime($b[$time_field] ?? 0) - strtotime($a[$time_field] ?? 0);
    });
} else {
    // 如果沒有時間欄位，可以按照其他欄位排序或保持原順序
    // 例如，可以按照題目內容、ID等排序
    usort($all_questions, function($a, $b) {
        // 這裡預設按 ID 排序（假設每種題目都有某種 ID 欄位）
        $a_id = 0;
        $b_id = 0;
        
        // 從 $a 中查找 ID
        if ($a['type'] === 'choice') {
            foreach (['ChoiceQuizID', 'QuizID', 'ID', 'Id', 'id'] as $field) {
                if (isset($a[$field])) {
                    $a_id = $a[$field];
                    break;
                }
            }
        } elseif ($a['type'] === 'fill') {
            foreach (['FillQuizID', 'QuizID', 'ID', 'Id', 'id'] as $field) {
                if (isset($a[$field])) {
                    $a_id = $a[$field];
                    break;
                }
            }
        } elseif ($a['type'] === 'tf') {
            foreach (['TFQuizID', 'QuizID', 'ID', 'Id', 'id'] as $field) {
                if (isset($a[$field])) {
                    $a_id = $a[$field];
                    break;
                }
            }
        }
        
        // 從 $b 中查找 ID
        if ($b['type'] === 'choice') {
            foreach (['ChoiceQuizID', 'QuizID', 'ID', 'Id', 'id'] as $field) {
                if (isset($b[$field])) {
                    $b_id = $b[$field];
                    break;
                }
            }
        } elseif ($b['type'] === 'fill') {
            foreach (['FillQuizID', 'QuizID', 'ID', 'Id', 'id'] as $field) {
                if (isset($b[$field])) {
                    $b_id = $b[$field];
                    break;
                }
            }
        } elseif ($b['type'] === 'tf') {
            foreach (['TFQuizID', 'QuizID', 'ID', 'Id', 'id'] as $field) {
                if (isset($b[$field])) {
                    $b_id = $b[$field];
                    break;
                }
            }
        }
        
        // 降序排列（較新的 ID 排在前面）
        return $b_id - $a_id;
    });
}

// 獲取每種題型的數量
$choice_count = count($choice_questions);
$fill_count = count($fill_questions);
$tf_count = count($tf_questions);
$total_count = count($all_questions);

// 處理分頁
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$total_pages = ceil($total_count / $per_page);
$page = max(1, min($page, $total_pages));
$start = ($page - 1) * $per_page;

// 當前頁面的題目
$current_questions = array_slice($all_questions, $start, $per_page);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章題目管理 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav3.css">
    <link rel="stylesheet" href="../css/article_questions.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <main class="container">
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success">
                題目已成功刪除
            </div>
        <?php endif; ?>

        <div class="page-header">
            <h1>文章題目管理</h1>
            <p>查看並編輯您為本文章創建的所有測驗題目，包括選擇題、填空題和是非題</p>
        </div>

        <div class="article-info">
            <?php 
            $category_class = '';
            switch (strtolower($article['Category'] ?? '')) {
                case 'climate':
                case '氣候永續':
                    $category_class = 'climate';
                    break;
                case 'ocean':
                case '海洋永續':
                    $category_class = 'ocean';
                    break;
                case 'land':
                case 'landscape':
                case '陸域永續':
                    $category_class = 'land';
                    break;
            }
            ?>
            <span class="category <?php echo $category_class; ?>"><?php echo htmlspecialchars($article['Category'] ?? '未分類'); ?></span>
            <span class="date">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <?php 
                // 檢查是否有日期欄位
                $article_date_value = '';
                foreach (['created_at', 'CreatedAt', 'createdAt', 'CreateTime', 'createTime', 'CreateDate', 'createDate'] as $field) {
                    if (isset($article[$field])) {
                        $article_date_value = $article[$field];
                        break;
                    }
                }
                
                if (!empty($article_date_value)) {
                    echo date('Y-m-d', strtotime($article_date_value));
                } else {
                    echo '未知日期';
                }
                ?>
            </span>
            <h2><?php echo htmlspecialchars($article['Title'] ?? '無標題'); ?></h2>
            <div class="article-description">
                <?php 
                if (!empty($article['Description'])) {
                    echo htmlspecialchars($article['Description']);
                } else {
                    echo htmlspecialchars(mb_substr(strip_tags($article['Content'] ?? ''), 0, 200, 'UTF-8')) . '...';
                }
                ?>
            </div>
        </div>

        <div class="questions-controls">
            <div class="questions-tabs">
                <a href="?article_id=<?php echo $article_id; ?>&type=all" class="tab <?php echo $question_type == 'all' ? 'active' : ''; ?>">
                    全部 (<?php echo $total_count; ?>)
                </a>
                <a href="?article_id=<?php echo $article_id; ?>&type=choice" class="tab <?php echo $question_type == 'choice' ? 'active' : ''; ?>">
                    選擇題 (<?php echo $choice_count; ?>)
                </a>
                <a href="?article_id=<?php echo $article_id; ?>&type=fill" class="tab <?php echo $question_type == 'fill' ? 'active' : ''; ?>">
                    填空題 (<?php echo $fill_count; ?>)
                </a>
                <a href="?article_id=<?php echo $article_id; ?>&type=tf" class="tab <?php echo $question_type == 'tf' ? 'active' : ''; ?>">
                    是非題 (<?php echo $tf_count; ?>)
                </a>
            </div>

            <div class="question-actions">
                <a href="deepseek-test.php?article_id=<?php echo $article_id; ?>" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    新增題目
                </a>
                <a href="article.php?id=<?php echo $article_id; ?>" class="btn btn-secondary">
                    查看文章
                </a>
            </div>
        </div>

        <div class="questions-list">
            <?php if (empty($current_questions)): ?>
                <div class="no-questions">
                    <h3>目前沒有題目</h3>
                    <p>您尚未為此文章創建任何測驗題目。點擊「新增題目」按鈕開始創建您的第一個題目。</p>
                    <a href="deepseek-test.php?article_id=<?php echo $article_id; ?>" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        新增題目
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($current_questions as $question): ?>
                    <div class="question-card">
                        <?php
                        // 根據題型顯示不同的標籤和內容
                        switch ($question['type']) {
                            case 'choice':
                                echo '<span class="question-type choice">選擇題</span>';
                                $options = [
                                    'A' => $question['OptionA'] ?? '',
                                    'B' => $question['OptionB'] ?? '',
                                    'C' => $question['OptionC'] ?? '',
                                    'D' => $question['OptionD'] ?? ''
                                ];
                                $correct_answer = $question['CorrectAnswer'] ?? '';
                                
                                // 使用正確的ID欄位
                                $question_id = $question['choiceID'] ?? '';
                                
                                $edit_url = "edit_choice_question.php?id={$question_id}&article_id={$article_id}";
                                $delete_url = "?article_id={$article_id}&delete_question={$question_id}&question_type=choice";
                                break;
                            case 'fill':
                                echo '<span class="question-type fill">填空題</span>';
                                $options = [];
                                $correct_answer = $question['CorrectAnswer'] ?? '';
                                
                                // 使用正確的ID欄位
                                $question_id = $question['fillID'] ?? '';
                                
                                $edit_url = "edit_fill_question.php?id={$question_id}&article_id={$article_id}";
                                $delete_url = "?article_id={$article_id}&delete_question={$question_id}&question_type=fill";
                                break;
                            case 'tf':
                                echo '<span class="question-type tf">是非題</span>';
                                $options = [
                                    'T' => '正確',
                                    'F' => '錯誤'
                                ];
                                $correct_answer = $question['CorrectAnswer'] ? 'T' : 'F';
                                
                                // 使用正確的ID欄位
                                $question_id = $question['tfID'] ?? '';
                                
                                $edit_url = "edit_tf_question.php?id={$question_id}&article_id={$article_id}";
                                $delete_url = "?article_id={$article_id}&delete_question={$question_id}&question_type=tf";
                                break;
                        }
                        ?>
                        
                        <div class="question-content">
                            <?php echo htmlspecialchars($question['QuestionText'] ?? ''); ?>
                        </div>
                        
                        <?php if (!empty($options)): ?>
                            <div class="question-options">
                                <?php foreach ($options as $key => $value): ?>
                                    <?php if (!empty($value)): ?>
                                        <div class="question-option">
                                            <span class="option-marker <?php echo $key === $correct_answer ? 'correct' : ''; ?>">
                                                <?php echo $key; ?>
                                            </span>
                                            <span class="option-text">
                                                <?php echo htmlspecialchars($value); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="question-answer">
                                <strong>正確答案：</strong> <?php echo htmlspecialchars($correct_answer); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="question-footer">
                            <div class="question-meta">
                                <?php if (isset($question['Difficulty']) && !empty($question['Difficulty'])): ?>
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 20V10"></path>
                                            <path d="M12 20V4"></path>
                                            <path d="M6 20v-6"></path>
                                        </svg>
                                        難度：<?php echo htmlspecialchars($question['Difficulty']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="question-actions-footer">
                                <a href="deepseek-test.php?article_id=<?php echo $article_id; ?>&edit_id=<?php echo $question_id; ?>&question=<?php echo htmlspecialchars(urlencode($question['QuestionText'] ?? '')); ?>&type=<?php echo $question['type']; ?>&answer=<?php 
                                    // 根據題目類型選擇正確的答案欄位
                                    if ($question['type'] === 'choice' || $question['type'] === 'fill') {
                                        echo htmlspecialchars(urlencode($question['CorrectAnswer'] ?? ''));
                                    } else if ($question['type'] === 'tf') {
                                        echo htmlspecialchars(urlencode($question['CorrectAnswer'] ? 'T' : 'F'));
                                    }
                                ?><?php 
                                    // 如果是選擇題，添加選項參數
                                    if ($question['type'] === 'choice') {
                                        echo '&option_a=' . htmlspecialchars(urlencode($options['A'] ?? ''));
                                        echo '&option_b=' . htmlspecialchars(urlencode($options['B'] ?? ''));
                                        echo '&option_c=' . htmlspecialchars(urlencode($options['C'] ?? ''));
                                        echo '&option_d=' . htmlspecialchars(urlencode($options['D'] ?? ''));
                                    }
                                ?>" class="action-link edit" title="編輯題目">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>
                                <a href="<?php echo $delete_url; ?>" class="action-link delete" title="刪除題目" onclick="return confirm('確定要刪除這個題目嗎？');">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <a href="?article_id=<?php echo $article_id; ?>&type=<?php echo $question_type; ?>&page=<?php echo max(1, $page - 1); ?>" class="pagination-btn prev <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    上一頁
                </a>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?article_id=<?php echo $article_id; ?>&type=<?php echo $question_type; ?>&page=<?php echo $i; ?>" class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <a href="?article_id=<?php echo $article_id; ?>&type=<?php echo $question_type; ?>&page=<?php echo min($total_pages, $page + 1); ?>" class="pagination-btn next <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    下一頁
                </a>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // 動態加載問題選項
        document.addEventListener('DOMContentLoaded', function() {
            // 可以在這裡添加任何需要的JavaScript功能
        });
    </script>
</body>
</html> 