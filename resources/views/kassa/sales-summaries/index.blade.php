@extends('layouts.kassa', ['title' => 'GoodPay - Verkoop samenvattingen'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kassa/sales-summaries.css') }}">
@endpush

@section('content')
    <section id="salesSummariesPage">
        <header class="summariesHeader">
            <div>
                <h1>Verkoop samenvattingen</h1>
                <p>Dagelijkse Excel-overzichten van de omzet en verkoopregels.</p>
            </div>
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

        <section class="generatePanel">
            <h2>Samenvatting handmatig genereren</h2>

            <p>
                De samenvatting wordt automatisch dagelijks gegenereerd.
            </p>

            <form action="{{ route('kassa.sales-summaries.generate') }}" method="POST" class="generateForm">
                @csrf

                <div class="formGroup">
                    <label for="summary_date">Datum</label>
                    <input
                        id="summary_date"
                        type="date"
                        name="summary_date"
                        value="{{ old('summary_date', now()->toDateString()) }}"
                        required
                    >
                </div>

                <button type="submit">
                    Genereer Excel
                </button>
            </form>
        </section>

        <section class="summaryList">
            <h2>Gegenereerde samenvattingen</h2>

            @forelse($salesSummaries as $salesSummary)
                <article class="summaryCard">
                    <div>
                        <h3>{{ $salesSummary->summary_date->format('d-m-Y') }}</h3>

                        <p>
                            Gegenereerd:
                            {{ $salesSummary->generated_at ? $salesSummary->generated_at->format('d-m-Y H:i') : 'Onbekend' }}
                        </p>

                        <ul class="summaryStats">
                            <li>
                                <strong>Verkoopregels:</strong>
                                {{ $salesSummary->total_sales_lines }}
                            </li>

                            <li>
                                <strong>Items verkocht:</strong>
                                {{ $salesSummary->total_items }}
                            </li>

                            <li>
                                <strong>Omzet incl. btw:</strong>
                                € {{ number_format((float) $salesSummary->total_revenue, 2, ',', ' ') }}
                            </li>

                            <li>
                                <strong>Omzet excl. btw:</strong>
                                € {{ number_format((float) $salesSummary->total_ex_vat, 2, ',', ' ') }}
                            </li>

                            <li>
                                <strong>Btw:</strong>
                                € {{ number_format((float) $salesSummary->vat_amount, 2, ',', ' ') }}
                            </li>
                        </ul>
                    </div>

                    <div class="summaryActions">
                        <a
                            href="{{ route('kassa.sales-summaries.download', $salesSummary) }}"
                            class="downloadButton"
                        >
                            Download Excel
                        </a>
                    </div>
                </article>
            @empty
                <p class="emptyState">
                    Er zijn nog geen verkoop-samenvattingen gegenereerd.
                </p>
            @endforelse

            <div class="paginationWrapper">
                {{ $salesSummaries->links() }}
            </div>
        </section>
    </section>
@endsection
