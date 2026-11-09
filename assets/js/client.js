/* DonutsTecLiberia — Client portal JS (v2) */

document.addEventListener('DOMContentLoaded', () => {

  /* ---------- Auto-scroll chat ---------- */
  const chatBox = document.getElementById('chatBox');
  if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

  /* ---------- AJAX chat send (client) ---------- */
  const chatForm = document.getElementById('chatForm');
  if (chatForm) {
    const textarea = chatForm.querySelector('textarea[name="body"]');
    const sendBtn  = chatForm.querySelector('button[type="submit"]');
    let sending = false;

    chatForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (sending) return;

      const body = textarea.value.trim();
      if (!body) return;

      sending = true;
      sendBtn.disabled = true;
      const originalBtn = sendBtn.innerHTML;
      sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

      try {
        const fd = new FormData(chatForm);
        const res = await fetch(chatForm.action, {
          method: 'POST',
          body: fd,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        if (data.success) {
          // Clear textarea FIRST (immediate feedback)
          textarea.value = '';
          textarea.style.height = 'auto';

          // Append the message HTML returned by server
          if (data.html) {
            const box = document.getElementById('chatBox');
            box.insertAdjacentHTML('beforeend', data.html);
            box.scrollTop = box.scrollHeight;

            // Tell the poller the last known id to avoid duplicates
            if (data.id && window.DTC_CHAT_POLLER) {
              window.DTC_CHAT_POLLER.setLastId(data.id);
            }
          }
        } else {
          alert(data.message || 'Failed to send.');
        }
      } catch (err) {
        alert('Network error. Please retry.');
      } finally {
        sending = false;
        sendBtn.disabled = false;
        sendBtn.innerHTML = originalBtn;
        textarea.focus();
      }
    });

    // Allow Enter to send, Shift+Enter for new line
    textarea.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        chatForm.requestSubmit();
      }
    });

    // Auto-grow textarea
    textarea.addEventListener('input', () => {
      textarea.style.height = 'auto';
      textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    });
  }

  /* ---------- Newsletter (public site) ---------- */
  const nlForm = document.getElementById('newsletterForm');
  const nlMsg  = document.getElementById('newsletterMsg');
  if (nlForm && nlMsg) {
    nlForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      const email = nlForm.querySelector('input[name="email"]').value.trim();
      if (!email) return;
      nlMsg.textContent = 'Subscribing...';
      nlMsg.className = 'small mt-2 text-info';
      try {
        const res = await fetch(nlForm.dataset.action || 'subscribe.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'email=' + encodeURIComponent(email)
        });
        const data = await res.json();
        nlMsg.textContent = data.message || (data.success ? 'Subscribed!' : 'Please try again.');
        nlMsg.className = 'small mt-2 ' + (data.success ? 'text-success' : 'text-danger');
        if (data.success) nlForm.reset();
      } catch {
        nlMsg.textContent = 'Network error.';
        nlMsg.className = 'small mt-2 text-danger';
      }
    });
  }
});