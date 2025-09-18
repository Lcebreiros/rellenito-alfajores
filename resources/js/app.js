// resources/js/app.js
import './bootstrap'

// ---------- Turbo ----------
import * as Turbo from '@hotwired/turbo'
window.Turbo = Turbo
Turbo.session.drive = false
Turbo.setProgressBarDelay(50)

// ---------- Livewire + Alpine ----------
// 1) Si Alpine ya está en window (CDN u otro bundle), NO lo volvemos a registrar
import Alpine from 'alpinejs'
import collapse from '@alpinejs/collapse'

// Marcas globales para evitar dobles registros al navegar/cargar parciales
if (!window.__ALPINE_WIRED__) {
  // Este callback hace que Livewire arranque Alpine cuando corresponde
  window.deferLoadingAlpine = (callback) => {
    window.addEventListener('livewire:initialized', callback, { once: true })
  }

  // Si ya había Alpine (por CDN), no lo pisamos
  if (!window.Alpine) {
    window.Alpine = Alpine
  }

  // Evitar volver a registrar plugins si volviera a evaluarse el módulo (Turbo cache, etc.)
  if (!window.__ALPINE_PLUGINS_LOADED__) {
    window.Alpine.plugin(collapse)
    window.__ALPINE_PLUGINS_LOADED__ = true
  }

  // Componentes/Stores solo una vez
  if (!window.__ALPINE_COMPONENTS_LOADED__) {
    // Componente global de switch de tema
    window.Alpine.data('themeSwitch', (entangled) => ({
      t: null,
      init() {
        const html = document.documentElement
        const ls = localStorage.getItem('theme')
        this.t = ls ? ls : (html.classList.contains('dark') ? 'dark' : 'light')

        // Adoptar estado Livewire cuando llegue el entangle
        queueMicrotask(() => {
          try { if (entangled != null) this.t = entangled } catch (_) {}
        })

        // Sync Alpine -> Livewire + <html> + localStorage
        this.$watch('t', (v) => {
          if (this.$wire?.setTheme) this.$wire.setTheme(v)
          html.classList.toggle('dark', v === 'dark')
          localStorage.setItem('theme', v)
        })
      },
      toggle() { this.t = (this.t === 'dark' ? 'light' : 'dark') },
    }))

    window.__ALPINE_COMPONENTS_LOADED__ = true
  }

  // Estados sutiles de navegación (una sola vez)
  document.addEventListener('livewire:navigating', () => {
    document.body.classList.add('page-loading')
  }, { once: false })

  document.addEventListener('livewire:navigated', () => {
    document.body.classList.remove('page-loading')
  }, { once: false })

  window.__ALPINE_WIRED__ = true
}

// ⚠️ NO llames Alpine.start() manualmente. Livewire lo hará cuando dispare 'livewire:initialized'.
