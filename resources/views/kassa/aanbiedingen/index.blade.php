@extends('layouts.kassa', ['title' => 'GoodPay - Aanbiedingen beheren'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kassa/offers-admin.css') }}">
@endpush

@section('content')
    <section id="offersAdminPage">
        <header class="offersAdminHeader">
            <div>
                <h1>Aanbiedingen beheren</h1>
                <p>Maak tijdelijke prijsaanpassingen voor één of meerdere gerechten.</p>
            </div>

            <a href="{{ route('kassa.aanbiedingen.create') }}" class="primaryButton">
                Nieuwe aanbieding
            </a>
        </header>

        @if(session('success'))
            <div class="successMessage">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="errorMessage">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('kassa.aanbiedingen') }}" method="GET" class="offerFilterForm">
            <div class="formGroup">
                <label for="zoek">Zoeken</label>
                <input
                    id="zoek"
                    type="search"
                    name="zoek"
                    value="{{ request('zoek') }}"
                    placeholder="Zoek op titel of beschrijving"
                >
            </div>

            <label class="checkboxGroup">
                <input
                    type="checkbox"
                    name="toon_inactief"
                    value="1"
                    @checked(request()->boolean('toon_inactief'))
                >
                Toon inactieve aanbiedingen
            </label>

            <button type="submit">Filteren</button>

            <a href="{{ route('kassa.aanbiedingen') }}" class="secondaryButton">
                Reset
            </a>
        </form>

        <section class="offerList">
            @forelse($offers as $offer)
                <article class="offerAdminCard {{ ! $offer->is_actief ? 'inactive' : '' }}">
                    <div>
                        <h2>{{ $offer->titel }}</h2>

                        <p>
                            Geldig van
                            <strong>{{ $offer->startdatum->format('d-m-Y') }}</strong>
                            t/m
                            <strong>{{ $offer->einddatum->format('d-m-Y') }}</strong>
                        </p>

                        <p>
                            {{ $offer->menu_items_count }}
                            {{ $offer->menu_items_count === 1 ? 'gerecht' : 'gerechten' }}
                            gekoppeld.
                        </p>

                        @if($offer->beschrijving)
                            <p>{{ $offer->beschrijving }}</p>
                        @endif

                        @if(! $offer->is_actief)
                            <p class="inactiveLabel">Inactief</p>
                        @endif
                    </div>

                    <div class="offerActions">
                        <a href="{{ route('kassa.aanbiedingen.edit', $offer) }}" class="secondaryButton">
                            Bewerken
                        </a>

                        @if($offer->is_actief)
                            <form action="{{ route('kassa.aanbiedingen.destroy', $offer) }}" method="POST">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="dangerButton">
                                    Deactiveren
                                </button>
                            </form>
                        @else
                            <form action="{{ route('kassa.aanbiedingen.restore', $offer) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="secondaryButton">
                                    Herstellen
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <p class="emptyState">Geen aanbiedingen gevonden.</p>
            @endforelse

            <div class="paginationWrapper">
                {{ $offers->links() }}
            </div>
        </section>
    </section>
@endsection
