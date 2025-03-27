<?php
// 設定 API 金鑰
$apiKey = 'sk-9483cae29d5644318c39537d786410f7'; // <<< 換成你的 DeepSeek API Key

$responseText = '';

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
    <title>DeepSeek 測試</title>
</head>
<body>
    <h2>問 DeepSeek 一個問題</h2>
    <form method="post">
        <input type="text" name="user_input" placeholder="輸入你的問題..." style="width: 300px;" required>
        <button type="submit">送出</button>
    </form>

    <?php if ($responseText): ?>
        <h3>回應：</h3>
        <div style="white-space: pre-wrap; border: 1px solid #ccc; padding: 10px; max-width: 600px;">
            <?php echo htmlspecialchars($responseText); ?>
        </div>
    <?php endif; ?>
</body>
</html>