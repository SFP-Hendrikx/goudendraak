@extends('layouts.app', ['title' => 'De Gouden Draak - Aanbiedingen'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/offers.css') }}">
@endpush

@section('content')
    <section class="offers-panel">
        <h2>Aanbiedingen van deze week</h2>

        <article class="offer-card">
            <h3>Speciale Studentenaanbieding</h3>

            <h4>Chinese Rijsttafel voor 2 personen</h4>

            <p class="offer-intro">
                Maak een keuze uit 3 van onderstaande keuzegerechten:
            </p>

            <div class="offer-dishes">
                <span>Koe Loe Yuk</span>
                <span>Foe Yong Hai</span>
                <span>Tjap Tjoy</span>
                <span>Garnalen met Gebakken Knoflook</span>
                <span>Babi Pangang</span>
                <span>Kipfilet in Zwarte Bonen saus</span>
            </div>

            <p>
                Met witte rijst. Nasi of bami voor meerprijs mogelijk.
            </p>

            <p class="offer-price">
                Prijs: €21,00
            </p>
        </article>
    </section>

    <div class="bottom-link">
        <a href="{{ route('home') }}">Naar Home</a>
    </div>
@endsection
