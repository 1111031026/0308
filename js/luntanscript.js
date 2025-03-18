document.addEventListener('DOMContentLoaded', () => {
    const typeButtons = document.querySelectorAll('.type-btn');
    const options = document.querySelectorAll('.options');

    // 確保初始狀態正確
    const activeType = document.querySelector('.type-btn[data-active="true"]')?.getAttribute('data-type');
    if (activeType) {
        options.forEach(opt => opt.removeAttribute('data-active'));
        document.querySelector(`.${activeType}-options`)?.setAttribute('data-active', 'true');
    }

    // 切換類型
    typeButtons.forEach(button => {
        button.addEventListener('click', () => {
            typeButtons.forEach(btn => btn.removeAttribute('data-active'));
            button.setAttribute('data-active', 'true');

            const type = button.getAttribute('data-type');
            options.forEach(opt => opt.removeAttribute('data-active'));
            document.querySelector(`.${type}-options`).setAttribute('data-active', 'true');

            if (type === 'true-false') {
                document.querySelectorAll('.true-false-btn').forEach(btn => btn.removeAttribute('data-selected'));
            }
        });
    });

    // 處理是非選項
    document.querySelectorAll('.true-false-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.true-false-btn').forEach(b => b.removeAttribute('data-selected'));
            btn.setAttribute('data-selected', 'true');
        });
    });

    // 模擬確認儲存
    const saveButton = document.querySelector('.external-btn.save');
    saveButton.addEventListener('click', (e) => {
        e.preventDefault();
        const type = document.querySelector('.type-btn[data-active="true"]')?.getAttribute('data-type');
        const content = document.querySelector('.content-textarea')?.value;

        let dataToSave = { type, content };
        if (type === 'multiple-choice') {
            dataToSave.options = Array.from(document.querySelectorAll('.multiple-choice-options input'))
                .map(input => input.value);
        } else if (type === 'true-false') {
            const selected = document.querySelector('.true-false-btn[data-selected="true"]');
            dataToSave.answer = selected ? selected.getAttribute('data-value') : null;
        } else if (type === 'fill-in') {
            dataToSave.answer = document.querySelector('.fill-in-options .content-textarea')?.value;
        }

        console.log('存入資料:', dataToSave);
        window.location.href = '1.html';
    });

    // 留言提交邏輯
    const commentInput = document.querySelector('.comment-input');
    const commentSubmit = document.querySelector('.comment-submit');
    const commentsContainer = document.querySelector('.comments-container');

    commentSubmit.addEventListener('click', () => {
        const commentText = commentInput.value.trim();
        if (commentText) {
            const user = 'user1013'; // 假設當前用戶為 user1013
            const commentDiv = document.createElement('div');
            commentDiv.className = 'comment';
            commentDiv.innerHTML = `<span class="comment-user">${user}:</span><span class="comment-text">${commentText}</span>`;
            commentsContainer.appendChild(commentDiv);
            commentInput.value = ''; // 清空輸入框
        }
    });

    // 允許按 Enter 鍵提交留言
    commentInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            commentSubmit.click();
        }
    });
});