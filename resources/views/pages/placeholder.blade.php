@extends('layouts.app', ['title' => 'De Gouden Draak'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/placeholder.css') }}">
@endpush

@section('content')
    <section class="placeholder-page">
        <h1>Pagina in aanbouw</h1>

        <p>
            Deze pagina wordt later omgezet vanuit de oude website naar Laravel.
        </p>

        <a href="{{ route('home') }}">Terug naar home</a>
    </section>
@endsection
