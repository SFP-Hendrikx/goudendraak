@extends('layouts.app', ['title' => 'De Gouden Draak - Aanbiedingen'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/offers.css') }}">
@endpush

@section('content')
    <section class="offers-panel">
        <h2>Aanbiedingen van deze week</h2>

        @forelse($offers as $offer)
            <article class="offer-card">
                <h3>{{ $offer->titel }}</h3>

                @if($offer->beschrijving)
                    <p class="offer-intro">
                        {{ $offer->beschrijving }}
                    </p>
                @endif

                <p class="offer-period">
                    Geldig van {{ $offer->startdatum->format('d-m-Y') }}
                    t/m {{ $offer->einddatum->format('d-m-Y') }}
                </p>

                <div class="offer-dishes">
                    @foreach($offer->menuItems as $menuItem)
                        <div class="offer-dish-card">
                            <h4>
                                {{ $menuItem->menunummer }}{{ $menuItem->menu_toevoeging }}.
                                {{ $menuItem->naam }}
                            </h4>

                            @if($menuItem->beschrijving)
                                <p>{{ $menuItem->beschrijving }}</p>
                            @endif

                            <p class="priceLine">
                                <span class="oldPrice">
                                    Normaal € {{ number_format((float) $menuItem->price, 2, ',', ' ') }}
                                </span>

                                <span class="newPrice">
                                    Nu € {{ number_format((float) $menuItem->pivot->actieprijs, 2, ',', ' ') }}
                                </span>
                            </p>
                        </div>
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
        <a href="{{ route('home') }}">Naar Home</a>
    </div>
@endsection
