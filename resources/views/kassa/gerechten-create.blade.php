@extends('layouts.kassa', ['title' => 'GoodPay - Nieuw gerecht'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kassa/gerechten.css') }}">
@endpush

@section('content')
    <section id="dishesPage">
        <header class="dishesHeader">
            <div>
                <h1>Nieuw gerecht aanmaken</h1>
                <p>Voeg een nieuw gerecht toe aan de menukaart en kassa.</p>
            </div>

            <a href="{{ route('kassa.gerechten') }}" class="secondaryButton">
                Terug naar gerechten
            </a>
        </header>

        @if($errors->any())
            <div class="errorMessage">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="dishCreatePanel">
            <form action="{{ route('kassa.gerechten.store') }}" method="POST" class="dishForm">
                @csrf

                <div class="formGrid">
                    <div class="formGroup small">
                        <label for="menunummer">Nummer</label>
                        <input
                            id="menunummer"
                            type="number"
                            name="menunummer"
                            min="1"
                            value="{{ old('menunummer') }}"
                        >
                    </div>

                    <div class="formGroup small">
                        <label for="menu_toevoeging">Toevoeging</label>
                        <input
                            id="menu_toevoeging"
                            type="text"
                            name="menu_toevoeging"
                            maxlength="20"
                            placeholder="A"
                            value="{{ old('menu_toevoeging') }}"
                        >
                    </div>

                    <div class="formGroup">
                        <label for="naam">Naam</label>
                        <input
                            id="naam"
                            type="text"
                            name="naam"
                            required
                            value="{{ old('naam') }}"
                        >
                    </div>

                    <div class="formGroup small">
                        <label for="price">Prijs</label>
                        <input
                            id="price"
                            type="text"
                            name="price"
                            placeholder="12,50"
                            value="{{ old('price') }}"
                        >
                    </div>

                    <div class="formGroup">
                        <label for="soortgerecht">Categorie</label>
                        <select id="soortgerecht" name="soortgerecht">
                            <option value="">Geen categorie</option>

                            @foreach($categories as $category)
                                <option value="{{ $category }}" @selected(old('soortgerecht') === $category)>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="formGroup">
                        <label for="nieuwe_soortgerecht">Nieuwe categorie</label>
                        <input
                            id="nieuwe_soortgerecht"
                            type="text"
                            name="nieuwe_soortgerecht"
                            maxlength="45"
                            placeholder="Alleen invullen voor nieuwe categorie"
                            value="{{ old('nieuwe_soortgerecht') }}"
                        >
                    </div>

                    <div class="formGroup full">
                        <label for="beschrijving">Beschrijving</label>
                        <textarea
                            id="beschrijving"
                            name="beschrijving"
                            rows="4"
                        >{{ old('beschrijving') }}</textarea>
                    </div>
                </div>

                <div class="dishActions">
                    <button type="submit">
                        Gerecht toevoegen
                    </button>

                    <a href="{{ route('kassa.gerechten') }}" class="secondaryButton">
                        Annuleren
                    </a>
                </div>
            </form>
        </section>
    </section>
@endsection
