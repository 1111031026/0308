<?php
$api_key = "sk-9483cae29d5644318c39537d786410f7";

function extractContent($html) {
    $prompt = "請幫我分析並重點整理以下文章內容。請注意以下格式要求：
1. 使用純文字格式輸出，完全不要使用任何特殊符號（包括但不限於：*、#、-、>、<、=、+、~、`、@、\$、%、^、&、|、\\、/、[、]、{、}等）
2. 使用數字編號（1、2、3...）和縮排來組織內容
3. 每個重點請以'重點X：'開頭（X為數字）
4. 內容請分段呈現，段落之間用空行分隔
5. 請務必使用繁體中文回答，不要使用英文或簡體中文
6. 不要使用任何標記語言或格式化符號
7. 不要使用任何表情符號或特殊字元

請根據以上要求分析以下內容：\n\n" . $html;
    
    global $api_key;
    $maxRetries = 3;
    $retryDelay = 2;
    
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $curl = curl_init();
        
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
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
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
        
        if (!$err && $httpCode === 200) {
            $decodedResponse = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decodedResponse['choices'][0]['message']['content'];
            }
        }
        
        if ($attempt < $maxRetries) {
            sleep($retryDelay);
            continue;
        }
    }
    
    return $html; // 如果提取失敗，返回原始HTML
}

function analyzeArticle($content) {
    global $api_key;
    
    $maxRetries = 3;
    $retryDelay = 2; // seconds
    
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $curl = curl_init();
    
    $prompt = "請幫我分析並重點整理以下文章內容。請注意以下格式要求：
1. 使用純文字格式輸出，完全不要使用任何特殊符號（包括但不限於：*、#、-、>、<、=、+、~、`、@、\$、%、^、&、|、\\、/、[、]、{、}等）
2. 使用數字編號（1、2、3...）和縮排來組織內容
3. 每個重點請以'重點X：'開頭（X為數字）
4. 內容請分段呈現，段落之間用空行分隔
5. 請務必使用繁體中文回答，不要使用英文或簡體中文
6. 不要使用任何標記語言或格式化符號
7. 不要使用任何表情符號或特殊字元

請根據以上要求分析以下內容：\n\n" . $content;

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
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
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

    if ($err || $httpCode !== 200) {
        if ($attempt < $maxRetries) {
            sleep($retryDelay);
            continue;
        }
        $errorMessage = $err ? "API 請求失敗 (嘗試 $attempt 次): " . $err : "API 響應錯誤，狀態碼: " . $httpCode . ", 響應內容: " . $response;
        return ["error" => $errorMessage];
    }

    try {
        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            if ($attempt < $maxRetries) {
                sleep($retryDelay);
                continue;
            }
            return ["error" => "JSON 解析錯誤 (嘗試 $attempt 次): " . json_last_error_msg()];
        }
        return $decodedResponse;
    } catch (Exception $e) {
        if ($attempt < $maxRetries) {
            sleep($retryDelay);
            continue;
        }
        return ["error" => "響應處理錯誤 (嘗試 $attempt 次): " . $e->getMessage()];
    }
    }
    
    return ["error" => "所有重試都失敗了，請稍後再試"];
}
?>