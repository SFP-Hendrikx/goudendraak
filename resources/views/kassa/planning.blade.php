@extends('layouts.kassa', ['title' => 'GoodPay - Planning'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kassa/planning.css') }}">
@endpush

@section('content')
    <section id="planningPage">
        <header class="planningHeader">
            <div>
                <h1>Weekplanning bediening</h1>
                <p>
                    Planning van {{ $weekStart->format('d-m-Y') }}
                    t/m {{ $weekEnd->format('d-m-Y') }}.
                </p>
            </div>

            <div class="weekNavigation">
                <a href="{{ route('kassa.planning', ['week_start' => $previousWeekStart]) }}">
                    Vorige week
                </a>

                <a href="{{ route('kassa.planning', ['week_start' => now()->startOfWeek(\Illuminate\Support\Carbon::MONDAY)->toDateString()]) }}">
                    Deze week
                </a>

                <a href="{{ route('kassa.planning', ['week_start' => $nextWeekStart]) }}">
                    Volgende week
                </a>
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

        <section class="planningCreatePanel">
            <h2>Planningregel toevoegen of bijwerken</h2>

            <form action="{{ route('kassa.planning.store') }}" method="POST" class="planningForm">
                @csrf

                <input type="hidden" name="week_start" value="{{ $weekStart->toDateString() }}">

                <div class="planningFormGrid">
                    <div class="formGroup">
                        <label for="datum">Dag</label>
                        <select id="datum" name="datum" required>
                            @foreach($days as $day)
                                <option value="{{ $day['value'] }}" @selected(old('datum') === $day['value'])>
                                    {{ $day['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="formGroup">
                        <label for="dienst">Dienst</label>
                        <select id="dienst" name="dienst" required>
                            @foreach($services as $serviceKey => $serviceLabel)
                                <option value="{{ $serviceKey }}" @selected(old('dienst') === $serviceKey)>
                                    {{ $serviceLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="formGroup">
                        <label for="medewerker_id">Medewerker</label>
                        <select id="medewerker_id" name="medewerker_id" required>
                            <option value="">Kies medewerker</option>

                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected((int) old('medewerker_id') === $employee->id)>
                                    {{ $employee->naam }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="formGroup full">
                        <label>Tafels</label>

                        <div class="tableCheckboxGrid">
                            @foreach($tables as $table)
                                <label class="tableCheckbox">
                                    <input
                                        type="checkbox"
                                        name="tafel_ids[]"
                                        value="{{ $table->id }}"
                                        @checked(in_array($table->id, old('tafel_ids', [])))
                                    >
                                    Tafel {{ $table->tafelnummer }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="formGroup full">
                        <label for="opmerking">Opmerking</label>
                        <input
                            id="opmerking"
                            type="text"
                            name="opmerking"
                            maxlength="255"
                            value="{{ old('opmerking') }}"
                            placeholder="Bijvoorbeeld: terras, drukke avond, nieuw personeel..."
                        >
                    </div>
                </div>

                <button type="submit">
                    Planning opslaan
                </button>
            </form>
        </section>

        <section class="planningWeek">
            <h2>Planning deze week</h2>

            <div class="planningGrid">
                @foreach($days as $day)
                    <article class="planningDay">
                        <h3>{{ $day['label'] }}</h3>

                        @foreach($services as $serviceKey => $serviceLabel)
                            @php
                                $groupKey = $day['value'] . '|' . $serviceKey;
                                $rulesForService = $planningRules->get($groupKey, collect());
                            @endphp

                            <section class="serviceBlock">
                                <h4>{{ $serviceLabel }}</h4>

                                @forelse($rulesForService as $planningRule)
                                    <div class="planningRuleCard">
                                        <div>
                                            <strong>{{ $planningRule->employee->naam }}</strong>

                                            <p>
                                                Tafels:
                                                {{ $planningRule->tables->sortBy('tafelnummer')->map(fn ($table) => $table->tafelnummer)->join(', ') }}
                                            </p>

                                            @if($planningRule->opmerking)
                                                <p class="planningRemark">
                                                    {{ $planningRule->opmerking }}
                                                </p>
                                            @endif
                                        </div>

                                        <form
                                            action="{{ route('kassa.planning.destroy', [
                                                'planningRule' => $planningRule,
                                                'week_start' => $weekStart->toDateString(),
                                            ]) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="dangerButton">
                                                Verwijderen
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="emptyService">Nog geen planning.</p>
                                @endforelse
                            </section>
                        @endforeach
                    </article>
                @endforeach
            </div>
        </section>
    </section>
@endsection
