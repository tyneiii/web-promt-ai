document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.querySelector('.comment-toggle');
    const dropdown = document.getElementById('commentDropdown');

    if (!toggle || !dropdown) return;

    // Click toggle
    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            loadUserComments();
            dropdown.style.display = 'block';
        } else {
            closeComments();
        }
    });

    // Đóng khi click ngoài
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.comment-toggle') && !e.target.closest('#commentDropdown')) {
            closeComments();
        }
    });
});

function closeComments() {
    const dropdown = document.getElementById('commentDropdown');
    if (dropdown) dropdown.style.display = 'none';
}


function deleteComment(comment_id, prompt_id) {
    if (!confirm("Bạn có chắc muốn xóa bình luận này?")) return;

    fetch("../../controller/comment/process_comment.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            action: "delete",
            comment_id: comment_id,
            prompt_id: prompt_id
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById("comment-" + comment_id).remove();
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert("Lỗi khi xóa bình luận");
    });
}

