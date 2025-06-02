<?php
session_start();
// 初始化變數
$edit_content = '';
$edit_type = '';
$edit_answer = '';
$edit_option_a = '';
$edit_option_b = '';
$edit_option_c = '';
$edit_option_d = '';
$edit_correct_answer = false;

// 從 URL 參數獲取題目內容 (從 article_questions.php 跳轉過來)
if (isset($_GET['question']) && isset($_GET['type'])) {
    $edit_content = urldecode($_GET['question']);
    $edit_answer = urldecode($_GET['answer'] ?? '');
    $edit_id = isset($_GET['edit_id']) ? $_GET['edit_id'] : null;
    
    // 根據題目類型設置對應的變數
    switch ($_GET['type']) {
        case 'choice':
            $edit_type = '選擇題';
            $edit_option_a = urldecode($_GET['option_a'] ?? '');
            $edit_option_b = urldecode($_GET['option_b'] ?? '');
            $edit_option_c = urldecode($_GET['option_c'] ?? '');
            $edit_option_d = urldecode($_GET['option_d'] ?? '');
            break;
        case 'tf':
            $edit_type = '是非題';
            $edit_correct_answer = strtolower($edit_answer) === 't' || strtolower($edit_answer) === 'true';
            $edit_answer = $edit_correct_answer ? 'true' : 'false';
            break;
        case 'fill':
            $edit_type = '問答題';
            break;
        default:
            $edit_type = '問答題';
            break;
    }
}

// 如果是編輯模式，從資料庫獲取題目信息
elseif (isset($_GET['edit_id'])) {
    include 'db_connect.php';
    $edit_id = intval($_GET['edit_id']);
    
    $stmt = $conn->prepare("SELECT * FROM teacher_questions WHERE question_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $edit_content = $row['content'];
        $edit_type = $row['question_type'];
        
        if ($edit_type === '選擇題') {
            $edit_answer = $row['answer'];
            $edit_option_a = $row['option_a'];
            $edit_option_b = $row['option_b'];
            $edit_option_c = $row['option_c'];
            $edit_option_d = $row['option_d'];
        } elseif ($edit_type === '是非題') {
            $edit_correct_answer = $row['correct_answer'];
            $edit_answer = $edit_correct_answer ? 'true' : 'false';
        } else {
            $edit_answer = $row['answer'];
        }
    }
    
    $stmt->close();
    $conn->close();
}

// 設定 API 金鑰
$apiKey = 'sk-9483cae29d5644318c39537d786410f7'; // <<< 換成你的 DeepSeek API Key

$responseText = '';

