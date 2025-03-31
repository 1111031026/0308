async function callDeepSeekAPI() {
    // 從輸入框獲取用戶輸入
    const input = document.getElementById("userInput").value;
    const responseElement = document.getElementById("response");

    // 設定 API 的基本資訊
    const apiKey = "sk-9483cae29d5644318c39537d786410f7"; // 將這裡替換成你的 DeepSeek API KEY
    const apiUrl = "https://api.deepseek.com/v1/chat/completions"; // DeepSeek 的 API 端點

    // 準備要發送的資料
    const data = {
        model: "deepseek-chat", // 使用 DeepSeek 的聊天模型
        messages: [
            { role: "user", content: input } // 用戶輸入的內容
        ],
        max_tokens: 100, // 限制回應長度，可調整
        temperature: 0.7 // 控制回應的創造性，範圍 0-1
    };

    try {
        // 發送請求到 DeepSeek API
        const response = await fetch(apiUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${apiKey}` // 使用你的 API KEY 進行驗證
            },
            body: JSON.stringify(data)
        });

        // 檢查回應是否成功
        if (!response.ok) {
            throw new Error("API 請求失敗");
        }

        // 解析回應資料
        const result = await response.json();
        const reply = result.choices[0].message.content; // 提取 AI 的回應

        // 將回應顯示在網頁上
        responseElement.textContent = reply;
    } catch (error) {
        // 處理錯誤
        responseElement.textContent = "發生錯誤：" + error.message;
    }
}