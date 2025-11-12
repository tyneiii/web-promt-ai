
let currentPrompt = '';

// Lấy elements
const promptModal = document.getElementById("prompt-modal");
const resultBox = document.getElementById("resultBox"); // Dùng #resultBox làm overlay modal kết quả

// Event delegation cho run-btn
document.addEventListener("DOMContentLoaded", function() {
  document.addEventListener("click", function(event) {
    if (event.target.classList.contains("run-btn")) {
      event.preventDefault();
      event.stopPropagation();
      openPromptModal(event);
    }
  });
  
  // Overlay đóng prompt modal
  if (promptModal) {
    promptModal.addEventListener("click", function(event) {
      if (event.target.classList.contains("modal-overlay")) {
        closePromptModal(event);
      }
    });
  }

  // Overlay đóng result modal (#resultBox)
  if (resultBox) {
    resultBox.addEventListener("click", function(event) {
      if (event.target === resultBox) { // Click overlay
        closeResultBox(event);
      }
    });
  }
});

function openPromptModal(event) {
  console.log('Button clicked:', event.target);
  
  let promptText = event.target.dataset.prompt || event.target.getAttribute('data-prompt');
  console.log('promptText loaded:', promptText ? promptText.substring(0, 100) + '...' : 'EMPTY!');
  
  if (!promptText) {
    alert("⚠️ Không tìm thấy prompt! Kiểm tra data-prompt trong HTML.");
    return;
  }
  
  currentPrompt = promptText;
  const promptInput = document.getElementById("promptInput");
  if (promptInput) {
    promptInput.value = promptText;
    promptInput.focus();
    console.log('Set to textarea OK');
  } else {
    console.error("Không tìm thấy #promptInput!");
  }
  
  if (promptModal) {
    promptModal.style.display = "flex";
    console.log('Prompt modal opened');
  }
}

function closePromptModal(event) {
  if (event) event.stopPropagation();
  if (promptModal) {
    promptModal.style.display = "none";
  }
  const promptInput = document.getElementById("promptInput");
  if (promptInput) promptInput.value = '';
}

// Mở result modal (#resultBox)
function openResultBox() {
  if (resultBox) {
    resultBox.style.display = "flex";
    console.log('Result modal opened');
  }
}

// Đóng result modal
window.closeResultBox = function(event) {
  if (event) event.stopPropagation();
  if (resultBox) {
    resultBox.style.display = "none";
    resultBox.innerHTML = ''; // Clear nội dung
  }
};

async function confirmRunPrompt() {
  const promptInput = document.getElementById("promptInput");
  const prompt = (promptInput ? promptInput.value.trim() : currentPrompt.trim());

  console.log('Sending prompt:', prompt.substring(0, 100) + '...');

  if (!prompt) {
    alert("⚠️ Vui lòng nhập prompt!");
    return;
  }

  closePromptModal();
  
  // Hiển thị loading trong #resultBox
  if (resultBox) {
    resultBox.innerHTML = `
      <div class="loading">
        <i class="fa fa-spinner fa-spin"></i> Đang chạy prompt...
      </div>
    `;
    openResultBox();
  } else {
    alert("⏳ Đang chạy prompt...");
  }

  try {
    const response = await fetch("/web-promt-ai/api/run_api.php", {  // Đảm bảo endpoint đúng
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ prompt }),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();
    console.log('API Response:', data);

    if (data.error) {
      if (resultBox) {
        resultBox.innerHTML = `
          <div class="error-content">
            <h4>Lỗi:</h4>
            <p>${escapeHtml(data.error)}</p>
            <button class="close-result" onclick="closeResultBox()" title="Đóng">×</button>
          </div>
        `;
      } else {
        alert(`❌ Lỗi: ${data.error}`);
      }
    } else {
      const result = data.result || data.choices?.[0]?.message?.content || "Không có kết quả.";
      if (resultBox) {
        resultBox.innerHTML = `
          <div class="result-content">
            <h4>Kết quả:</h4>
            <pre>${escapeHtml(result)}</pre>
            <button class="close-result" onclick="closeResultBox()" title="Đóng">×</button>
          </div>
        `;
      } else {
        alert(`✅ Kết quả:\n\n${result}`);
      }
    }
  } catch (err) {
    console.error('Lỗi API:', err);
    if (resultBox) {
      resultBox.innerHTML = `
        <div class="error-content">
          <h4>Lỗi kết nối:</h4>
          <p>${escapeHtml(err.message)}</p>
          <button class="close-result" onclick="closeResultBox()" title="Đóng">×</button>
        </div>
      `;
    } else {
      alert(`⚠️ Lỗi kết nối: ${err.message}`);
    }
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}