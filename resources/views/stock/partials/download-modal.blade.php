<div id="downloadModal"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-title">
  <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 max-w-md w-full border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-2xl">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
      <h3 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-neutral-100 flex items-center">
        <i class="fas fa-download text-emerald-600 dark:text-emerald-400 mr-2" aria-hidden="true"></i>
        Descargar reporte
      </h3>
      <button type="button"
              id="closeModal"
              aria-label="Cerrar modal"
              class="text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 transition-colors">
        <i class="fas fa-times text-xl" aria-hidden="true"></i>
      </button>
    </div>

    {{-- Descripción --}}
    <p class="text-gray-600 dark:text-neutral-300 mb-4">
      Selecciona el formato para guardar localmente:
      @if($branchId)
        <br><small class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
          <i class="fas fa-info-circle mr-1" aria-hidden="true"></i>
          Se exportará solo la sucursal actual
        </small>
      @elseif($isCompanyView)
        <br><small class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
          <i class="fas fa-info-circle mr-1" aria-hidden="true"></i>
          Se exportará vista consolidada de la empresa
        </small>
      @endif
    </p>

    {{-- Opciones de descarga --}}
    <div class="space-y-3">
      {{-- CSV --}}
      <a href="{{ route('stock.export.csv', request()->query()) }}"
         class="group w-full flex items-center justify-between p-3.5 border rounded-lg transition-all
                border-gray-300 hover:bg-gray-50 hover:border-gray-400
                dark:border-neutral-600 dark:hover:bg-neutral-800 dark:hover:border-neutral-500
                focus:outline-none focus:ring-2 focus:ring-green-500">
        <div class="flex items-center">
          <div class="bg-green-100 dark:bg-emerald-500/10 p-2.5 rounded-lg mr-3 group-hover:scale-110 transition-transform">
            <i class="fas fa-file-csv text-green-600 dark:text-emerald-300 text-lg" aria-hidden="true"></i>
          </div>
          <div class="text-left">
            <div class="font-medium text-gray-900 dark:text-neutral-100">CSV</div>
            <div class="text-sm text-gray-500 dark:text-neutral-400">Abrilo con Excel / Google Sheets</div>
          </div>
        </div>
        <i class="fas fa-download text-gray-400 dark:text-neutral-500 group-hover:text-green-600 dark:group-hover:text-emerald-400 transition-colors" aria-hidden="true"></i>
      </a>

      {{-- PDF --}}
      <button type="button"
              onclick="window.print(); document.getElementById('downloadModal').classList.add('hidden'); document.body.style.overflow = '';"
              class="group w-full flex items-center justify-between p-3.5 border rounded-lg transition-all
                     border-gray-300 hover:bg-gray-50 hover:border-gray-400
                     dark:border-neutral-600 dark:hover:bg-neutral-800 dark:hover:border-neutral-500
                     focus:outline-none focus:ring-2 focus:ring-blue-500">
        <div class="flex items-center">
          <div class="bg-blue-100 dark:bg-blue-500/10 p-2.5 rounded-lg mr-3 group-hover:scale-110 transition-transform">
            <i class="fas fa-file-pdf text-blue-600 dark:text-blue-300 text-lg" aria-hidden="true"></i>
          </div>
          <div class="text-left">
            <div class="font-medium text-gray-900 dark:text-neutral-100">PDF</div>
            <div class="text-sm text-gray-500 dark:text-neutral-400">Usa "Guardar como PDF" al imprimir</div>
          </div>
        </div>
        <i class="fas fa-print text-gray-400 dark:text-neutral-500 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" aria-hidden="true"></i>
      </button>
    </div>

    {{-- Info adicional --}}
    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-500/10 rounded-lg border border-blue-100 dark:border-blue-900/30">
      <div class="flex items-start">
        <i class="fas fa-info-circle text-blue-500 dark:text-blue-300 mt-0.5 mr-2 flex-shrink-0" aria-hidden="true"></i>
        <div class="text-sm text-blue-700 dark:text-blue-200">
          Se exportan los productos con los filtros actuales aplicados.
        </div>
      </div>
    </div>
  </div>
</div>
