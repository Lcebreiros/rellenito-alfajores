@extends('layouts.app')

@section('title', __('company.branch_create_page_title'))

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold mb-2">{{ __('company.branch_create_heading') }}</h1>
        <p class="text-gray-600">{{ __('company.branch_create_desc') }}</p>
    </div>

    {{-- Incluir el componente Livewire --}}
    @livewire('company.branch-create')
</div>
@endsection
