<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Crear cuenta • Gestior</title>

  {{-- Fuentes --}}
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

  {{-- Vite --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    :root{
      --bg-deep-1:#0b1020; --bg-deep-2:#0f172a; --stroke: rgba(168,85,247,.22);
      --violet:#7c3aed; --violet-500:#8b5cf6; --violet-400:#a78bfa;
    }
    body{ font-family:'Inter',sans-serif; letter-spacing:-0.012rem; }
    [x-cloak]{display:none!important}

    /* Fondo abstracto degradé */
    .abstract-bg{
      position: fixed; inset:0; z-index:-1;
      background:
        radial-gradient(1200px 600px at 90% 110%, rgba(37,99,235,.22), transparent 60%),
        radial-gradient(900px 500px at -10% -20%, rgba(91,33,182,.24), transparent 60%),
        linear-gradient(180deg, var(--bg-deep-2) 0%, var(--bg-deep-1) 100%);
    }

    .card{
      border-radius: 1.5rem; overflow: hidden;
      box-shadow: 0 30px 80px rgba(0,0,0,.45), 0 0 0 1px rgba(255,255,255,.04) inset;
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
      backdrop-filter: blur(2px);
    }

    .focus-ring:focus{ box-shadow:0 0 0 3px rgba(124,58,237,.16); outline:none; }
    .txt{ transition: box-shadow .18s ease, transform .08s ease; }
    .btn{ transition: transform .18s ease, box-shadow .18s ease, background-color .2s; }
    .btn:hover{ transform: translateY(-1px); }
    .btn:active{ transform: translateY(0); }

    /* Plan cards */
    .plan-card{
      cursor: pointer;
      transition: all 0.2s ease;
      border: 2px solid transparent;
    }
    .plan-card:hover{
      border-color: rgba(124,58,237,.3);
      transform: translateY(-2px);
    }
    .plan-card.selected{
      border-color: #7c3aed;
      background: linear-gradient(135deg, rgba(124,58,237,.08), rgba(139,92,246,.06));
    }

    /* Business type cards */
    .business-card{
      cursor: pointer;
      transition: all 0.2s ease;
      border: 2px solid transparent;
    }
    .business-card:hover{
      border-color: rgba(124,58,237,.3);
      transform: translateY(-2px);
    }
    .business-card.selected{
      border-color: #7c3aed;
      background: linear-gradient(135deg, rgba(124,58,237,.08), rgba(139,92,246,.06));
    }
  </style>

  @livewireStyles
</head>
<body class="h-full" x-data="{
  step: 1,
  businessType: '',
  plan: '',
  name: '',
  email: '',
  password: '',
  passwordConfirmation: '',

  nextStep() {
    if(this.step < 3) this.step++;
  },
  prevStep() {
    if(this.step > 1) this.step--;
  },
  selectBusinessType(type) {
    this.businessType = type;
  },
  selectPlan(planType) {
    this.plan = planType;
  },
  canProceedStep1() {
    return this.businessType !== '';
  },
  canProceedStep2() {
    return this.plan !== '';
  },
  canProceedStep3() {
    return this.name !== '' && this.email !== '' && this.password !== '' && this.passwordConfirmation !== '';
  }
}">
  <div class="abstract-bg"></div>

  <div class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-4xl">

      {{-- Logo --}}
      <div class="text-center mb-8">
        <img src="{{ asset('images/Gestior.png') }}" alt="Gestior" class="h-16 w-auto mx-auto select-none" />
      </div>

      <div class="card bg-white p-8 md:p-10">

        {{-- Progress Indicator --}}
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all"
                     :class="step >= 1 ? 'bg-violet-600 border-violet-600 text-white' : 'border-slate-300 text-slate-400'">
                  <span class="text-sm font-semibold">1</span>
                </div>
                <div class="flex-1 h-1 mx-2 transition-all"
                     :class="step > 1 ? 'bg-violet-600' : 'bg-slate-200'"></div>
              </div>
            </div>
            <div class="flex-1">
              <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all"
                     :class="step >= 2 ? 'bg-violet-600 border-violet-600 text-white' : 'border-slate-300 text-slate-400'">
                  <span class="text-sm font-semibold">2</span>
                </div>
                <div class="flex-1 h-1 mx-2 transition-all"
                     :class="step > 2 ? 'bg-violet-600' : 'bg-slate-200'"></div>
              </div>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-end">
                <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all"
                     :class="step >= 3 ? 'bg-violet-600 border-violet-600 text-white' : 'border-slate-300 text-slate-400'">
                  <span class="text-sm font-semibold">3</span>
                </div>
              </div>
            </div>
          </div>
          <div class="flex items-center justify-between mt-3 text-xs font-medium">
            <span :class="step >= 1 ? 'text-violet-600' : 'text-slate-400'">Tipo de negocio</span>
            <span :class="step >= 2 ? 'text-violet-600' : 'text-slate-400'">Plan</span>
            <span :class="step >= 3 ? 'text-violet-600' : 'text-slate-400'">Datos personales</span>
          </div>
        </div>

        {{-- Errores --}}
        @if ($errors->any())
          <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
            <ul class="text-sm text-red-600 space-y-1">
              @foreach ($errors->all() as $error)
                <li>• {{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('register.wizard.store') }}">
          @csrf

          {{-- STEP 1: Business Type --}}
          <div x-show="step === 1" x-cloak>
            <div class="mb-6">
              <h2 class="text-2xl font-semibold tracking-tight text-slate-900 mb-2">¿Qué tipo de negocio tienes?</h2>
              <p class="text-sm text-slate-500">Selecciona el tipo que mejor describe tu actividad</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
              {{-- Comercio/Tienda --}}
              <div class="business-card p-6 bg-white rounded-xl border-2"
                   :class="businessType === 'comercio' ? 'selected' : 'border-slate-200'"
                   @click="selectBusinessType('comercio')">
                <div class="flex items-start gap-4">
                  <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <h3 class="text-lg font-semibold text-slate-900 mb-1">Comercio / Tienda</h3>
                    <p class="text-sm text-slate-600">Gestiona productos, ventas, stock y pedidos para tu negocio</p>
                    <ul class="mt-3 space-y-1.5 text-xs text-slate-500">
                      <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-violet-500" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Control de inventario
                      </li>
                      <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-violet-500" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Gestión de ventas
                      </li>
                      <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-violet-500" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Reportes de productos
                      </li>
                    </ul>
                  </div>
                  <div class="flex-shrink-0">
                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                         :class="businessType === 'comercio' ? 'border-violet-600 bg-violet-600' : 'border-slate-300'">
                      <svg x-show="businessType === 'comercio'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Alquiler/Estacionamiento --}}
              <div class="business-card p-6 bg-white rounded-xl border-2"
                   :class="businessType === 'alquiler' ? 'selected' : 'border-slate-200'"
                   @click="selectBusinessType('alquiler')">
                <div class="flex items-start gap-4">
                  <div class="flex-shrink-0 w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <h3 class="text-lg font-semibold text-slate-900 mb-1">Alquiler / Estacionamiento</h3>
                    <p class="text-sm text-slate-600">Administra espacios, servicios, turnos y cobros de estacionamiento</p>
                    <ul class="mt-3 space-y-1.5 text-xs text-slate-500">
                      <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-violet-500" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Control de espacios
                      </li>
                      <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-violet-500" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Gestión de turnos
                      </li>
                      <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-violet-500" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Panel de operarios
                      </li>
                    </ul>
                  </div>
                  <div class="flex-shrink-0">
                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                         :class="businessType === 'alquiler' ? 'border-violet-600 bg-violet-600' : 'border-slate-300'">
                      <svg x-show="businessType === 'alquiler'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <input type="hidden" name="business_type" :value="businessType">

            <button type="button"
                    @click="nextStep()"
                    :disabled="!canProceedStep1()"
                    :class="canProceedStep1() ? 'bg-violet-600 hover:bg-violet-700 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                    class="btn w-full py-3 px-4 rounded-lg font-semibold focus:ring-2 focus:ring-violet-700 focus:ring-offset-2 shadow-lg transition-all">
              Siguiente: Elegir plan →
            </button>
          </div>

          {{-- STEP 2: Plan Selection --}}
          <div x-show="step === 2" x-cloak>
            <div class="mb-6">
              <h2 class="text-2xl font-semibold tracking-tight text-slate-900 mb-2">Elige tu plan</h2>
              <p class="text-sm text-slate-500">Selecciona el plan que mejor se adapte a tus necesidades</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
              {{-- Plan Básico --}}
              <div class="plan-card p-5 bg-white rounded-xl border-2"
                   :class="plan === 'basic' ? 'selected' : 'border-slate-200'"
                   @click="selectPlan('basic')">
                <div class="flex items-start justify-between mb-3">
                  <div>
                    <h3 class="text-lg font-bold text-slate-900">Básico</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Para empezar</p>
                  </div>
                  <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                       :class="plan === 'basic' ? 'border-violet-600 bg-violet-600' : 'border-slate-300'">
                    <svg x-show="plan === 'basic'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </div>
                <div class="mb-4">
                  <span class="text-2xl font-bold text-slate-900">Gratis</span>
                </div>
                <ul class="space-y-2 text-xs text-slate-600">
                  <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Funcionalidades básicas</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>1 sucursal</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Soporte por email</span>
                  </li>
                </ul>
              </div>

              {{-- Plan Premium --}}
              <div class="plan-card p-5 bg-white rounded-xl border-2 relative"
                   :class="plan === 'premium' ? 'selected' : 'border-slate-200'"
                   @click="selectPlan('premium')">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                  <span class="px-3 py-1 bg-gradient-to-r from-violet-500 to-purple-500 text-white text-xs font-bold rounded-full shadow-lg">
                    Recomendado
                  </span>
                </div>
                <div class="flex items-start justify-between mb-3">
                  <div>
                    <h3 class="text-lg font-bold text-slate-900">Premium</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Más popular</p>
                  </div>
                  <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                       :class="plan === 'premium' ? 'border-violet-600 bg-violet-600' : 'border-slate-300'">
                    <svg x-show="plan === 'premium'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </div>
                <div class="mb-4">
                  <span class="text-2xl font-bold text-slate-900">Gratis</span>
                </div>
                <ul class="space-y-2 text-xs text-slate-600">
                  <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Todo del plan Básico</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Hasta 3 sucursales</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Reportes avanzados</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Soporte prioritario</span>
                  </li>
                </ul>
              </div>

              {{-- Plan Enterprise --}}
              <div class="plan-card p-5 bg-white rounded-xl border-2"
                   :class="plan === 'enterprise' ? 'selected' : 'border-slate-200'"
                   @click="selectPlan('enterprise')">
                <div class="flex items-start justify-between mb-3">
                  <div>
                    <h3 class="text-lg font-bold text-slate-900">Enterprise</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Para empresas</p>
                  </div>
                  <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                       :class="plan === 'enterprise' ? 'border-violet-600 bg-violet-600' : 'border-slate-300'">
                    <svg x-show="plan === 'enterprise'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </div>
                <div class="mb-4">
                  <span class="text-2xl font-bold text-slate-900">Gratis</span>
                </div>
                <ul class="space-y-2 text-xs text-slate-600">
                  <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Todo del plan Premium</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Sucursales ilimitadas</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>API completa</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-violet-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Soporte dedicado 24/7</span>
                  </li>
                </ul>
              </div>
            </div>

            <input type="hidden" name="plan" :value="plan">

            <div class="flex gap-3">
              <button type="button"
                      @click="prevStep()"
                      class="btn flex-1 py-3 px-4 rounded-lg font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 transition-all">
                ← Anterior
              </button>
              <button type="button"
                      @click="nextStep()"
                      :disabled="!canProceedStep2()"
                      :class="canProceedStep2() ? 'bg-violet-600 hover:bg-violet-700 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                      class="btn flex-1 py-3 px-4 rounded-lg font-semibold focus:ring-2 focus:ring-violet-700 focus:ring-offset-2 shadow-lg transition-all">
              Siguiente: Datos personales →
              </button>
            </div>
          </div>

          {{-- STEP 3: Personal Data --}}
          <div x-show="step === 3" x-cloak>
            <div class="mb-6">
              <h2 class="text-2xl font-semibold tracking-tight text-slate-900 mb-2">Datos personales</h2>
              <p class="text-sm text-slate-500">Completa tus datos para finalizar el registro</p>
            </div>

            <div class="space-y-5 mb-8">
              {{-- Nombre --}}
              <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre completo</label>
                <input id="name" name="name" type="text" x-model="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="txt focus-ring w-full px-3 py-2.5 border border-slate-200 rounded-md bg-white placeholder-slate-400"
                       placeholder="Juan Pérez">
              </div>

              {{-- Email --}}
              <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Correo electrónico</label>
                <div class="relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 7a2 2 0 012-2h14a2 2 0 012 2v.217a2 2 0 01-.894 1.664l-7 4.667a2 2 0 01-2.212 0l-7-4.667A2 2 0 013 7.217V7z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7"/>
                    </svg>
                  </span>
                  <input id="email" name="email" type="email" x-model="email" value="{{ old('email') }}" required autocomplete="username"
                         class="txt focus-ring w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-md bg-white placeholder-slate-400"
                         placeholder="juan@empresa.com">
                </div>
              </div>

              {{-- Password --}}
              <div x-data="{showPass:false}">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña</label>
                <div class="relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 11V7a4 4 0 10-8 0v4m-2 0h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2z"/>
                    </svg>
                  </span>
                  <input id="password" name="password" :type="showPass ? 'text' : 'password'" x-model="password" required autocomplete="new-password"
                         class="txt focus-ring w-full pl-10 pr-10 py-2.5 border border-slate-200 rounded-md bg-white placeholder-slate-400"
                         placeholder="••••••••">
                  <button type="button" @click="showPass=!showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg x-show="!showPass" class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      <circle cx="12" cy="12" r="3" stroke-width="1.5"/>
                    </svg>
                    <svg x-show="showPass" x-cloak class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 3l18 18M10.477 10.477A3 3 0 0012 15c1.657 0 3-1.343 3-3a3 3 0 00-3-3c-.525 0-1.02.135-1.45.373M9.88 9.88L6.343 6.343M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m3.32-2.91A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.186 2.592"/>
                    </svg>
                  </button>
                </div>
              </div>

              {{-- Confirm Password --}}
              <div x-data="{showPass2:false}">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Confirmar contraseña</label>
                <div class="relative">
                  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 11V7a4 4 0 10-8 0v4m-2 0h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2z"/>
                    </svg>
                  </span>
                  <input id="password_confirmation" name="password_confirmation" :type="showPass2 ? 'text' : 'password'" x-model="passwordConfirmation" required autocomplete="new-password"
                         class="txt focus-ring w-full pl-10 pr-10 py-2.5 border border-slate-200 rounded-md bg-white placeholder-slate-400"
                         placeholder="••••••••">
                  <button type="button" @click="showPass2=!showPass2" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg x-show="!showPass2" class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      <circle cx="12" cy="12" r="3" stroke-width="1.5"/>
                    </svg>
                    <svg x-show="showPass2" x-cloak class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 3l18 18M10.477 10.477A3 3 0 0012 15c1.657 0 3-1.343 3-3a3 3 0 00-3-3c-.525 0-1.02.135-1.45.373M9.88 9.88L6.343 6.343M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542 7a9.97 9.97 0 011.563-3.029m3.32-2.91A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.186 2.592"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            {{-- Info box --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
              <div class="flex gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-blue-800">
                  <p class="font-semibold mb-1">¿Qué sucede después?</p>
                  <ul class="space-y-1 text-blue-700">
                    <li>• Revisaremos tu solicitud</li>
                    <li>• Te enviaremos las credenciales de acceso por email</li>
                    <li>• Podrás comenzar a usar Gestior inmediatamente</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="flex gap-3">
              <button type="button"
                      @click="prevStep()"
                      class="btn flex-1 py-3 px-4 rounded-lg font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 transition-all">
                ← Anterior
              </button>
              <button type="submit"
                      :disabled="!canProceedStep3()"
                      :class="canProceedStep3() ? 'bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                      class="btn flex-1 py-3 px-4 rounded-lg font-semibold focus:ring-2 focus:ring-violet-700 focus:ring-offset-2 shadow-lg transition-all">
                Solicitar acceso gratis
              </button>
            </div>

            <div class="text-center text-sm text-slate-500 mt-4">
              <a href="{{ route('login') }}" class="font-medium text-violet-700 hover:underline">
                ¿Ya tienes cuenta? Inicia sesión
              </a>
            </div>
          </div>
        </form>
      </div>

      <div class="text-center mt-6">
        <p class="text-xs text-slate-400">&copy; {{ date('Y') }} Gestior — Todos los derechos reservados.</p>
      </div>
    </div>
  </div>

  @livewireScripts
</body>
</html>
