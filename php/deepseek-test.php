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
    $stmt = $conn->prepare("INSERT INTO questions (title, content, answer) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $answer);
    
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
                <input type="text" name="question_title" placeholder="題目標題" required>
                <textarea name="question_content" placeholder="題目內容" required></textarea>
                <input type="text" name="question_answer" placeholder="正確答案" required>
                <button type="submit" name="save_question">儲存題目</button>
            </form>
        </div>

        <div class="api-chat">
            <h2>問 DeepSeek 一個問題</h2>
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