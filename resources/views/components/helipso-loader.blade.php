{{-- Helipso constellation loader. palette: auto|brand|cosmos|violet|fuchsia|white|ink --}}
@props(['palette' => 'auto', 'size' => '64px', 'cycle' => 7000, 'stroke' => 1.2])

<div {{ $attributes->only('class') }}
     style="width:{{ $size }};height:{{ $size }};display:inline-block;flex-shrink:0;"
     x-data="{
       _hl: null,
       init() {
         if (!window.initHelipsoLoader) return;
         var pal = '{{ $palette }}';
         if (pal === 'auto') pal = document.documentElement.classList.contains('dark') ? 'cosmos' : 'violet';
         this._hl = window.initHelipsoLoader(this.$el.querySelector('svg'), {
           palette: pal,
           cycleMs: {{ $cycle }},
           strokeW: {{ $stroke }}
         });
       },
       destroy() { this._hl && this._hl.destroy(); }
     }">
  <svg viewBox="0 0 100 100" style="width:100%;height:100%;display:block;overflow:visible;"></svg>
</div>
