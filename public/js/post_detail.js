      const fullPromptContent = window.fullPromptContent || '';  // Default về '' nếu undefined
      const runBtn = document.querySelector('.run-ai-btn'); 

function handleAction(action, promptId, comment = null) {
      const apiUrl = '../../Controller/user/prompt_detail1.php';
      const postData = { action, prompt_id: promptId };
 // Sửa selector nếu cần
      if (comment !== null) postData.comment = comment;
      fetch(apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(postData)
      })
      .then(r => r.json())
      .then(data => {
        alert(data.success ? 'Thành công: ' + data.message : 'Lỗi: ' + data.message);
        if (data.success) window.history.back();
      })
      .catch(err => {
        console.error(err);
        alert('Lỗi kết nối server');
      });
    }

    async function runPromptWithAI(promptContent) {
      const runResult = document.getElementById('run-result');
      runResult.innerHTML = `
        <div class="loading" style="text-align:center; padding:20px; color:#666;">
          <i class="fas fa-spinner fa-spin" style="font-size:24px;"></i><br><br>
          <strong>Đang chạy thử prompt bằng AI (miễn phí)</strong><br>
          <small>Đang thử các model mạnh hỗ trợ tiếng Việt... (5-15 giây)</small>
        </div>
      `;
      try {
        const response = await fetch("http://localhost:8080/web-promt-ai/api/run_api.php", { 
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ prompt: promptContent })
        });
        const data = await response.json();
        if (data.success) {
          runResult.innerHTML = `
            <div class="ai-response" style="background:#f8fff8; border-left:5px solid #4CAF50; padding:15px; border-radius:8px; margin:15px 0;">
              <strong style="color:#2e7d32;">
                Thành công! Đã chạy bằng model: <code style="background:#e8f5e8; padding:3px 8px; border-radius:4px;">${data.model || 'AI'}</code>
              </strong>
              <div style="margin-top:12px; padding:15px; background:white; border-radius:6px; white-space: pre-wrap; font-family: inherit; line-height:1.6;">
                ${data.result.replace(/\n/g, '<br>')}
              </div>
            </div>
            ${getActionButtonsHTML()}
          `;
        } else {
          runResult.innerHTML = `
            <div style="background:#ffebee; border-left:5px solid #f44336; padding:15px; border-radius:8px; color:#c62828;">
              <strong>Không thể chạy thử prompt</strong><br><br>
              ${data.error || 'Lỗi không xác định'}<br><br>
              <small>Gợi ý: Thử lại sau 1-2 phút (hết quota tạm thời) hoặc kiểm tra file <code>debug.log</code></small>
            </div>
            ${getActionButtonsHTML()}
          `;
        }
      } catch (err) {
        console.error('Lỗi kết nối:', err);
        runResult.innerHTML = `
          <div style="color:red; background:#fff0f0; padding:15px; border-radius:8px;">
            <strong>Lỗi kết nối tới run_api.php</strong><br>
            Kiểm tra:<br>
            • File <code>api/run_api.php</code> có tồn tại?<br>
            • File <code>api/key.php</code> có đúng token?<br>
            • Mở F12 → Console/Network để xem lỗi chi tiết
          </div>
          ${getActionButtonsHTML()}
        `;
      }
    }

    function openReportPopup() {
      document.getElementById('reportPopup').style.display = 'flex';
    }

    function closeReportPopup() {
      document.getElementById('reportPopup').style.display = 'none';
    }

    function getActionButtonsHTML() {
      let buttons = '<div class="action-buttons">';
      if (promptStatus === 'waiting') {
        buttons += `
          <button class="action-btn approve-btn" onclick="if(confirm('Duyệt bài đăng này?')) handleAction('approve', currentPromptId, '')">
            Duyệt bài đăng
          </button>
          <button class="action-btn reject-btn" onclick="if(confirm('Từ chối bài đăng này?')) {
            const comment = prompt('Lý do từ chối:');
            handleAction('reject', currentPromptId, comment || 'Không có lý do');
          }">
            Từ chối bài đăng
          </button>
        `;
      } else if (promptStatus === 'report') {
        buttons += `
          <button class="action-btn approve-btn" onclick="if(confirm('Xóa báo cáo và khôi phục bài?')) handleAction('unreport', currentPromptId)">
            Bỏ báo cáo (Duyệt lại)
          </button>
          <button class="action-btn delete-btn" style="background:#e74c3c;" onclick="if(confirm('XÓA VĨNH VIỄN bài đăng này?')) handleAction('delete', currentPromptId)">
            Xóa bài đăng
          </button>
        `;
      } else {
        buttons += `<button class="action-btn back-btn" onclick="window.history.back()">Trở về</button>`;
      }
      buttons += '</div>';
      return buttons;
    }

    document.addEventListener('DOMContentLoaded', () => {
      // Modal ảnh
      const modal = document.getElementById("imageModal");
      const modalImg = document.getElementById("img01");
      const postImage = document.querySelector(".post-image");
      const span = document.getElementsByClassName("close")[0];
      if (postImage) {
        postImage.onclick = () => {
          modal.style.display = "flex";
          modalImg.src = postImage.src;
        };
      }
      if (span) span.onclick = () => modal.style.display = "none";
      window.onclick = (e) => { if (e.target === modal) modal.style.display = "none"; };

      // Nút Run Prompt - Sửa selector thành .run-ai-btn
      const runBtn = document.querySelector('.run-ai-btn');
      if (runBtn) {
        runBtn.addEventListener('click', () => {
          if (!fullPromptContent || fullPromptContent.trim() === '') {
            alert('Prompt trống, không thể chạy thử!');
            return;
          }
          runPromptWithAI(fullPromptContent);
        });
      }

      // Auto resize textarea
      document.querySelectorAll('.short-desc, .info-block textarea').forEach(textarea => {
        const autoGrow = () => {
          textarea.style.height = 'auto';
          textarea.style.height = textarea.scrollHeight + 'px';
        };
        textarea.addEventListener('input', autoGrow);
        autoGrow();
      });
    });
  