@extends('layouts.app', ['title' => 'De Gouden Draak - Menukaart'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
@endpush

@section('content')
    <section class="menu-card">
        <img
            src="{{ asset('paginas/menukaarten/restaurant-menukaart-1-2.jpg') }}"
            alt="Menukaart pagina 1 en 2"
            class="menu-card-image"
        >

        <img
            src="{{ asset('paginas/menukaarten/restaurant-menukaart-1.jpg') }}"
            alt="Menukaart pagina 3"
            class="menu-card-image"
        >
    </section>

    <div class="bottom-link">
        <a href="{{ route('contact') }}">Naar Contact</a>
    </div>
@endsection
