@extends('layouts.app', ['title' => 'De Gouden Draak - Home'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')
    <section class="student-offer">
        <h2>
            Al jaren is De Gouden Draak een begrip als het gaat om de beste afhaalgerechten
            in 's-Hertogenbosch.
            <br>
            Graag trakteren we u op authentieke gerechten uit de Cantonese keuken.
        </h2>

        <h3>Speciale Studentenaanbieding</h3>

        <h4>Chinese Rijsttafel 2 personen</h4>

        <p>Maak een keuze uit 3 van onderstaande keuzegerechten:</p>

        <div class="dish-grid">
            <span>Koe Loe Yuk</span>
            <span>Foe Yong Hai</span>
            <span>Tjap Tjoy</span>
            <span>Garnalen met Gebakken Knoflook</span>
            <span>Babi Pangang</span>
            <span>Kipfilet in Zwarte Bonen saus</span>
        </div>

        <p>Met witte rijst. Nasi of bami voor meerprijs mogelijk.</p>

        <p class="price">Prijs: €21,00</p>
    </section>

    <div class="bottom-link">
        <a href="{{ route('contact') }}">Naar Contact</a>
    </div>
@endsection
