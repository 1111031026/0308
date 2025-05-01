<?php
function checkAnswerSimilarity($userAnswer, $correctAnswer) {
    global $api_key;
    
    $curl = curl_init();
    
    $prompt = "請判斷以下兩個答案是否表達相同的意思，標準可以寬鬆一點，例如有兩個以上的字與正確答案相符就視為正確。回答只需要true或false。\n答案1：{$userAnswer}\n答案2：{$correctAnswer}";

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
        return false;
    }

    try {
        $decodedResponse = json_decode($response, true);
        if (isset($decodedResponse['choices'][0]['message']['content'])) {
            $content = strtolower(trim($decodedResponse['choices'][0]['message']['content']));
            return $content === 'true';
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}