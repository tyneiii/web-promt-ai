// Updated run_api.js - Fixed delegation and target handling
let currentPrompt = '';

// Lấy elements
const promptModal = document.getElementById("prompt-modal");
const resultBox = document.getElementById("resultBox");
const promptInput = document.getElementById("promptInput");

// Utility: Escape HTML
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Event listeners
document.addEventListener("DOMContentLoaded", function() {
  console.log('Script loaded!'); // Debug

  // Event delegation: Use closest to handle clicks on children
  document.addEventListener("click", function(event) {
    const btn = event.target.closest(".run-btn");
    if (btn) {
      console.log('Button clicked:', btn); // Debug
      openPromptModal({ target: event.target, currentTarget: btn }); // Pass event with correct currentTarget
    }
  });
  
  console.log('Attached to run-btn delegation'); // Debug

  // Overlay đóng prompt modal
  if (promptModal) {
    promptModal.addEventListener("click", function(event) {
      if (event.target.classList.contains("modal-overlay")) {
        closePromptModal(event);
      }
    });
  }

  // Overlay đóng result modal
  if (resultBox) {
    resultBox.addEventListener("click", function(event) {
      if (event.target === resultBox) {
        closeResultBox(event);
      }
    });
  }
});

function openPromptModal(event) {
  console.log('openPromptModal called'); // Debug
  
  // Use currentTarget or closest to get the button
  const btn = event.currentTarget || event.target.closest(".run-btn");
  if (!btn) {
    console.error("Button not found!");
    return;
  }
  
  let promptText = btn.dataset.prompt || btn.getAttribute('data-prompt');
  console.log('promptText loaded:', promptText ? promptText.substring(0, 100) + '...' : 'EMPTY!'); // Debug
  
  if (!promptText || promptText.trim() === '') {
    alert("⚠️ Không tìm thấy prompt! Kiểm tra data-prompt trong HTML.");
    return;
  }
  
  currentPrompt = promptText;
  
  if (promptInput) {
    promptInput.value = promptText;
    promptInput.focus();
    console.log('Set to textarea OK');
  } else {
    console.error("Không tìm thấy #promptInput!");
    return;
  }
  
  if (promptModal) {
    promptModal.style.display = "flex";
    document.body.style.overflow = "hidden"; // Prevent scroll
    console.log('Prompt modal opened');
  } else {
    console.error("Không tìm thấy #prompt-modal!");
  }
}

function closePromptModal(event) {
  if (event) event.stopPropagation();
  if (promptModal) {
    promptModal.style.display = "none";
    document.body.style.overflow = ""; // Restore scroll
  }
  if (promptInput) promptInput.value = '';
}

// Mở result modal
function openResultBox() {
  if (resultBox) {
    resultBox.style.display = "flex";
    document.body.style.overflow = "hidden";
    console.log('Result modal opened');
  }
}

// Đóng result modal
window.closeResultBox = function(event) {
  if (event) event.stopPropagation();
  if (resultBox) {
    resultBox.style.display = "none";
    resultBox.innerHTML = ''; // Clear nội dung
    document.body.style.overflow = "";
  }
};

async function confirmRunPrompt() {
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
      <div style="display: flex; justify-content: center; align-items: center; height: 100vh; background: rgba(0,0,0,0.8);">
        <div class="loading" style="text-align: center; color: white;">
          <i class="fa fa-spinner fa-spin" style="font-size: 2em; color: #ff4d4d;"></i><br>
          Đang chạy prompt...
        </div>
      </div>
    `;
    openResultBox();
  } else {
    alert("⏳ Đang chạy prompt...");
  }

  try {
    const response = await fetch("/web-promt-ai/api/run_api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ prompt }),
    });

    console.log('Response status:', response.status); // Debug

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`HTTP ${response.status}: ${errorText}`);
    }

    const data = await response.json();
    console.log('API Response:', data);

    if (data.error) {
      showResultError(data.error);
    } else {
      const result = data.result || data.choices?.[0]?.message?.content || "Không có kết quả.";
      showResultSuccess(result);
    }
  } catch (err) {
    console.error('Lỗi API:', err);
    showResultError(err.message);
  }
}

// Helper: Hiển thị error
function showResultError(errorMsg) {
  if (resultBox) {
    resultBox.innerHTML = `
      <div style="padding: 20px; max-width: 600px; margin: auto; background: #333; color: #ff4d4d; border-radius: 8px; position: relative;">
        <h4>Lỗi:</h4>
        <pre style="white-space: pre-wrap;">${escapeHtml(errorMsg)}</pre>
        <button class="close-result" onclick="closeResultBox()" title="Đóng">×</button>
      </div>
    `;
  } else {
    alert(`❌ Lỗi: ${errorMsg}`);
  }
}

// Helper: Hiển thị success
function showResultSuccess(result) {
  if (resultBox) {
    resultBox.innerHTML = `
      <div style="padding: 20px; max-width: 600px; margin: auto; background: #111; color: white; border-radius: 8px; position: relative;">
        <h4 style="color: #4dff4d;">Kết quả:</h4>
        <pre style="white-space: pre-wrap; background: #222; padding: 10px; border-radius: 4px;">${escapeHtml(result)}</pre>
        <button class="close-result" onclick="closeResultBox()" title="Đóng">×</button>
      </div>
    `;
  } else {
    alert(`✅ Kết quả:\n\n${result}`);
  }
}