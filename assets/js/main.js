/* DonutsTecLiberia — Public site JS */

document.addEventListener('DOMContentLoaded', function () {
  /* ---------- Newsletter subscribe (AJAX) ---------- */
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
      } catch (err) {
        nlMsg.textContent = 'Network error. Please try again.';
        nlMsg.className = 'small mt-2 text-danger';
      }
    });
  }

  /* ---------- Portfolio filters ---------- */
  const filterButtons = document.querySelectorAll('[data-portfolio-filter]');
  const portfolioItems = document.querySelectorAll('[data-portfolio-category]');
  filterButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const cat = btn.dataset.portfolioFilter;
      filterButtons.forEach(b => b.classList.remove('btn-dtc-primary', 'active'));
      filterButtons.forEach(b => b.classList.add('btn-dtc-outline'));
      btn.classList.remove('btn-dtc-outline');
      btn.classList.add('btn-dtc-primary', 'active');
      portfolioItems.forEach(item => {
        const show = cat === 'all' || item.dataset.portfolioCategory === cat;
        item.style.display = show ? '' : 'none';
      });
    });
  });
});