document.addEventListener('DOMContentLoaded', () => {
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
            // 自動滾動到最新留言
            commentsContainer.scrollTop = commentsContainer.scrollHeight;
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