// run_api.js - chỉ chứa JS
let currentPrompt = '';

const promptModal = document.getElementById("prompt-modal");
const resultBox = document.getElementById("resultBox");
const promptInput = document.getElementById("promptInput");

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

document.addEventListener("DOMContentLoaded", () => {
  // Delegation cho nút run
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".run-btn");
    if (btn) openPromptModal(btn);
  });

  // Đóng modal khi click overlay
  promptModal?.addEventListener("click", (e) => {
    if (e.target.classList.contains("modal-overlay")) closePromptModal();
  });
  resultBox?.addEventListener("click", (e) => {
    if (e.target === resultBox) closeResultBox();
  });
});

function openPromptModal(btn) {
  const promptText = btn.dataset.prompt || '';
  if (!promptText.trim()) {
    alert("Không có prompt!");
    return;
  }
  currentPrompt = promptText;
  promptInput.value = promptText;
  promptModal.style.display = "flex";
  document.body.style.overflow = "hidden";
  promptInput.focus();
}

function closePromptModal() {
  promptModal.style.display = "none";
  document.body.style.overflow = "";
  promptInput.value = "";
}

function openResultBox() {
  resultBox.style.display = "flex";
  document.body.style.overflow = "hidden";
}

window.closeResultBox = function () {
  resultBox.style.display = "none";
  resultBox.innerHTML = "";
  document.body.style.overflow = "";
};

async function confirmRunPrompt() {
  const prompt = promptInput.value.trim() || currentPrompt.trim();
  if (!prompt) return alert("Nhập prompt đi bạn ơi!");

  closePromptModal();

  resultBox.innerHTML = `<div class="loading"><i class="fa fa-spinner fa-spin"></i> Đang hỏi AI...</div>`;
  openResultBox();

  console.log("Đang gửi prompt:", prompt);  // ← THÊM DÒNG NÀY ĐỂ XEM CÓ GỬI ĐÚNG KHÔNG

  try {
    const res = await fetch("../../api/run_api.php",  {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ prompt: prompt })  // ← Đảm bảo gửi đúng key "prompt"
    });

    // Thêm log để debug
    console.log("Status:", res.status);
    const text = await res.text();  // Dùng text() trước để xem thật sự nhận gì
    console.log("Raw response:", text);

    const data = JSON.parse(text);

    if (data.error) throw new Error(data.error);

    showResultSuccess(data.result);
  } catch (err) {
    showResultError(err.message);
  }
}
function showResultSuccess(text) {
  resultBox.innerHTML = `
    <div class="result-content">
      <button class="close-result" onclick="closeResultBox()">X</button>
      <h4>Kết quả từ AI</h4>
      <pre>${escapeHtml(text)}</pre>
    </div>`;
}

function showResultError(msg) {
  resultBox.innerHTML = `
    <div class="error-content">
      <button class="close-result" onclick="closeResultBox()">X</button>
      <h4>Lỗi rồi</h4>
      <pre>${escapeHtml(msg)}</pre>
    </div>`;
}