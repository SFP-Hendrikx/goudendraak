@extends('layouts.app', ['title' => 'De Gouden Draak - Nieuws'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/news.css') }}">
@endpush

@section('content')
    <section class="content-panel news-panel">
        <h2>
            Door de Corona crisis is De Gouden Draak op het moment slechts beperkt open.
            <br>
            Het restaurant-gedeelte is gesloten. U kan uw favoriete gerechten nog wel afhalen.
        </h2>
    </section>

    <div class="bottom-link">
        <a href="{{ route('contact') }}">Naar Contact</a>
    </div>
@endsection
