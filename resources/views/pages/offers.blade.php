@extends('layouts.app', ['title' => 'De Gouden Draak - Aanbiedingen'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/offers.css') }}">
@endpush

@section('content')
    <section class="offers-panel">
        <h2>Aanbiedingen van dit moment</h2>

        @forelse($offers as $offer)
            <article class="offer-card">
                <header class="offer-header">
                    <h3>{{ $offer->titel }}</h3>

                    <p class="offer-period">
                        Geldig van {{ $offer->startdatum->format('d-m-Y') }}
                        t/m {{ $offer->einddatum->format('d-m-Y') }}
                    </p>
                </header>

                @if($offer->beschrijving)
                    <p class="offer-intro">
                        {{ $offer->beschrijving }}
                    </p>
                @endif

                <div class="offer-dishes">
                    @foreach($offer->menuItems as $menuItem)
                        <article class="offer-dish-card">
                            <h4>
                                {{ $menuItem->fullMenuNumber() }}.
                                {{ $menuItem->naam }}
                            </h4>

                            @if($menuItem->beschrijving)
                                <p>{{ $menuItem->beschrijving }}</p>
                            @endif

                            <p class="price-line">
                                <span class="old-price">
                                    Normaal € {{ number_format((float) $menuItem->price, 2, ',', ' ') }}
                                </span>

                                <span class="new-price">
                                    Nu € {{ number_format((float) $menuItem->pivot->actieprijs, 2, ',', ' ') }}
                                </span>
                            </p>
                        </article>
                    @endforeach
                </div>
            </article>
        @empty
            <article class="offer-card">
                <h3>Geen actuele aanbiedingen</h3>

                <p>
                    Er zijn op dit moment geen aanbiedingen actief.
                    Kom later terug voor nieuwe acties.
                </p>
            </article>
        @endforelse
    </section>

    <div class="bottom-link">
        <a href="{{ route('menu') }}">Naar de menukaart</a>
    </div>
@endsection
