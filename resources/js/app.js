// resources/js/app.js
import './bootstrap'

// --- Turbo (solo barra de progreso; desactivamos Drive para que NO intercepte links) ---
import * as Turbo from '@hotwired/turbo'
window.Turbo = Turbo
Turbo.session.drive = false              // ⬅️ clave: que no tome los <a>
Turbo.setProgressBarDelay(50)

// --- Livewire 3 + Alpine ---
// Registramos Alpine y lo arrancamos DESPUÉS de Livewire (evita carreras con @entangle)
import Alpine from 'alpinejs'
import collapse from '@alpinejs/collapse'

// Livewire llama este callback cuando está listo; Alpine arranca recién ahí
window.deferLoadingAlpine = (callback) => {
  window.addEventListener('livewire:initialized', callback)
}
window.Alpine = Alpine

// Componente global para el switch (más robusto)
function themeSwitchFactory(entangled) {
  return {
    t: null,
    init() {
      const html = document.documentElement
      // 1) Estado inmediato (sin parpadeo)
      const ls = localStorage.getItem('theme')
      this.t = ls ? ls : (html.classList.contains('dark') ? 'dark' : 'light')

      // 2) Adoptar valor Livewire cuando esté (entangle)
      queueMicrotask(() => {
        try {
          if (typeof entangled !== 'undefined' && entangled !== null) this.t = entangled
        } catch (_) {}
      })

      // 3) Sincronización Alpine → Livewire + <html> + localStorage
      this.$watch('t', (v) => {
        if (this.$wire?.setTheme) this.$wire.setTheme(v)
        html.classList.toggle('dark', v === 'dark')
        localStorage.setItem('theme', v)
      })
    },
    toggle() { this.t = (this.t === 'dark' ? 'light' : 'dark') },
  }
}

// Registro de plugins y componentes cuando Alpine inicializa
document.addEventListener('alpine:init', () => {
  Alpine.plugin(collapse)
  Alpine.data('themeSwitch', themeSwitchFactory)
})

// --- Estados sutiles de navegación con Livewire Navigate ---
document.addEventListener('livewire:navigating', () => {
  document.body.classList.add('page-loading')
})
document.addEventListener('livewire:navigated', () => {
  document.body.classList.remove('page-loading')
})

// --- Sortable global (si lo usás en Blade) ---
import Sortable from 'sortablejs'
window.Sortable = Sortable

// ⚠️ No llames Alpine.start() manual. Livewire lo hace cuando corresponde.