<?php
require_once 'db_connect.php';

// 獲取文章ID
$article_id = isset($_GET['article_id']) ? intval($_GET['article_id']) : 0;

// 獲取選擇題
$choice_questions = [];
$choice_sql = "SELECT c.*, u.Username FROM choicequiz c LEFT JOIN user u ON c.UserID = u.UserID WHERE c.ArticleID = ?";
$stmt = $conn->prepare($choice_sql);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()) {
    $choice_questions[] = $row;
}

// 獲取填空題
$fill_questions = [];
$fill_sql = "SELECT f.*, u.Username FROM fillquiz f LEFT JOIN user u ON f.UserID = u.UserID WHERE f.ArticleID = ?";
$stmt = $conn->prepare($fill_sql);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()) {
    $fill_questions[] = $row;
}

// 獲取是非題
$tf_questions = [];
$tf_sql = "SELECT t.*, u.Username FROM tfquiz t LEFT JOIN user u ON t.UserID = u.UserID WHERE t.ArticleID = ?";
$stmt = $conn->prepare($tf_sql);
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()) {
    $tf_questions[] = $row;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章測驗 - 永續小站</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/quiz_new.css">
    <link rel="stylesheet" href="../css/nav.css">
</head>
<body>
    <header>
        
<nav class="navbar">
    <div class="logo">
        <img src="../img/icon.png" style="color: white;" alt="永續小站 Logo">
        <h1 style="font-size: 24px;"><a href="index.php" class="logo-title">永續小站</a></h1>
    </div>
    <ul class="nav-links">
        <li><a href="climate.php" style="font-size: 16px;">氣候永續</a></li>
        <li><a href="landscape.php" style="font-size: 16px; ">陸域永續</a></li>
        <li><a href="ocean.php" style="font-size: 16px;">海洋永續</a></li>
                                    <li><a href="#land">商城</a></li>
                        </ul>
    <div class="nav-icons">
        <a href="#"><img src="../img/achv.png" alt="成就"></a>
        <a href="user.php"><img src="../img/user.png" alt="用戶"></a>
    </div>
</nav>    </header>

    <div class="quiz-container">
        <h1 class="quiz-type-title">測驗題目</h1>
        <div class="accordion" id="quizAccordion">
            <!-- 選擇題區塊 -->
                        <div class="accordion-item">
                <h2 class="accordion-header" id="choiceHeading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#choiceCollapse" aria-expanded="false">
                        選擇題
                    </button>
                </h2>
                <div id="choiceCollapse" class="accordion-collapse collapse" data-bs-parent="#quizAccordion" style="">
                    <div class="accordion-body">
                        <?php foreach($choice_questions as $question): ?>
                        <div class="question-container" data-question-id="<?php echo htmlspecialchars($question['choiceID']); ?>" data-question-type="choice">
                            <div class="question-text"><?php echo htmlspecialchars($question['QuestionText']); ?></div>
                            <div class="options-container">
                                <div class="option-item">
                                    <input type="radio" name="choice_<?php echo $question['choiceID']; ?>" value="A"> 
                                    <label><?php echo htmlspecialchars($question['OptionA']); ?></label>
                                </div>
                                <div class="option-item">
                                    <input type="radio" name="choice_<?php echo $question['choiceID']; ?>" value="B"> 
                                    <label><?php echo htmlspecialchars($question['OptionB']); ?></label>
                                </div>
                                <div class="option-item">
                                    <input type="radio" name="choice_<?php echo $question['choiceID']; ?>" value="C"> 
                                    <label><?php echo htmlspecialchars($question['OptionC']); ?></label>
                                </div>
                                <div class="option-item">
                                    <input type="radio" name="choice_<?php echo $question['choiceID']; ?>" value="D"> 
                                    <label><?php echo htmlspecialchars($question['OptionD']); ?></label>
                                </div>
                            </div>
                            <button class="submit-btn" onclick="submitAnswer(this)">提交答案</button>
                            <div class="feedback"></div>
                            <div class="teacher-info">出題老師：<?php echo htmlspecialchars($question['Username'] ?? '未知'); ?></div>
                        </div>
                        <?php endforeach; ?>
                                            </div>
                </div>
            </div>
            
            <!-- 填空題區塊 -->
                        <div class="accordion-item">
                <h2 class="accordion-header" id="fillHeading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#fillCollapse" aria-expanded="false">
                        填空題
                    </button>
                </h2>
                <div id="fillCollapse" class="accordion-collapse collapse" data-bs-parent="#quizAccordion" style="">
                    <div class="accordion-body">
                        <?php foreach($fill_questions as $question): ?>
                        <div class="question-container" data-question-id="<?php echo htmlspecialchars($question['fillID']); ?>" data-question-type="fill">
                            <div class="question-text"><?php echo htmlspecialchars($question['QuestionText']); ?></div>
                            <input type="text" class="fill-input" placeholder="請輸入答案">
                            <button class="submit-btn" onclick="submitAnswer(this)">提交答案</button>
                            <div class="feedback"></div>
                            <div class="teacher-info">出題老師：<?php echo htmlspecialchars($question['Username'] ?? '未知'); ?></div>
                        </div>
                        <?php endforeach; ?>
                                            </div>
                </div>
            </div>
            
            <!-- 是非題區塊 -->
                        <div class="accordion-item">
                <h2 class="accordion-header" id="tfHeading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tfCollapse" aria-expanded="false">
                        是非題
                    </button>
                </h2>
                <div id="tfCollapse" class="accordion-collapse collapse" data-bs-parent="#quizAccordion" style="">
                    <div class="accordion-body">
                        <?php foreach($tf_questions as $question): ?>
                        <div class="question-container" data-question-id="<?php echo htmlspecialchars($question['tfID']); ?>" data-question-type="tf">
                            <div class="question-text"><?php echo htmlspecialchars($question['QuestionText']); ?></div>
                            <div class="options-container">
                                <div class="option-item">
                                    <input type="radio" name="tf_<?php echo $question['tfID']; ?>" value="T"> 
                                    <label>是</label>
                                </div>
                                <div class="option-item">
                                    <input type="radio" name="tf_<?php echo $question['tfID']; ?>" value="F"> 
                                    <label>否</label>
                                </div>
                            </div>
                            <button class="submit-btn" onclick="submitAnswer(this)">提交答案</button>
                            <div class="feedback"></div>
                            <div class="teacher-info">出題老師：<?php echo htmlspecialchars($question['Username'] ?? '未知'); ?></div>
                        </div>
                        <?php endforeach; ?>
                                            </div>
                </div>
            </div>
                    </div>

        
            </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function submitAnswer(button) {
        const questionContainer = button.closest('.question-container');
        const questionId = questionContainer.dataset.questionId;
        const questionType = questionContainer.dataset.questionType;
        const feedbackDiv = questionContainer.querySelector('.feedback');
        let answer = '';

        // 根據題目類型獲取答案
        if (questionType === 'choice') {
            const selectedOption = questionContainer.querySelector('input[type="radio"]:checked');
            if (!selectedOption) {
                alert('請選擇一個答案');
                return;
            }
            answer = selectedOption.value;
        } else if (questionType === 'fill') {
            const input = questionContainer.querySelector('.fill-input');
            if (!input.value.trim()) {
                alert('請輸入答案');
                return;
            }
            answer = input.value.trim();
        } else if (questionType === 'tf') {
            const selectedOption = questionContainer.querySelector('input[type="radio"]:checked');
            if (!selectedOption) {
                alert('請選擇一個答案');
                return;
            }
            answer = selectedOption.value;
        }

        // 發送答案到伺服器進行驗證
        fetch('check_answer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                questionId: questionId,
                questionType: questionType,
                answer: answer
            })
        })
        .then(response => response.json())
        .then(data => {
            feedbackDiv.style.display = 'block';
            if (data.correct) {
                feedbackDiv.className = 'feedback correct';
                feedbackDiv.textContent = '答對了！' + (data.explanation ? ' 解釋：' + data.explanation : '');
            } else {
                feedbackDiv.className = 'feedback incorrect';
                feedbackDiv.textContent = '答錯了。' + (data.explanation ? ' 正確答案：' + data.explanation : '');
            }
            // 禁用提交按鈕和輸入
            button.disabled = true;
            const inputs = questionContainer.querySelectorAll('input');
            inputs.forEach(input => input.disabled = true);
        })
        .catch(error => {
            console.error('Error:', error);
            feedbackDiv.style.display = 'block';
            feedbackDiv.className = 'feedback incorrect';
            feedbackDiv.textContent = '提交答案時發生錯誤，請稍後再試。';
        });
    }
    </script>

</body></html>