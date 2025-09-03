<div
  class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm dark:shadow-slate-900/40 ring-1 ring-slate-200/70 dark:ring-slate-700 h-full flex flex-col p-4 sm:p-5 transition-colors"
  x-data="balanceBars({
    labels: @js($labels),
    primary: @js($earnings),
    secondary: @js($expenses),
    primaryLabel: 'Ganancias',
    secondaryLabel: 'Gastos',
    unit: 'Productos'
  })"
  x-init="init()"
  :class="compact ? 'p-3' : 'p-4 sm:p-5'"
>
  {{-- Header --}}
  <div class="flex items-center justify-between mb-3 sm:mb-4 shrink-0">
    <h4
      class="font-semibold text-slate-800 dark:text-slate-100"
      :class="compact ? 'text-[13px]' : 'text-sm sm:text-base'"
    >
      Balance
    </h4>
  </div>

  {{-- CHART --}}
  <div class="relative flex-1 min-h-[120px]" :style="micro ? 'min-height:100px' : (compact ? 'min-height:120px' : 'min-height:160px')">
    <canvas x-ref="canvas" class="w-full h-full"></canvas>

    @php $hasData = (collect($earnings)->sum() + collect($expenses)->sum()) > 0; @endphp
    @unless($hasData)
      <div class="absolute inset-0 grid place-items-center text-xs sm:text-sm text-slate-500 dark:text-slate-400">
        Aún no hay datos suficientes para graficar.
      </div>
    @endunless
  </div>

  {{-- Resumen --}}
  @php
    $totalEarnings = isset($totalEarnings) ? $totalEarnings : collect($earnings)->sum();
    $totalExpenses = isset($totalExpenses) ? $totalExpenses : collect($expenses)->sum();
    $balance = $totalEarnings - $totalExpenses;
  @endphp
  <div
    class="mt-3 sm:mt-4 grid gap-2 sm:gap-3 shrink-0"
    :class="compact ? 'grid-cols-3' : 'grid-cols-1 sm:grid-cols-3'"
    x-show="!micro"
    x-transition.opacity
  >
    <div class="rounded-xl p-2 sm:p-3 ring-1 ring-emerald-200/60 dark:ring-emerald-900/40 bg-emerald-50/60 dark:bg-emerald-900/20">
      <div class="text-[11px] sm:text-[12px] font-medium text-emerald-700 dark:text-emerald-300">Ganancias</div>
      <div class="text-base sm:text-lg font-semibold text-emerald-800 dark:text-emerald-200">$ {{ number_format($totalEarnings, 2, ',', '.') }}</div>
    </div>
    <div class="rounded-xl p-2 sm:p-3 ring-1 ring-slate-200/60 dark:ring-slate-800 bg-slate-50/60 dark:bg-slate-800/40">
      <div class="text-[11px] sm:text-[12px] font-medium text-slate-700 dark:text-slate-300">Gastos</div>
      <div class="text-base sm:text-lg font-semibold text-slate-800 dark:text-slate-200">$ {{ number_format($totalExpenses, 2, ',', '.') }}</div>
    </div>
    <div class="rounded-xl p-2 sm:p-3 ring-1 ring-slate-200/70 dark:ring-slate-700 bg-white dark:bg-slate-900">
      <div class="text-[11px] sm:text-[12px] font-medium text-slate-700 dark:text-slate-300">Balance</div>
      <div class="text-base sm:text-lg font-semibold {{ $balance >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
        $ {{ number_format($balance, 2, ',', '.') }}
      </div>
    </div>
  </div>
</div>

