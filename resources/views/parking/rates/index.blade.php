@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between gap-3">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100">Tarifas de estacionamiento</h1>
      <p class="text-sm text-neutral-600 dark:text-neutral-400">Configura fracción y precios sin afectar otros servicios.</p>
    </div>
  </div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-3 sm:px-6 space-y-4">
  @if(session('ok'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif
  @if($errors->any())
    <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-1 container-glass shadow-sm overflow-hidden">
      <div class="px-4 sm:px-6 py-3 bg-neutral-100/70 dark:bg-neutral-800/60 border-b border-neutral-200 dark:border-neutral-700">
        <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Nueva tarifa (rápida)</h2>
        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">Elegí un esquema y ajusta valores sin perderte.</p>
      </div>
      <form method="POST" action="{{ route('parking.rates.store') }}" class="p-4 sm:p-6 space-y-4" x-data="rateCreator()" x-init="setMode('fraction')">
        @csrf

        <div class="space-y-2">
          <label class="block text-sm font-semibold text-neutral-800 dark:text-neutral-100">Nombre</label>
          <input x-model="form.name" name="name" required maxlength="150" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm" placeholder="Ej: Auto por hora">
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-semibold text-neutral-800 dark:text-neutral-100">Tipo de vehículo</label>
          <input x-model="form.vehicle_type" name="vehicle_type" maxlength="50" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm" placeholder="Auto, moto...">
        </div>

        <div class="space-y-2">
          <p class="text-xs font-semibold text-neutral-700 dark:text-neutral-200">Esquema de cobro</p>
          <div class="grid grid-cols-1 gap-2">
            <label @click="setMode('fraction')" class="flex items-start gap-3 rounded-xl border px-3 py-2 cursor-pointer transition
                      " :class="mode === 'fraction' ? 'border-indigo-400 bg-indigo-50/60 dark:bg-indigo-900/20' : 'border-neutral-200 dark:border-neutral-700'">
              <input type="radio" class="mt-1 text-indigo-600" :checked="mode === 'fraction'">
              <div>
                <div class="text-sm font-bold text-neutral-900 dark:text-neutral-50">Fracción simple</div>
                <p class="text-xs text-neutral-600 dark:text-neutral-400">Cobrás por bloques iguales (ej: cada 30 min).</p>
              </div>
            </label>
            <label @click="setMode('block')" class="flex items-start gap-3 rounded-xl border px-3 py-2 cursor-pointer transition"
                   :class="mode === 'block' ? 'border-indigo-400 bg-indigo-50/60 dark:bg-indigo-900/20' : 'border-neutral-200 dark:border-neutral-700'">
              <input type="radio" class="mt-1 text-indigo-600" :checked="mode === 'block'">
              <div>
                <div class="text-sm font-bold text-neutral-900 dark:text-neutral-50">Bloque inicial + fracción</div>
                <p class="text-xs text-neutral-600 dark:text-neutral-400">Un mínimo (1h) y luego fraccionás.</p>
              </div>
            </label>
            <label @click="setMode('day')" class="flex items-start gap-3 rounded-xl border px-3 py-2 cursor-pointer transition"
                   :class="mode === 'day' ? 'border-indigo-400 bg-indigo-50/60 dark:bg-indigo-900/20' : 'border-neutral-200 dark:border-neutral-700'">
              <input type="radio" class="mt-1 text-indigo-600" :checked="mode === 'day'">
              <div>
                <div class="text-sm font-bold text-neutral-900 dark:text-neutral-50">Precio fijo (día/mes)</div>
                <p class="text-xs text-neutral-600 dark:text-neutral-400">Ideal para estadías largas o abonos.</p>
              </div>
            </label>
          </div>
        </div>

        <div class="rounded-lg border border-neutral-200 bg-white p-3 shadow-sm dark:border-neutral-700 dark:bg-neutral-900 space-y-3">
          <div class="rounded-lg border border-amber-100 bg-amber-50 p-3 text-xs text-neutral-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-50">
            <div class="font-bold mb-1">Atajo hora + media hora</div>
            <p class="leading-tight">Si tu esquema es cobrar 1h a un precio y luego cada 30 min a otro, completa estos campos y se configurará automáticamente el bloque inicial de 60 min y la fracción de 30 min.</p>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Precio 1 hora</label>
              <input x-model.number="form.price_hour" name="price_hour" type="number" min="0" step="0.01"
                     class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Precio 30 minutos</label>
              <input x-model.number="form.price_half_hour" name="price_half_hour" type="number" min="0" step="0.01"
                     class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Fracción (min)</label>
              <input x-model.number="form.fraction_minutes" name="fraction_minutes" type="number" min="1" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Precio fracción</label>
              <input x-model.number="form.price_per_fraction" name="price_per_fraction" type="number" min="0" step="0.01" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-show="mode === 'block'">
            <div>
              <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Bloque inicial (min)</label>
              <input x-model.number="form.initial_block_minutes" name="initial_block_minutes" type="number" min="1" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Precio bloque inicial</label>
              <input x-model.number="form.initial_block_price" name="initial_block_price" type="number" min="0" step="0.01" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
              <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Precio por hora (después del bloque) - opcional</label>
              <input x-model.number="form.hour_price" name="hour_price" type="number" min="0" step="0.01" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm" placeholder="Si está vacío, usa fracciones">
              <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1">Después del bloque inicial, cobrar por horas completas a este precio antes de fraccionar.</p>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-show="mode === 'day'">
            <div>
              <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Precio 24h</label>
              <input x-model.number="form.day_price" name="day_price" type="number" min="0" step="0.01" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Mensual / Semana (opcional)</label>
              <div class="grid grid-cols-2 gap-2">
                <input x-model.number="form.week_price" name="week_price" type="number" min="0" step="0.01" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-2.5 py-2 text-sm" placeholder="Semana">
                <input x-model.number="form.month_price" name="month_price" type="number" min="0" step="0.01" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-2.5 py-2 text-sm" placeholder="Mes">
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-show="mode !== 'day'">
            <div>
              <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">12 horas (opcional)</label>
              <input x-model.number="form.half_day_price" name="half_day_price" type="number" min="0" step="0.01" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            </div>
            <div>
              <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">24 horas (opcional)</label>
              <input x-model.number="form.day_price" name="day_price" type="number" min="0" step="0.01" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
            </div>
          </div>

          <div class="flex items-center gap-2">
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-neutral-700 dark:text-neutral-200">
              <input x-model="form.is_active" type="checkbox" name="is_active" value="1" class="rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500 dark:border-neutral-700">
              Activa
            </label>
            <span class="text-[11px] text-neutral-500 dark:text-neutral-400">La podés desactivar si solo querés guardarla.</span>
          </div>

          <div class="rounded-lg bg-indigo-50/80 p-3 text-xs text-neutral-800 border border-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-50 dark:border-indigo-800">
            <div class="font-bold mb-1">Resumen rápido</div>
            <p x-text="summary()" class="leading-tight"></p>
          </div>
        </div>

        <div class="flex justify-end">
          <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-semibold transition">
            Guardar
          </button>
        </div>
      </form>
    </div>

    <div class="lg:col-span-2 container-glass shadow-sm overflow-hidden">
      <div class="px-4 sm:px-6 py-3 bg-neutral-100/70 dark:bg-neutral-800/60 border-b border-neutral-200 dark:border-neutral-700">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Tarifas configuradas</h2>
          <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $rates->count() }} tarifas</span>
        </div>
      </div>
      @if($rates->isEmpty())
        <p class="p-6 text-sm text-neutral-500 dark:text-neutral-400">Aún no hay tarifas cargadas.</p>
      @else
        <div class="divide-y divide-neutral-100 dark:divide-neutral-800">
          @foreach($rates as $rate)
            <div class="p-4 sm:p-5">
              <form method="POST" action="{{ route('parking.rates.update', $rate) }}" class="space-y-3">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nombre</label>
                    <input name="name" value="{{ $rate->name }}" required maxlength="150"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Tipo de vehículo</label>
                    <input name="vehicle_type" value="{{ $rate->vehicle_type }}" maxlength="50"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                  <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-neutral-600 dark:text-neutral-300">Activa</label>
                    <input type="checkbox" name="is_active" value="1" @checked($rate->is_active)
                           class="rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500 dark:border-neutral-700">
                  </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Fracción (min)</label>
                    <input name="fraction_minutes" type="number" min="1" value="{{ $rate->fraction_minutes }}"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Precio fracción</label>
                    <input name="price_per_fraction" type="number" min="0" step="0.01" value="{{ $rate->price_per_fraction }}"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Bloque inicial (min)</label>
                    <input name="initial_block_minutes" type="number" min="1" value="{{ $rate->initial_block_minutes }}"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Precio bloque inicial</label>
                    <input name="initial_block_price" type="number" min="0" step="0.01" value="{{ $rate->initial_block_price }}"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Precio por hora (después del bloque)</label>
                    <input name="hour_price" type="number" min="0" step="0.01" value="{{ $rate->hour_price }}"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                           placeholder="Opcional">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">12 horas</label>
                    <input name="half_day_price" type="number" min="0" step="0.01" value="{{ $rate->half_day_price }}"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">24 horas</label>
                    <input name="day_price" type="number" min="0" step="0.01" value="{{ $rate->day_price }}"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Semana</label>
                    <input name="week_price" type="number" min="0" step="0.01" value="{{ $rate->week_price }}"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Mes</label>
                    <input name="month_price" type="number" min="0" step="0.01" value="{{ $rate->month_price }}"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div class="sm:col-span-3">
                    <div class="rounded-lg border border-amber-100 bg-amber-50 p-3 text-xs text-neutral-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-50">
                      <div class="font-bold mb-1">Atajo hora + media hora</div>
                      <p class="leading-tight">Completar estos valores ajusta el bloque inicial a 60 min y la fracción a 30 min.</p>
                    </div>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Precio 1 hora</label>
                    <input name="price_hour" type="number" min="0" step="0.01"
                           value="{{ ($rate->initial_block_minutes == 60) ? $rate->initial_block_price : '' }}"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Precio 30 minutos</label>
                    <input name="price_half_hour" type="number" min="0" step="0.01"
                           value="{{ ($rate->fraction_minutes == 30) ? $rate->price_per_fraction : '' }}"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-neutral-900 hover:bg-neutral-800 text-white px-4 py-2 text-sm font-semibold transition">
                    Guardar cambios
                  </button>
                </div>
              </form>
              <form method="POST" action="{{ route('parking.rates.destroy', $rate) }}" onsubmit="return confirm('¿Eliminar tarifa?');" class="mt-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-rose-600 hover:text-rose-700 font-semibold">Eliminar</button>
              </form>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

