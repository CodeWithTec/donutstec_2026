/**
 * DonutsTecLiberia — Live Chat Poller (v2)
 * - Sends new messages via form (handled elsewhere)
 * - Polls /fetch_messages.php every 3s for new IDs only
 * - Exposes window.DTC_CHAT_POLLER.setLastId(id) so the sender can
 *   tell the poller to skip its own just-sent message.
 */
function dtcPollChat(opts) {
  const {
    endpoint,
    projectId,
    boxId = 'chatBox',
    lastId: initialId = 0,
    interval = 3000,
  } = opts;

  let lastId = initialId;
  let timer = null;
  let inflight = false;

  const box = () => document.getElementById(boxId);

  async function poll() {
    if (inflight || document.hidden) return;
    inflight = true;
    try {
      const res = await fetch(
        `${endpoint}?project_id=${projectId}&last_id=${lastId}`,
        { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' }
      );
      const data = await res.json();
      if (data.success && Array.isArray(data.messages)) {
        const b = box();
        data.messages.forEach(msg => {
          // Skip anything we already rendered (dedupe by id)
          if (msg.id <= lastId) return;
          b.insertAdjacentHTML('beforeend', msg.html);
          lastId = msg.id;
        });
        if (data.messages.length) b.scrollTop = b.scrollHeight;
      }
    } catch (e) { /* silent */ }
    finally { inflight = false; }
  }

  function start() {
    if (timer) return;
    timer = setInterval(poll, interval);
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden) poll();
    });
  }

  start();

  const api = {
    poll,
    stop: () => { clearInterval(timer); timer = null; },
    setLastId: (id) => { if (id > lastId) lastId = id; },
    getLastId: () => lastId,
  };
  window.DTC_CHAT_POLLER = api;
  return api;
}