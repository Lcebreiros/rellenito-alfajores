{{-- MOBILE: barra superior --}}
<div class="md:hidden w-full bg-white border-b">
    <div class="h-14 flex items-center justify-between px-4">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2">
            <x-application-mark class="h-8 w-auto" />
            <span class="font-semibold">Panel</span>
        </a>
        {{-- Aquí puedes agregar un botón hamburguesa más adelante --}}
    </div>
</div>