@extends('layouts.kassa', ['title' => 'GoodPay - Gerechten beheren'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kassa/gerechten.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/kassa/gerechten.js') }}" defer></script>
@endpush

@section('content')
    <section id="dishesPage">
        <header class="dishesHeader">
            <div>
                <h1>Gerechten beheren</h1>
                <p>Pas bestaande gerechten aan of deactiveer gerechten.</p>
            </div>

            <a href="{{ route('kassa.gerechten.create') }}" class="createButton">
                Nieuw gerecht aanmaken
            </a>
        </header>

        @if(session('success'))
            <div class="successMessage">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="errorMessage">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="errorMessage">
                {{ $errors->first() }}
            </div>
        @endif

        <form
            action="{{ route('kassa.gerechten') }}"
            method="GET"
            class="dishFilterForm"
            data-dish-filter-form
        >
            <div class="formGroup">
                <label for="zoek">Zoeken</label>
                <input
                    id="zoek"
                    type="search"
                    name="zoek"
                    value="{{ request('zoek') }}"
                    placeholder="Zoek op naam, nummer of categorie"
                    autocomplete="off"
                    data-dish-search
                >
            </div>

            <div class="formGroup">
                <label for="categorie">Categorie</label>
                <select id="categorie" name="categorie" data-dish-instant>
                    <option value="">Alle categorieën</option>

                    @foreach($categories as $category)
                        <option value="{{ $category }}" @selected(request('categorie') === $category)>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <label class="checkboxGroup">
                <input
                    type="checkbox"
                    name="toon_inactief"
                    value="1"
                    @checked(request()->boolean('toon_inactief'))
                    data-dish-instant
                >
                Toon inactieve gerechten
            </label>
        </form>

        <section class="dishList">
            <h2>Bestaande gerechten</h2>

            @forelse($menuItems as $menuItem)
                <article class="dishCard {{ ! $menuItem->is_actief ? 'inactive' : '' }}">
                    <form
                        id="updateDish{{ $menuItem->id }}"
                        action="{{ route('kassa.gerechten.update', array_merge(['menuItem' => $menuItem], request()->query())) }}"
                        method="POST"
                        class="dishForm"
                    >
                        @csrf
                        @method('PATCH')

                        <div class="formGrid">
                            <div class="formGroup small">
                                <label for="menunummer{{ $menuItem->id }}">Nummer</label>
                                <input
                                    id="menunummer{{ $menuItem->id }}"
                                    type="number"
                                    name="menunummer"
                                    min="1"
                                    value="{{ $menuItem->menunummer }}"
                                >
                            </div>

                            <div class="formGroup small">
                                <label for="menu_toevoeging{{ $menuItem->id }}">Toevoeging</label>
                                <input
                                    id="menu_toevoeging{{ $menuItem->id }}"
                                    type="text"
                                    name="menu_toevoeging"
                                    maxlength="20"
                                    value="{{ $menuItem->menu_toevoeging }}"
                                >
                            </div>

                            <div class="formGroup">
                                <label for="naam{{ $menuItem->id }}">Naam</label>
                                <input
                                    id="naam{{ $menuItem->id }}"
                                    type="text"
                                    name="naam"
                                    required
                                    value="{{ $menuItem->naam }}"
                                >
                            </div>

                            <div class="formGroup small">
                                <label for="price{{ $menuItem->id }}">Prijs</label>
                                <input
                                    id="price{{ $menuItem->id }}"
                                    type="text"
                                    name="price"
                                    value="{{ $menuItem->price !== null ? number_format((float) $menuItem->price, 2, ',', '') : '' }}"
                                >
                            </div>

                            <div class="formGroup">
                                <label for="soortgerecht{{ $menuItem->id }}">Categorie</label>
                                <select id="soortgerecht{{ $menuItem->id }}" name="soortgerecht">
                                    <option value="">Geen categorie</option>

                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" @selected($menuItem->soortgerecht === $category)>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="formGroup">
                                <label for="nieuwe_soortgerecht{{ $menuItem->id }}">Nieuwe categorie, overschrijft gekozen categorie</label>                                <input
                                    id="nieuwe_soortgerecht{{ $menuItem->id }}"
                                    type="text"
                                    name="nieuwe_soortgerecht"
                                    maxlength="45"
                                    placeholder="Optioneel"
                                >
                            </div>

                            <div class="formGroup full">
                                <label for="beschrijving{{ $menuItem->id }}">Beschrijving</label>
                                <textarea
                                    id="beschrijving{{ $menuItem->id }}"
                                    name="beschrijving"
                                    rows="2"
                                >{{ $menuItem->beschrijving }}</textarea>
                            </div>
                        </div>
                    </form>

                    <div class="dishActions">
                        <button type="submit" form="updateDish{{ $menuItem->id }}">
                            Opslaan
                        </button>

                        @if($menuItem->is_actief)
                            <form
                                action="{{ route('kassa.gerechten.destroy', array_merge(['menuItem' => $menuItem], request()->query())) }}"
                                method="POST"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="dangerButton">
                                    Deactiveren
                                </button>
                            </form>
                        @else
                            <form
                                action="{{ route('kassa.gerechten.restore', array_merge(['menuItem' => $menuItem], request()->query())) }}"
                                method="POST"
                            >
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
                <p class="emptyState">Geen gerechten gevonden.</p>
            @endforelse

            <div class="paginationWrapper">
                {{ $menuItems->links() }}
            </div>
        </section>
    </section>
@endsection