// 儲存題目到資料庫
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_question'])) {
    $content = $_POST['question_content'] ?? '';
    $answer = $_POST['question_answer'] ?? '';
    $question_type = $_POST['question_type'] ?? '問答題';
    
    // 連接資料庫
    include 'db_connect.php';
    
    // 獲取文章ID
    $article_id = isset($_GET['article_id']) ? intval($_GET['article_id']) : null;

    // 檢查是否為編輯模式
    if (isset($_GET['edit_id'])) {
        $edit_id = intval($_GET['edit_id']);
        
        if ($question_type === '選擇題') {
            $stmt = $conn->prepare("UPDATE teacher_questions SET content = ?, answer = ?, question_type = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ? WHERE question_id = ?");
            $option_a = $_POST['option_a'] ?? '';
            $option_b = $_POST['option_b'] ?? '';
            $option_c = $_POST['option_c'] ?? '';
            $option_d = $_POST['option_d'] ?? '';
            $stmt->bind_param("sssssssi", $content, $answer, $question_type, $option_a, $option_b, $option_c, $option_d, $edit_id);
        } elseif ($question_type === '是非題') {
            $stmt = $conn->prepare("UPDATE teacher_questions SET content = ?, question_type = ?, correct_answer = ? WHERE question_id = ?");
            $correct_answer = $_POST['question_answer'] === '是' ? 1 : 0;
            $stmt->bind_param("ssii", $content, $question_type, $correct_answer, $edit_id);
        } else {
            $stmt = $conn->prepare("UPDATE teacher_questions SET content = ?, answer = ?, question_type = ? WHERE question_id = ?");
            $stmt->bind_param("sssi", $content, $answer, $question_type, $edit_id);
        }
    } else {
        if ($question_type === '選擇題') {
            $stmt = $conn->prepare("INSERT INTO teacher_questions (content, answer, question_type, option_a, option_b, option_c, option_d, article_id, UserID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $option_a = $_POST['option_a'] ?? '';
            $option_b = $_POST['option_b'] ?? '';
            $option_c = $_POST['option_c'] ?? '';
            $option_d = $_POST['option_d'] ?? '';
            $user_id = $_SESSION['user_id'] ?? null;
            $stmt->bind_param("sssssssii", $content, $answer, $question_type, $option_a, $option_b, $option_c, $option_d, $article_id, $user_id);
        } elseif ($question_type === '是非題') {
            $stmt = $conn->prepare("INSERT INTO teacher_questions (content, question_type, correct_answer, article_id, UserID) VALUES (?, ?, ?, ?, ?)");
            $correct_answer = $_POST['question_answer'] === '是' ? 1 : 0;
            $user_id = $_SESSION['user_id'] ?? null;
            $stmt->bind_param("ssiii", $content, $question_type, $correct_answer, $article_id, $user_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO teacher_questions (content, answer, question_type, article_id, UserID) VALUES (?, ?, ?, ?, ?)");
            $user_id = $_SESSION['user_id'] ?? null;
            $stmt->bind_param("sssii", $content, $answer, $question_type, $article_id, $user_id);
        }
    }
    
    // 執行SQL
    if ($stmt->execute()) {
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        } else {
            // 將重定向改為返回文章題目管理頁面
            $redirect_url = 'view-all-qusetion.php?article_id=' . $article_id;
            echo '<script>alert("題目儲存成功！"); window.location.href = "' . $redirect_url . '";</script>';
        }
    } else {
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $conn->error]);
            exit;
        } else {
            echo '<script>alert("儲存失敗: ' . $conn->error . '");</script>';
        }
    }
    
    $stmt->close();
    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 獲取文章URL
    $article_id = isset($_GET['article_id']) ? intval($_GET['article_id']) : null;
    $article_url = '';
    
    if ($article_id) {
        include 'db_connect.php';
        $stmt = $conn->prepare("SELECT ArticleURL FROM article WHERE ArticleID = ?");
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $article_url = $row['ArticleURL'];
        }
        
        $stmt->close();
        $conn->close();
    }
    
    if (isset($_POST['user_input'])) {
        $userInput = $_POST['user_input'];
        
        // 根據選擇的題型添加對應的提示
        $questionType = $_POST['ai_question_type'] ?? 'none';
        
        if ($questionType !== 'none') {
            $promptPrefix = '';
            
            switch ($questionType) {
                case 'choice':
                    $promptPrefix = "請根據文章生成一道適合國中或國小學生程度的選擇題，包含四個選項(A、B、C、D)，並標明正確答案。\n\n請注意以下幾點：\n1. 題目難度要符合國中或國小學生的認知發展階段\n2. 用詞要簡單易懂，避免過於複雜的專業術語\n3. 選項要清晰明確，避免模糊不清的表述\n4. 題目內容要貼近學生的生活經驗\n\n請參考 Bloom 認知領域修訂版本來設計題目，確保題目能評估學生的認知能力層次：\n\n1. 記憶（Remembering）：回想、辨識和重現資訊\n2. 理解（Understanding）：解釋、描述和闡明訊息意義\n3. 應用（Applying）：將概念或技能運用於新情境\n4. 分析（Analyzing）：將資訊細分並分析關係\n5. 評鑑（Evaluating）：進行批判性判斷和評價\n6. 創造（Creating）：整合元素產出新的觀點\n\n請在生成題目後，以以下格式說明題目的認知層次分析：\n\n【認知層次分析】\n- 主要評估層次：[請選擇上述六個層次中的一個或多個]\n- 評估原因：[說明為什麼選擇這些層次]\n- 教學價值：[說明此題目如何幫助學生發展這些認知能力]\n- 適合年級：[說明此題目最適合哪個年級的學生，以及原因]";
                    break;
                case 'fill':
                    $promptPrefix = "請根據文章生成一道適合國中或國小學生程度的問答題，並提供參考答案。\n\n請注意以下幾點：\n1. 題目難度要符合國中或國小學生的認知發展階段\n2. 用詞要簡單易懂，避免過於複雜的專業術語\n3. 問題要具體明確，避免過於抽象或複雜的思考\n4. 題目內容要貼近學生的生活經驗\n\n請參考 Bloom 認知領域修訂版本來設計題目，確保題目能評估學生的認知能力層次：\n\n1. 記憶（Remembering）：回想、辨識和重現資訊\n2. 理解（Understanding）：解釋、描述和闡明訊息意義\n3. 應用（Applying）：將概念或技能運用於新情境\n4. 分析（Analyzing）：將資訊細分並分析關係\n5. 評鑑（Evaluating）：進行批判性判斷和評價\n6. 創造（Creating）：整合元素產出新的觀點\n\n請在生成題目後，以以下格式說明題目的認知層次分析：\n\n【認知層次分析】\n- 主要評估層次：[請選擇上述六個層次中的一個或多個]\n- 評估原因：[說明為什麼選擇這些層次]\n- 教學價值：[說明此題目如何幫助學生發展這些認知能力]\n- 適合年級：[說明此題目最適合哪個年級的學生，以及原因]";
                    break;
                case 'tf':
                    $promptPrefix = "請根據文章生成一道適合國中或國小學生程度的是非題，並標明正確答案是「是」還是「否」。\n\n請注意以下幾點：\n1. 題目難度要符合國中或國小學生的認知發展階段\n2. 用詞要簡單易懂，避免過於複雜的專業術語\n3. 是非判斷要明確，避免模稜兩可的表述\n4. 題目內容要貼近學生的生活經驗\n\n請參考 Bloom 認知領域修訂版本來設計題目，確保題目能評估學生的認知能力層次：\n\n1. 記憶（Remembering）：回想、辨識和重現資訊\n2. 理解（Understanding）：解釋、描述和闡明訊息意義\n3. 應用（Applying）：將概念或技能運用於新情境\n4. 分析（Analyzing）：將資訊細分並分析關係\n5. 評鑑（Evaluating）：進行批判性判斷和評價\n6. 創造（Creating）：整合元素產出新的觀點\n\n請在生成題目後，以以下格式說明題目的認知層次分析：\n\n【認知層次分析】\n- 主要評估層次：[請選擇上述六個層次中的一個或多個]\n- 評估原因：[說明為什麼選擇這些層次]\n- 教學價值：[說明此題目如何幫助學生發展這些認知能力]\n- 適合年級：[說明此題目最適合哪個年級的學生，以及原因]";
                    break;
            }
            
            if (!empty($promptPrefix)) {
                $userInput = $promptPrefix . "\n\n" . $userInput;
            }
        }
        
        if ($article_url) {
            $userInput .= "\n\n請根據這篇文章的內容來出題：" . $article_url;
        }

        $url = 'https://api.deepseek.com/chat/completions';

        $data = [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => $userInput],
            ],
            'stream' => false,
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $responseText = '錯誤: ' . curl_error($ch);
        } else {
            $result = json_decode($response, true);
            $responseText = $result['choices'][0]['message']['content'] ?? '沒有回應';
        }

        curl_close($ch);
    }
}

