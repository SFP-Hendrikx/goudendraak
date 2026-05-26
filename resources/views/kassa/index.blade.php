@extends('layouts.kassa', ['title' => 'GoodPay Kassa'])

@if(session('kassa_logged_in'))
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/kassa/cashdesk.css') }}">
    @endpush

    @push('scripts')
        <script>
            window.kassaPayUrl = "{{ route('kassa.pay') }}";

            window.kassaFilterGroups = @js(
                $menuItems
                    ->groupBy(fn ($item) => $item->soortgerecht ?: 'Overig')
                    ->map(function ($items, $type) {
                        return [
                            'type' => $type,
                            'items' => $items
                                ->map(function ($item) use ($type) {
                                    return [
                                        'searchText' => trim(
                                            ($item->menunummer ?? '') . ' ' .
                                            ($item->menu_toevoeging ?? '') . ' ' .
                                            ($item->naam ?? '') . ' ' .
                                            ($item->beschrijving ?? '') . ' ' .
                                            ($type ?? '')
                                        ),
                                    ];
                                })
                                ->values(),
                        ];
                    })
                    ->values()
            );

            window.kassaCommonRemarks = @js(($commonRemarks ?? collect())->values());

            window.kassaSideDishOptions = @js(($sideDishOptions ?? collect())->values());
        </script>

        @vite('resources/js/kassa/cashdesk.js')
    @endpush
@endif

@section('content')
    @if(!session('kassa_logged_in'))
        <section id="loginDiv">
            <form action="{{ route('kassa.login') }}" method="POST">
                @csrf

                <input
                    type="number"
                    name="employeeNr"
                    placeholder="Medewerker Nummer"
                    min="1"
                    value="{{ old('employeeNr') }}"
                    autofocus
                >

                <br>

                <input
                    type="password"
                    name="password"
                    placeholder="Wachtwoord"
                >

                <br>

                <input type="submit" value="inloggen">
            </form>
        </section>

        @if($errors->any())
            <div class="errorMessage">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('login_error'))
            <div class="errorMessage">
                {{ session('login_error') }}
            </div>
        @endif
    @else
        @php
            $groupedMenuItems = $menuItems->groupBy(fn ($item) => $item->soortgerecht ?: 'Overig');
            $categories = $groupedMenuItems->keys()->values();
        @endphp

        <section id="cashDeskPage" v-cloak>
            <div id="cashDeskLeft">
                <div id="cashDeskFilters">
                    <div class="filterGroup">
                        <label for="menuSearch">Zoeken</label>

                        <input
                            id="menuSearch"
                            type="search"
                            v-model="search"
                            placeholder="Zoek op nummer, naam of beschrijving..."
                        >
                    </div>

                    <div class="filterGroup">
                        <label for="categoryFilter">Categorie</label>

                        <select id="categoryFilter" v-model="category">
                            <option value="all">Alle categorieën</option>

                            @foreach($categories as $category)
                                <option value="{{ $category }}">
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button
                        type="button"
                        id="resetFilters"
                        v-on:click="resetFilters"
                    >
                        Reset filters
                    </button>

                    <p class="filterStatus" v-if="hasActiveFilter">
                        Filter actief
                    </p>
                </div>

                <div id="itemsToSelect">
                    @foreach($groupedMenuItems as $type => $items)
                        <div
                            class="menuItemType"
                            v-show="groupVisible(@js($type))"
                        >
                            {{ $type }}
                        </div>

                        <table
                            class="itemToSelectTable"
                            v-show="groupVisible(@js($type))"
                        >
                            <tbody>
                            @foreach($items as $item)
                                @php
                                    $searchText = trim(
                                        ($item->menunummer ?? '') . ' ' .
                                        ($item->menu_toevoeging ?? '') . ' ' .
                                        ($item->naam ?? '') . ' ' .
                                        ($item->beschrijving ?? '') . ' ' .
                                        ($type ?? '')
                                    );

                                    $displayNumber = trim(($item->menunummer ?? '') . ($item->menu_toevoeging ?? ''));
                                @endphp

                                <tr
                                    v-show="rowVisible(@js($type), @js($searchText))"
                                >
                                    <td>
                                        {{ $displayNumber }}.
                                    </td>

                                    <td>
                                        {{ $item->naam }}

                                        @if($item->beschrijving)
                                            <i>({{ $item->beschrijving }})</i>
                                        @endif
                                    </td>

                                    <td>
                                        € {{ number_format((float) $item->price, 2, ',', ' ') }}
                                    </td>

                                    <td>
                                        <button
                                            type="button"
                                            class="addMenuItem"
                                            data-id="{{ $item->id }}"
                                            data-number="{{ $displayNumber }}"
                                            data-name="{{ $item->naam }}"
                                            data-description="{{ $item->beschrijving }}"
                                            data-price="{{ (float) $item->price }}"
                                        >
                                            Toevoegen
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endforeach

                    <p class="noResults" v-if="!hasResults">
                        Geen gerechten gevonden.
                    </p>
                </div>
            </div>

            <div id="cashDeskRight" v-pre>
                <div id="itemsSelectedContainer">
                    <div id="itemsSelected">
                        <div class="orderHeader">Bestelling</div>

                        <table class="itemSelectedTable">
                            <tbody id="selectedOrderRows"></tbody>
                        </table>
                    </div>

                    <div id="itemsSelectedTotal">
                        <table class="itemSelectedTotalTable">
                            <tbody>
                            <tr>
                                <td></td>

                                <td>
                                    Totaal:
                                </td>

                                <td>
                                    € <span class="totalAmount">0,00</span>
                                </td>

                                <td>
                                    <button type="button" id="payOrder">Afrekenen</button>
                                    <button type="button" id="clearOrder">Verwijderen</button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <p></p>
            </div>
        </div>
    @endif
@endsection
