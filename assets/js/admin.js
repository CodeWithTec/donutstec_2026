/* DonutsTecLiberia — Admin JS */

document.addEventListener('DOMContentLoaded', () => {
  /* ---------- AJAX send message (admin side) ---------- */
  const chatForm = document.getElementById('adminChatForm');
  if (chatForm) {
    chatForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const ta = chatForm.querySelector('textarea[name="body"]');
      if (!ta.value.trim()) return;
      const fd = new FormData(chatForm);
      const res = await fetch(chatForm.action, { method: 'POST', body: fd });
      const data = await res.json();
      if (data.success) {
        const box = document.getElementById('adminChatBox');
        box.insertAdjacentHTML('beforeend', data.html);
        box.scrollTop = box.scrollHeight;
        ta.value = '';
      } else alert(data.message);
    });
  }

  /* ---------- Auto-submit filters ---------- */
  document.querySelectorAll('[data-autosubmit]').forEach(el => {
    el.addEventListener('change', () => el.form.submit());
  });

  /* ---------- Confirm deletes ---------- */
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

  /* ---------- Copy-to-clipboard ---------- */
  document.querySelectorAll('[data-copy]').forEach(btn => {
    btn.addEventListener('click', () => {
      navigator.clipboard.writeText(btn.dataset.copy);
      const old = btn.innerHTML;
      btn.innerHTML = '<i class="bi bi-check2"></i>';
      setTimeout(() => btn.innerHTML = old, 1200);
    });
  });

  /* ---------- Chat auto-scroll ---------- */
  const chatBox = document.getElementById('adminChatBox');
  if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
});