?>

<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <title>題目編輯器</title>
    <link rel="icon" type="image/png" href="../img/icon.png">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/deepseek.css">
</head>
<header>
    <?php
    include "nav.php";
    ?>
</header>
<div style="height: 80px;"></div>
<body>
    <div class="editor-container">
        <div class="question-editor">
            <h2>題目編輯器</h2>
            <form id="question_form" method="post" action="">
                <select name="question_type" id="question_type" required>
                    <option value="選擇題" <?php echo ($edit_type === '選擇題') ? 'selected' : ''; ?>>選擇題</option>
                    <option value="是非題" <?php echo ($edit_type === '是非題') ? 'selected' : ''; ?>>是非題</option>
                    <option value="問答題" <?php echo ($edit_type === '問答題') ? 'selected' : ''; ?>>問答題</option>
                </select>

                <textarea name="question_content" placeholder="題目內容" required><?php echo htmlspecialchars($edit_content); ?></textarea>
                <div id="answer_section">
                    <!-- 動態內容將由JavaScript生成 -->
                    <input type="text" name="question_answer" placeholder="正確答案" required>
                </div>
                <button type="submit" name="save_question">儲存題目</button>
                <a href="view-all-qusetion.php?article_id=<?php echo isset($_GET['article_id']) ? intval($_GET['article_id']) : ''; ?>" class="view-questions-btn">查看已儲存題目</a>
            </form>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const questionType = document.getElementById('question_type');
                const answerSection = document.getElementById('answer_section');
                
                // 設置初始值
                const editType = '<?php echo $edit_type; ?>';
                const editAnswer = '<?php echo addslashes($edit_answer); ?>';
                const editOptionA = '<?php echo addslashes($edit_option_a); ?>';
                const editOptionB = '<?php echo addslashes($edit_option_b); ?>';
                const editOptionC = '<?php echo addslashes($edit_option_c); ?>';
                const editOptionD = '<?php echo addslashes($edit_option_d); ?>';
                
                // 觸發一次change事件來初始化答案區域
                const event = new Event('change');
                questionType.dispatchEvent(event);
                
                // 設置已保存的值
                setTimeout(() => {
                    if (editType === '選擇題') {
                        document.querySelector('input[name="option_a"]').value = editOptionA;
                        document.querySelector('input[name="option_b"]').value = editOptionB;
                        document.querySelector('input[name="option_c"]').value = editOptionC;
                        document.querySelector('input[name="option_d"]').value = editOptionD;
                        document.querySelector('select[name="question_answer"]').value = editAnswer;
                    } else if (editType === '是非題') {
                        const tfSelect = document.querySelector('select[name="question_answer"]');
                        if (tfSelect) {
                            tfSelect.value = editAnswer === 'true' ? '是' : '否';
                        }
                    } else if (editType === '問答題') {
                        const answerInput = document.querySelector('input[name="question_answer"]');
                        if (answerInput) {
                            answerInput.value = editAnswer;
                        }
                    }
                }, 0);
            });
            
            document.getElementById('question_type').addEventListener('change', function() {
                const answerSection = document.getElementById('answer_section');
                const type = this.value;
                
                let html = '';
                if (type === '選擇題') {
                    html = `
                        <div class="options">
                            <input type="text" name="option_a" placeholder="選項A" required>
                            <input type="text" name="option_b" placeholder="選項B" required>
                            <input type="text" name="option_c" placeholder="選項C" required>
                            <input type="text" name="option_d" placeholder="選項D" required>
                        </div>
                        <label>選擇答案：</label>
                        <select name="question_answer" required>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    `;
                } else if (type === '是非題') {
                    html = `
                        <label>選擇答案：</label>
                        <select name="question_answer" required>
                            <option value="是">是</option>
                            <option value="否">否</option>
                        </select>
                    `;
                } else {
                    html = '<input type="text" name="question_answer" placeholder="正確答案" required>';
                }
                
                answerSection.innerHTML = html;
            });

            // AJAX 送出表單
            document.getElementById('question_form').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                formData.append('save_question', '1');
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('題目儲存成功！');
                        // 清空所有 input、textarea、select 欄位內容
                        form.querySelectorAll('input[type="text"], textarea').forEach(el => el.value = '');
                        form.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
                        document.getElementById('question_type').dispatchEvent(new Event('change'));
                    } else {
                        alert('儲存失敗: ' + (data.error || '未知錯誤'));
                    }
                })
                .catch(err => {
                    alert('儲存失敗: ' + err);
                });
            });
            </script>
        </div>

        <div class="api-chat">
            <h2>出題助手</h2>
            <form method="post">
                <div class="question-type-selector">
                    <label for="ai_question_type">請選擇您想出的題型：</label>
                    <select name="ai_question_type" id="ai_question_type">
                        <option value="none">無 (編輯問題時可用)</option>
                        <option value="choice">選擇題</option>
                        <option value="fill">問答題</option>
                        <option value="tf">是非題</option>
                    </select>
                </div>
                <input type="text" name="user_input" placeholder="輸入你的問題..." required>
                <button type="submit">送出</button>
            </form>

    <?php if ($responseText): ?>
            <h3>回應：</h3>
            <div class="response-container" style="word-wrap: break-word; overflow-wrap: break-word; max-width: 100%; box-sizing: border-box; overflow-x: hidden; max-height: 530px; overflow-y: auto;">
                <?php echo htmlspecialchars($responseText); ?>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- 加載動畫覆蓋層移到 body 最底部，預設不顯示 -->
    <div class="loading-overlay" id="loadingOverlay">
        <img src="../img/loading-earth.svg" class="loading-earth" alt="載入中">
        <div class="loading-text">
            <span>L</span>
            <span>O</span>
            <span>A</span>
            <span>D</span>
            <span>I</span>
            <span>N</span>
            <span>G</span>
            <span>.</span>
            <span>.</span>
            <span>.</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.api-chat form');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            form.addEventListener('submit', function() {
                loadingOverlay.classList.add('active');
            });
        });
    </script>
</body>

</html>