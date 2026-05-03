(function () {
  if (window.__HID_SCANNER_LOADED__) return;
  window.__HID_SCANNER_LOADED__ = true;

  const MIN_CHARS    = 4;
  const MAX_TOTAL_MS = 1500; // tiempo máximo desde primer char hasta Enter

  let buffer    = '';
  let firstTime = 0;
  let lastTime  = 0;

  document.addEventListener('keydown', function (e) {
    const now = Date.now();

    if (e.key === 'Enter') {
      const code  = buffer.trim();
      const total = now - firstTime;
      buffer    = '';
      firstTime = 0;
      lastTime  = 0;

      if (code.length >= MIN_CHARS && total > 0 && total <= MAX_TOTAL_MS) {
        e.preventDefault();
        window.dispatchEvent(new CustomEvent('hid-barcode', {
          detail: { code },
          bubbles: true,
        }));
      }
      return;
    }

    if (e.key.length !== 1) return;

    // Resetear si hubo una pausa larga (el usuario dejó de tipear)
    if (lastTime > 0 && now - lastTime > MAX_TOTAL_MS) {
      buffer    = '';
      firstTime = 0;
    }

    if (!buffer) firstTime = now;
    buffer   += e.key;
    lastTime  = now;
  }, true); // capture phase: intercepta antes que otros listeners
})();
