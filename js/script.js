document.addEventListener('DOMContentLoaded', () => {
    const typeButtons = document.querySelectorAll('.type-btn');
    const options = document.querySelectorAll('.options');

    // 確保初始狀態正確
    const activeType = document.querySelector('.type-btn[data-active="true"]').getAttribute('data-type');
    options.forEach(opt => opt.removeAttribute('data-active'));
    document.querySelector(`.${activeType}-options`).setAttribute('data-active', 'true');

    // 切換類型
    typeButtons.forEach(button => {
        button.addEventListener('click', () => {
            // 更新按鈕活躍狀態
            typeButtons.forEach(btn => btn.removeAttribute('data-active'));
            button.setAttribute('data-active', 'true');

            // 更新選項顯示
            const type = button.getAttribute('data-type');
            options.forEach(opt => opt.removeAttribute('data-active'));
            document.querySelector(`.${type}-options`).setAttribute('data-active', 'true');

            // 重置是非選項
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
    saveButton.addEventListener('click', () => {
        const type = document.querySelector('.type-btn[data-active="true"]').getAttribute('data-type');
        const content = document.querySelector('.content-textarea').value;

        let dataToSave = { type, content };
        if (type === 'multiple-choice') {
            dataToSave.options = Array.from(document.querySelectorAll('.multiple-choice-options input'))
                .map(input => input.value);
        } else if (type === 'true-false') {
            const selected = document.querySelector('.true-false-btn[data-selected="true"]');
            dataToSave.answer = selected ? selected.getAttribute('data-value') : null;
        } else if (type === 'fill-in') {
            dataToSave.answer = document.querySelector('.fill-in-options .content-textarea').value;
        }

        // 這裡模擬存入資料庫，實際應與後端 API 整合
        console.log('存入資料:', dataToSave);

        // 跳轉到 1.html
        window.location.href = '1.html';
    });
});