<script>
function balanceBars({ labels, primary, secondary, primaryLabel, secondaryLabel, unit }) {
  return {
    chart: null,
    compact: false,
    micro: false,
    prefersDark: window.matchMedia('(prefers-color-scheme: dark)'),
    ro: null,

    isDark() {
      return document.documentElement.classList.contains('dark') || this.prefersDark.matches;
    },
    colors() {
      const dark = this.isDark();
      return {
        blue: '#3b82f6',
        blueBorder: '#3b82f6',
        // grises ajustados para dark: un poco más suaves pero legibles
        grey: dark ? 'rgba(148,163,184,0.42)' : 'rgba(148,163,184,0.55)',
        greyBorder: dark ? 'rgba(148,163,184,0.62)' : 'rgba(148,163,184,0.75)',
        grid: dark ? 'rgba(148,163,184,0.18)' : 'rgba(148,163,184,0.22)',
        xtick: dark ? '#e2e8f0' : '#475569',
        ytick: dark ? '#e2e8f0' : '#475569',
        legend: dark ? '#f1f5f9' : '#334155',
        tooltipBg: dark ? 'rgba(2,6,23,0.94)' : 'rgba(15,23,42,0.92)',
      };
    },
    abbr(n) {
      n = Number(n || 0);
      const abs = Math.abs(n);
      if (abs >= 1e9) return (n/1e9).toFixed(1).replace('.0','') + 'B';
      if (abs >= 1e6) return (n/1e6).toFixed(1).replace('.0','') + 'M';
      if (abs >= 1e3) return (n/1e3).toFixed(1).replace('.0','') + 'K';
      return n.toString();
    },
    fmt(n) { return new Intl.NumberFormat('es-AR').format(Number(n || 0)); },

    externalTooltip(ctx) {
      const { chart, tooltip } = ctx;
      let el = chart.canvas.parentNode.querySelector('.ext-tooltip');
      if (!el) {
        el = document.createElement('div');
        el.className = 'ext-tooltip';
        el.style.cssText = `
          position:absolute; pointer-events:none; transform:translate(-50%, -120%);
          background:${this.colors().tooltipBg}; color:#fff;
          padding:${this.compact ? '6px 8px' : '8px 10px'};
          border-radius:12px; box-shadow:0 10px 24px rgba(0,0,0,.35);
          border:1px solid rgba(148,163,184,.2);
          font-size:${this.compact ? '11px' : '12px'}; line-height:1.1;
        `;
        const dot = document.createElement('div');
        dot.style.cssText = 'position:absolute; top:100%; left:50%; width:8px; height:8px; background:inherit; transform:translate(-50%,-2px) rotate(45deg); border-radius:2px; border:1px solid rgba(148,163,184,.2)';
        el.appendChild(dot);
        const list = document.createElement('div');
        list.className = 'items';
        el.appendChild(list);
        chart.canvas.parentNode.appendChild(el);
      }
      if (tooltip.opacity === 0) { el.style.opacity = 0; return; }

      const items = el.querySelector('.items');
      items.innerHTML = '';
      tooltip.dataPoints.forEach(dp => {
        const row = document.createElement('div');
        row.style.cssText = 'display:flex; align-items:center; gap:8px; margin:4px 0;';
        const color = document.createElement('span');
        color.style.cssText = `width:8px;height:8px;border-radius:9999px;background:${dp.dataset.borderColor || dp.dataset.backgroundColor}`;
        const text = document.createElement('div');
        text.innerHTML = `<span style="font-weight:700">${this.fmt(dp.raw)}</span> <span style="opacity:.85">${unit}</span>`;
        row.appendChild(color); row.appendChild(text);
        items.appendChild(row);
      });

      const {offsetLeft: posX, offsetTop: posY} = chart.canvas;
      el.style.opacity = 1;
      el.style.left = posX + tooltip.caretX + 'px';
      el.style.top  = posY + tooltip.caretY + 'px';
    },

    render() {
      const ctx = this.$refs.canvas.getContext('2d');
      const c = this.colors();

      const showLegend = !this.compact;
      const barRadius  = this.compact ? 8 : 12;
      const maxBarBlue = this.compact ? 16 : 22;
      const maxBarGrey = this.compact ? 12 : 18;
      const catPct     = this.compact ? 0.65 : 0.7;

      this.chart?.destroy();
      this.chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              label: secondaryLabel,
              data: secondary,
              backgroundColor: c.grey,
              borderColor: c.greyBorder,
              borderWidth: 1,
              borderRadius: barRadius,
              borderSkipped: false,
              maxBarThickness: maxBarGrey,
              categoryPercentage: catPct,
              barPercentage: this.compact ? 0.42 : 0.45,
              order: 1
            },
            {
              label: primaryLabel,
              data: primary,
              backgroundColor: c.blue,
              borderColor: c.blueBorder,
              borderWidth: 0,
              borderRadius: barRadius,
              borderSkipped: false,
              maxBarThickness: maxBarBlue,
              categoryPercentage: catPct,
              barPercentage: this.compact ? 0.65 : 0.7,
              order: 2
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: { duration: 500, easing: 'easeOutQuart' },
          layout: { padding: this.compact ? 4 : 8 },
          plugins: {
            legend: {
              display: showLegend,
              position: 'top',
              align: 'start',
              labels: {
                color: c.legend,
                usePointStyle: true,
                pointStyle: 'circle',
                boxWidth: 8,
                font: { size: 12, weight: 600 }
              }
            },
            tooltip: {
              enabled: false,
              external: (ctx) => this.externalTooltip(ctx)
            }
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: { color: c.xtick, font: { size: this.compact ? 11 : 12 } }
            },
            y: {
              beginAtZero: true,
              grid: { color: this.micro ? 'transparent' : c.grid, drawBorder: false },
              ticks: {
                color: c.ytick,
                callback: v => this.abbr(v),
                font: { size: this.compact ? 10 : 11 }
              }
            }
          }
        }
      });
    },

    init() {
      const ensure = (cb) => {
        if (!window.Chart) {
          const s = document.createElement('script');
          s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
          s.onload = cb; document.head.appendChild(s);
        } else cb();
      };

      ensure(() => {
        this.render();

        // Modo compacto/micro por altura
        const setMode = (h) => {
          this.compact = h < 260;
          this.micro   = h < 190;
        };
        this.ro = new ResizeObserver((entries) => {
          const h = entries[0]?.contentRect?.height || 0;
          const wasCompact = this.compact, wasMicro = this.micro;
          setMode(h);
          if (wasCompact !== this.compact || wasMicro !== this.micro) this.render();
        });
        this.ro.observe(this.$root);

        // Redibujar al cambiar tema (clase 'dark' o preferencia del SO)
        const rerender = () => this.render();
        const mo = new MutationObserver(rerender);
        mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        this.prefersDark.addEventListener('change', rerender);
      });
    }
  }
}
</script>
