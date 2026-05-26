@extends('layouts.kassa', ['title' => 'GoodPay - Verkoop Overzicht'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kassa/sales.css') }}">
@endpush

@section('content')
    <section id="salesPage">
        <div id="salesTopLeft">
            <div id="selectDates">
                <form action="{{ route('kassa.sales') }}" method="GET">
                    <table class="dateSelect">
                        <tbody>
                        <tr>
                            <td>Begin datum:</td>
                            <td>
                                <input
                                    id="begindate"
                                    type="date"
                                    name="begindate"
                                    value="{{ request('begindate') }}"
                                >
                            </td>
                            <td rowspan="2">
                                <button id="datesSelectedBtn" type="submit">
                                    Maak Overzicht
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td>Eind datum:</td>
                            <td>
                                <input
                                    id="enddate"
                                    type="date"
                                    name="enddate"
                                    value="{{ request('enddate') }}"
                                >
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>

        <div id="salesTopRight">
            <div id="overviewInfo">
                <table>
                    <tbody>
                    <tr>
                        <td>Omzet:</td>
                        <td>€ {{ number_format($total, 2, ',', ' ') }}</td>

                        <td>BTW:</td>
                        <td>€ {{ number_format($vat, 2, ',', ' ') }}</td>

                        <td>excl. BTW:</td>
                        <td>€ {{ number_format($totalExVat, 2, ',', ' ') }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="salesBottom">
            <div id="salesResult">
                @if($errors->any())
                    <div class="errorMessage">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if($hasFilter && $sales->isEmpty() && ! $errors->any())
                    <div class="errorMessage">
                        Geen verkoop op de aangegeven datum.
                    </div>
                @endif

                <table>
                    <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Gerecht</th>
                        <th>Prijs</th>
                        <th>Aantal</th>
                        <th>Subtotaal</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($sales as $sale)
                        <tr>
                            <td>
                                {{ \Illuminate\Support\Carbon::parse($sale->saleDate)->format('d-m-Y') }}
                            </td>

                            <td>
                                {{ $sale->naam }}
                            </td>

                            <td>
                                € {{ number_format((float) $sale->price, 2, ',', ' ') }}
                            </td>

                            <td>
                                {{ $sale->amount }}
                            </td>

                            <td>
                                € {{ number_format((float) $sale->price * (int) $sale->amount, 2, ',', ' ') }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
