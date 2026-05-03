/**
 * HID Barcode Scanner Detector
 *
 * Physical USB/Bluetooth scanners work as HID keyboards: they emit characters
 * very rapidly (< 30ms apart) followed by Enter. This module detects that
 * pattern and dispatches a `hid-barcode` CustomEvent on window.
 *
 * Usage: import './hid-scanner' in app.js, then listen to the event:
 *   window.addEventListener('hid-barcode', e => console.log(e.detail.code))
 *
 * Or in Alpine: x-on:hid-barcode.window="handler($event.detail.code)"
 */
(function () {
  if (window.__HID_SCANNER_LOADED__) return;
  window.__HID_SCANNER_LOADED__ = true;

  const MIN_CHARS     = 4;    // barcodes shorter than this are ignored
  const MAX_MS_CHAR   = 40;   // max ms between chars to be considered scanner
  const MAX_TOTAL_MS  = 600;  // if the whole sequence takes longer, it's manual

  let buffer      = '';
  let firstTime   = 0;
  let lastTime    = 0;

  document.addEventListener('keydown', (e) => {
    const now = Date.now();

    // ── Enter: evaluate what's in the buffer ──────────────────────────
    if (e.key === 'Enter') {
      const code     = buffer;
      const total    = now - firstTime;
      const avgPerCh = buffer.length > 0 ? total / buffer.length : 9999;

      buffer    = '';
      firstTime = 0;
      lastTime  = 0;

      if (code.length >= MIN_CHARS && avgPerCh <= MAX_MS_CHAR && total <= MAX_TOTAL_MS) {
        // Looks like scanner input — fire event
        window.dispatchEvent(new CustomEvent('hid-barcode', {
          detail: { code: code.trim() },
          bubbles: true,
        }));
        // Don't preventDefault: some inputs need Enter handled normally
      }
      return;
    }

    // ── Ignore non-printable keys ─────────────────────────────────────
    if (e.key.length !== 1) {
      // A non-printable key in the middle resets the buffer
      if (lastTime > 0 && now - lastTime > MAX_MS_CHAR * 2) {
        buffer    = '';
        firstTime = 0;
        lastTime  = 0;
      }
      return;
    }

    // ── Accumulate ────────────────────────────────────────────────────
    if (buffer.length === 0) {
      firstTime = now;
    } else if (now - lastTime > MAX_MS_CHAR) {
      // Gap too long — this is manual typing, reset
      buffer    = '';
      firstTime = now;
    }

    buffer   += e.key;
    lastTime  = now;
  });
})();
