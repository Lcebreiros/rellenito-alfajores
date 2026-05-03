(function () {
  if (window.__HID_SCANNER_LOADED__) return;
  window.__HID_SCANNER_LOADED__ = true;

  const MIN_CHARS     = 4;
  const MAX_MS_CHAR   = 50;   // Subimos a 50ms por si es un scanner Bluetooth
  const MAX_TOTAL_MS  = 800;  

  let buffer      = '';
  let firstTime   = 0;
  let lastTime    = 0;

  document.addEventListener('keydown', (e) => {
    const now = Date.now();

    // Si el usuario está escribiendo en un textarea o un input de texto normal, 
    // quizás quieras ignorar el scanner para no ensuciar lo que escribe.
    if (e.target.tagName === 'TEXTAREA' || (e.target.tagName === 'INPUT' && e.target.type === 'text')) {
       // Opcional: podrías dejarlo pasar si querés que el scanner llene ese input.
    }

    if (e.key === 'Enter') {
      const total    = now - firstTime;
      const avgPerCh = buffer.length > 0 ? total / buffer.length : 9999;

      if (buffer.length >= MIN_CHARS && avgPerCh <= MAX_MS_CHAR && total <= MAX_TOTAL_MS) {
        // 100% es un scanner:
        e.preventDefault(); // 👈 EVITA EL SCROLL y el envío de formularios accidentales
        
        const scannedCode = buffer.trim();
        window.dispatchEvent(new CustomEvent('hid-barcode', {
          detail: { code: scannedCode },
          bubbles: true,
        }));
        
        console.log("Scanner detectado en Helipso:", scannedCode);
      }

      // Reset siempre al final del Enter
      buffer    = '';
      firstTime = 0;
      lastTime  = 0;
      return;
    }

    if (e.key.length !== 1) return;

    if (buffer.length === 0) {
      firstTime = now;
    } else if (now - lastTime > MAX_MS_CHAR) {
      buffer    = '';
      firstTime = now;
    }

    buffer   += e.key;
    lastTime  = now;
  }, true); // 👈 Usamos 'true' (capture phase) para atraparlo antes que otros elementos
})();