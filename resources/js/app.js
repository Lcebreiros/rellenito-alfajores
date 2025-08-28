import './bootstrap';

import Alpine from 'alpinejs'

window.Alpine = Alpine

// Store global para abrir/cerrar el drawer móvil
Alpine.store('ui', {
  drawer: false
})

Alpine.start()

