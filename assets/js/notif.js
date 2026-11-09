/**
 * DonutsTecLiberia — Notification bell poller.
 * Expects HTML:
 *   <a id="notifBell" href="#" ...>
 *     <i class="bi bi-bell"></i>
 *     <span id="notifBadge" class="badge">0</span>
 *   </a>
 *   <div id="notifDropdown"></div>
 */
(function () {
  const BASE = window.DTC_BASE_URL || '';
  const bell = document.getElementById('notifBell');
  const badge = document.getElementById('notifBadge');
  const dropdown = document.getElementById('notifDropdown');
  if (!bell || !badge) return;

  async function refresh() {
    try {
      const res = await fetch(`${BASE}/ajax/notifications.php`);
      const data = await res.json();
      if (!data.success) return;

      // Update badge
      if (data.unread > 0) {
        badge.textContent = data.unread > 9 ? '9+' : data.unread;
        badge.style.display = 'inline-block';
      } else {
        badge.style.display = 'none';
      }

      // Update dropdown list
      if (dropdown) {
        if (!data.notifications.length) {
          dropdown.innerHTML = '<div class="p-3 text-muted small text-center">No notifications</div>';
        } else {
          dropdown.innerHTML = data.notifications.map(n =>
            `<a href="${n.link || '#'}" class="dropdown-item py-2 ${n.is_read ? '' : 'bg-light'}" data-notif-id="${n.id}">
               <div class="fw-semibold small">${escapeHtml(n.title)}</div>
               <div class="small text-muted text-truncate">${escapeHtml(n.message || '')}</div>
               <div class="small text-muted">${n.time_ago}</div>
             </a>`
          ).join('') +
          `<div class="dropdown-divider"></div>
           <button class="dropdown-item text-center small" id="markAllRead">Mark all as read</button>`;
        }
      }
    } catch (e) { /* silent */ }
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[m]));
  }

  // Mark single as read when clicked
  document.addEventListener('click', async (e) => {
    const link = e.target.closest('[data-notif-id]');
    if (link && !link.classList.contains('bg-light')) {
      const fd = new FormData();
      fd.append('id', link.dataset.notifId);
      await fetch(`${BASE}/ajax/read_notification.php`, { method:'POST', body:fd });
    }
    if (e.target.id === 'markAllRead') {
      e.preventDefault();
      const fd = new FormData(); fd.append('all','1');
      await fetch(`${BASE}/ajax/read_notification.php`, { method:'POST', body:fd });
      refresh();
    }
  });

  refresh();
  setInterval(refresh, 15000); // every 15s
})();