@extends('layouts.kassa', ['title' => 'GoodPay - Aanbieding bewerken'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kassa/offers-admin.css') }}">
@endpush

@section('content')
    <section id="offersAdminPage">
        <header class="offersAdminHeader">
            <div>
                <h1>Aanbieding bewerken</h1>
                <p>Pas de periode, tekst en gekoppelde gerechten aan.</p>
            </div>

            <a href="{{ route('kassa.aanbiedingen') }}" class="secondaryButton">
                Terug naar aanbiedingen
            </a>
        </header>

        @if($errors->any())
            <div class="errorMessage">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('kassa.aanbiedingen.update', $offer) }}" method="POST" class="offerForm">
            @csrf
            @method('PATCH')

            <section class="offerPanel">
                <h2>Algemene gegevens</h2>

                <div class="formGrid">
                    <div class="formGroup">
                        <label for="titel">Titel</label>
                        <input
                            id="titel"
                            type="text"
                            name="titel"
                            required
                            maxlength="150"
                            value="{{ old('titel', $offer->titel) }}"
                        >
                    </div>

                    <div class="formGroup">
                        <label for="startdatum">Startdatum</label>
                        <input
                            id="startdatum"
                            type="date"
                            name="startdatum"
                            required
                            value="{{ old('startdatum', $offer->startdatum->toDateString()) }}"
                        >
                    </div>

                    <div class="formGroup">
                        <label for="einddatum">Einddatum</label>
                        <input
                            id="einddatum"
                            type="date"
                            name="einddatum"
                            required
                            value="{{ old('einddatum', $offer->einddatum->toDateString()) }}"
                        >
                    </div>

                    <label class="checkboxGroup">
                        <input
                            type="checkbox"
                            name="is_actief"
                            value="1"
                            @checked(old('is_actief', $offer->is_actief))
                        >
                        Actief
                    </label>

                    <div class="formGroup full">
                        <label for="beschrijving">Beschrijving</label>
                        <textarea
                            id="beschrijving"
                            name="beschrijving"
                            rows="3"
                        >{{ old('beschrijving', $offer->beschrijving) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="offerPanel">
                <h2>Gerechten en actieprijzen</h2>

                <div class="offerProductList">
                    @foreach($menuItems as $menuItem)
                        @php
                            $selectedData = $selectedMenuItems->get($menuItem->id);
                            $isSelected = old("menu_items.{$menuItem->id}.selected", $selectedData !== null);
                            $actionPrice = old(
                                "menu_items.{$menuItem->id}.actieprijs",
                                $selectedData ? number_format((float) $selectedData['actieprijs'], 2, ',', '') : ''
                            );
                        @endphp

                        <article class="offerProductRow">
                            <label class="productSelect">
                                <input
                                    type="checkbox"
                                    name="menu_items[{{ $menuItem->id }}][selected]"
                                    value="1"
                                    @checked($isSelected)
                                >

                                <span>
                                    {{ $menuItem->menunummer }}{{ $menuItem->menu_toevoeging }}.
                                    {{ $menuItem->naam }}
                                </span>
                            </label>

                            <span class="normalPrice">
                                Normaal: € {{ number_format((float) $menuItem->price, 2, ',', ' ') }}
                            </span>

                            <label class="actionPrice">
                                Actieprijs
                                <input
                                    type="text"
                                    name="menu_items[{{ $menuItem->id }}][actieprijs]"
                                    placeholder="0,00"
                                    value="{{ $actionPrice }}"
                                >
                            </label>
                        </article>
                    @endforeach
                </div>
            </section>

            <div class="offerActions">
                <button type="submit">
                    Aanbieding opslaan
                </button>

                <a href="{{ route('kassa.aanbiedingen') }}" class="secondaryButton">
                    Annuleren
                </a>
            </div>
        </form>
    </section>
@endsection
