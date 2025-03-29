<?php
// 設定 API 金鑰
$apiKey = 'sk-9483cae29d5644318c39537d786410f7'; // <<< 換成你的 DeepSeek API Key

$responseText = '';

// 儲存題目到資料庫
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_question'])) {
    $title = $_POST['question_title'] ?? '';
    $content = $_POST['question_content'] ?? '';
    $answer = $_POST['question_answer'] ?? '';
    
    // 連接資料庫
    include 'db_connect.php';
    
    // 準備SQL語句
    $stmt = $conn->prepare("INSERT INTO questions (title, content, answer, question_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $content, $answer, $question_type);
    
    // 獲取選擇的題型
    $question_type = $_POST['question_type'] ?? '問答題';
    
    // 執行SQL
    if ($stmt->execute()) {
        echo '<script>alert("題目儲存成功！");</script>';
    } else {
        echo '<script>alert("儲存失敗: ' . $conn->error . '");</script>';
    }
    
    $stmt->close();
    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = $_POST['user_input'] ?? '';

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
        'Authorization: ' . 'Bearer ' . $apiKey,
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
?>

<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <title>題目編輯器</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/deepseek.css">
</head>
<header>
    <?php
    include "nav.php";
    ?>
</header>

<body>
    <div class="editor-container">
        <div class="question-editor">
            <h2>新增題目</h2>
            <form method="post" action="">
                <select name="question_type" id="question_type" required>
                    <option value="選擇題">選擇題</option>
                    <option value="是非題">是非題</option>
                    <option value="問答題">問答題</option>
                </select>
                <input type="text" name="question_title" placeholder="題目標題" required>
                <textarea name="question_content" placeholder="題目內容" required></textarea>
                <div id="answer_section">
                    <!-- 動態內容將由JavaScript生成 -->
                    <input type="text" name="question_answer" placeholder="正確答案" required>
                </div>
                <button type="submit" name="save_question">儲存題目</button>
            </form>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const questionType = document.getElementById('question_type');
                const answerSection = document.getElementById('answer_section');
                
                // 觸發一次change事件來初始化答案區域
                const event = new Event('change');
                questionType.dispatchEvent(event);
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
            </script>
        </div>

        <div class="api-chat">
            <h2>出題助手</h2>
            <form method="post">
                <input type="text" name="user_input" placeholder="輸入你的問題..." required>
                <button type="submit">送出</button>
            </form>

    <?php if ($responseText): ?>
            <h3>回應：</h3>
            <div class="response-container">
                <?php echo htmlspecialchars($responseText); ?>
            </div>
        <?php endif; ?>
        </div>
    </div>
</body>

</html>