<script>
  function rateCreator() {
    return {
      mode: 'fraction',
      form: {
        name: '',
        vehicle_type: '',
        fraction_minutes: 30,
        price_per_fraction: 0,
        price_hour: '',
        price_half_hour: '',
        initial_block_minutes: '',
        initial_block_price: '',
        half_day_price: '',
        day_price: '',
        week_price: '',
        month_price: '',
        is_active: true,
      },
      setMode(mode) {
        this.mode = mode;
        if (mode === 'fraction') {
          this.form.fraction_minutes = 30;
          this.form.price_per_fraction = this.form.price_per_fraction || 0;
          this.form.initial_block_minutes = '';
          this.form.initial_block_price = '';
        }
        if (mode === 'block') {
          this.form.initial_block_minutes = this.form.initial_block_minutes || 60;
          this.form.initial_block_price = this.form.initial_block_price || 0;
          this.form.fraction_minutes = this.form.fraction_minutes || 30;
          this.form.price_per_fraction = this.form.price_per_fraction || 0;
        }
        if (mode === 'day') {
          this.form.day_price = this.form.day_price || 0;
        }
      },
      summary() {
        const fmt = (n) => {
          const num = Number(n || 0);
          return '$ ' + num.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        if (this.mode === 'fraction') {
          return `Cada ${this.form.fraction_minutes || 0} min: ${fmt(this.form.price_per_fraction)}. Opcional: 12h ${fmt(this.form.half_day_price)} / 24h ${fmt(this.form.day_price)}.`;
        }
        if (this.mode === 'block') {
          return `Mínimo ${this.form.initial_block_minutes || 0} min por ${fmt(this.form.initial_block_price)} y luego cada ${this.form.fraction_minutes || 0} min: ${fmt(this.form.price_per_fraction)}.`;
        }
        return `Precio 24h: ${fmt(this.form.day_price)}. Semana ${fmt(this.form.week_price)} / Mes ${fmt(this.form.month_price)}.`;
      }
    };
  }
</script>
