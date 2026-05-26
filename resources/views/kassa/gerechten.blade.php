@extends('layouts.kassa', ['title' => 'GoodPay - Gerechten'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kassa/gerechten.css') }}">
@endpush

@section('content')
    <section id="menuPage">
        <img
            src="{{ asset('paginas/menukaarten/restaurant-menukaart-1-2.jpg') }}"
            alt="Menukaart pagina 1 en 2"
        >

        <img
            src="{{ asset('paginas/menukaarten/restaurant-menukaart-1.jpg') }}"
            alt="Menukaart pagina 3"
        >
    </section>
@endsection
