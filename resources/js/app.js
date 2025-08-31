import './bootstrap'

// 👇 Livewire 3 ya incluye Alpine automáticamente
// Solo necesitamos registrar plugins adicionales antes de que Livewire inicie Alpine

import collapse from '@alpinejs/collapse'

// 👇 Hook into Livewire's Alpine initialization
document.addEventListener('livewire:init', () => {
    // Alpine ya está disponible através de Livewire, solo agregamos plugins
    window.Alpine.plugin(collapse)
})

// 👇 ELIMINAR: No iniciamos Alpine manualmente
// import Alpine from 'alpinejs'
// window.Alpine = Alpine
// Alpine.plugin(collapse)
// if (!window.__ALPINE_STARTED__) {
//   Alpine.start()
//   window.__ALPINE_STARTED__ = true
// }