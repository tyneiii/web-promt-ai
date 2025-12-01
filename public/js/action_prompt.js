document.querySelectorAll('.love-btn, .save-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const promptId = this.dataset.promptId;
        const isLove = this.classList.contains('love-btn');
        const isActive = this.classList.contains(isLove ? 'loved' : 'saved');

        // Tạm vô hiệu hóa nút để tránh click liên tục
        this.disabled = true;

        fetch('../../public/ajax/action_prompt.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: isActive ? (isLove ? 'unlove' : 'unsave') : (isLove ? 'love' : 'save'),
                prompt_id: promptId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const countSpan = this.querySelector('.count');
                const icon = this.querySelector('i');

                // Cập nhật số lượng
                countSpan.textContent = isLove ? data.love_count : data.save_count;

                // Toggle trạng thái
                if (data.action === 'loved' || data.action === 'saved') {
                    this.classList.add('loved', 'saved');
                    if (isLove) {
                        icon.className = 'fa-solid fa-heart text-red';
                        this.classList.add('loved');
                        this.classList.remove('saved');
                    } else {
                        icon.className = 'fa-solid fa-bookmark text-blue';
                        this.classList.add('saved');
                    }
                } else {
                    this.classList.remove('loved', 'saved');
                    if (isLove) {
                        icon.className = 'fa-regular fa-heart';
                    } else {
                        icon.className = 'fa-regular fa-bookmark';
                    }
                }
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Lỗi kết nối!');
        })
        .finally(() => {
            this.disabled = false;
        });
    });
});