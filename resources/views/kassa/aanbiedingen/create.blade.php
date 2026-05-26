@extends('layouts.kassa', ['title' => 'GoodPay - Nieuwe aanbieding'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kassa/offers-admin.css') }}">
@endpush

@section('content')
    <section id="offersAdminPage">
        <header class="offersAdminHeader">
            <div>
                <h1>Nieuwe aanbieding</h1>
                <p>Koppel één of meerdere gerechten aan een tijdelijke actieprijs.</p>
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

        <form action="{{ route('kassa.aanbiedingen.store') }}" method="POST" class="offerForm">
            @csrf

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
                            value="{{ old('titel') }}"
                        >
                    </div>

                    <div class="formGroup">
                        <label for="startdatum">Startdatum</label>
                        <input
                            id="startdatum"
                            type="date"
                            name="startdatum"
                            required
                            value="{{ old('startdatum', now()->toDateString()) }}"
                        >
                    </div>

                    <div class="formGroup">
                        <label for="einddatum">Einddatum</label>
                        <input
                            id="einddatum"
                            type="date"
                            name="einddatum"
                            required
                            value="{{ old('einddatum', now()->addDays(6)->toDateString()) }}"
                        >
                    </div>

                    <label class="checkboxGroup">
                        <input
                            type="checkbox"
                            name="is_actief"
                            value="1"
                            checked
                        >
                        Actief
                    </label>

                    <div class="formGroup full">
                        <label for="beschrijving">Beschrijving</label>
                        <textarea
                            id="beschrijving"
                            name="beschrijving"
                            rows="3"
                        >{{ old('beschrijving') }}</textarea>
                    </div>
                </div>
            </section>

            <section class="offerPanel">
                <h2>Gerechten en actieprijzen</h2>

                <div class="offerProductList">
                    @foreach($menuItems as $menuItem)
                        @php
                            $oldSelected = old("menu_items.{$menuItem->id}.selected");
                            $oldActionPrice = old("menu_items.{$menuItem->id}.actieprijs");
                        @endphp

                        <article class="offerProductRow">
                            <label class="productSelect">
                                <input
                                    type="checkbox"
                                    name="menu_items[{{ $menuItem->id }}][selected]"
                                    value="1"
                                    @checked($oldSelected)
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
                                    value="{{ $oldActionPrice }}"
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
