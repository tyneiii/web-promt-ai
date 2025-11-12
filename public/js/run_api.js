const modal = document.getElementById("prompt-modal");
const runBtn = document.getElementById("runBtn");
const resultBox = document.getElementById("resultBox");

runBtn.addEventListener("click", openPromptModal);

function openPromptModal() {
  modal.style.display = "flex";
}

function closePromptModal() {
  modal.style.display = "none";
}

async function confirmRunPrompt() {
  closePromptModal();
  const prompt = document.getElementById("promptInput").value.trim();

  if (!prompt) {
    alert("⚠️ Vui lòng nhập prompt!");
    return;
  }

  resultBox.style.display = "block";
  resultBox.textContent = "⏳ Đang chạy prompt...";

  try {
    const response = await fetch("/web-promt-ai/api/run_api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ prompt }),
    });

    const data = await response.json();
    if (data.error) {
      resultBox.textContent = "❌ Lỗi: " + data.error;
    } else {
      resultBox.textContent = "✅ Kết quả:\n\n" + data.result;
    }
  } catch (err) {
    resultBox.textContent = "⚠️ Lỗi kết nối: " + err.message;
  }
}
