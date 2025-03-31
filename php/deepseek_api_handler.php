<?php
$api_key = "sk-9483cae29d5644318c39537d786410f7";

function generateQuestion($type) {
    global $api_key;
    
    $curl = curl_init();
    
    $prompt = "";
    switch($type) {
        case 'multiple-choice':
            $prompt = "請生成一道關於永續發展的選擇題，包含題目和四個選項(A,B,C,D)，格式為JSON：{\"question\": \"題目\", \"options\": {\"A\": \"選項A\", \"B\": \"選項B\", \"C\": \"選項C\", \"D\": \"選項D\"}, \"answer\": \"正確選項\"}";
            break;
        case 'true-false':
            $prompt = "請生成一道關於永續發展的是非題，格式為JSON：{\"question\": \"題目\", \"answer\": true/false}";
            break;
        case 'fill-in':
            $prompt = "請生成一道關於永續發展的填空題，格式為JSON：{\"question\": \"題目\", \"answer\": \"答案\"}";
            break;
    }

    $postData = [
        "model" => "deepseek-chat",
        "messages" => [
            [
                "role" => "user",
                "content" => $prompt
            ]
        ]
    ];

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.deepseek.com/v1/chat/completions",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,  // 添加這行來忽略 SSL 驗證
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $api_key,
            "Content-Type: application/json",
            "Accept: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    
    curl_close($curl);

    if ($err) {
        return ["error" => "API 請求失敗: " . $err];
    }

    if ($httpCode !== 200) {
        return ["error" => "API 響應錯誤，狀態碼: " . $httpCode . ", 響應內容: " . $response];
    }

    try {
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ["error" => "JSON 解析錯誤: " . json_last_error_msg()];
        }
        return $decodedResponse;
    } catch (Exception $e) {
        return ["error" => "響應處理錯誤: " . $e->getMessage()];
    }
}

// 如果是通過 AJAX 請求調用
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $type = $input['type'] ?? '';
    echo json_encode(generateQuestion($type));
}
?>