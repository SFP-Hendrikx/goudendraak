<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>De Gouden Draak - Tablet bestellen</title>

    <link rel="stylesheet" href="{{ asset('css/tablet.css') }}">
</head>
<body class="tablet-body">
<main class="tablet-page">
    <header class="tablet-header">
        <p class="tablet-subtitle">De Gouden Draak</p>

        <h1>Bestellen via tablet</h1>

        <p>
            Kies je tafel, selecteer gerechten en plaats je ronde.
            Je kunt maximaal {{ $maxRounds }} rondes bestellen.
        </p>
    </header>

    @if(session('success'))
        <div class="tablet-alert tablet-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="tablet-alert tablet-alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="tablet-alert tablet-alert-error">
            <strong>Controleer je bestelling:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="tablet-card">
        <form method="GET" action="{{ route('tablet.index') }}" class="tablet-table-form">
            <label for="tafel">
                Tafel
            </label>

            <select name="tafel" id="tafel" onchange="this.form.submit()">
                <option value="">Kies je tafel</option>

                @foreach($tables as $table)
                    <option
                        value="{{ $table->tafelnummer }}"
                        @selected((string) $selectedTableNumber === (string) $table->tafelnummer)
                    >
                        Tafel {{ $table->tafelnummer }}
                    </option>
                @endforeach
            </select>

            <button type="submit">
                Start bestellen
            </button>
        </form>
    </section>

    @if($selectedTableNumber !== '' && $status)
        <section class="tablet-status-grid">
            <article class="tablet-status-card">
                <span>Tafel</span>
                <strong>{{ $selectedTableNumber }}</strong>
            </article>

            <article class="tablet-status-card">
                <span>Rondes gebruikt</span>
                <strong>{{ $status['roundsUsed'] }} / {{ $maxRounds }}</strong>
            </article>

            <article class="tablet-status-card">
                <span>Rondes over</span>
                <strong>{{ $status['roundsLeft'] }}</strong>
            </article>

            <article class="tablet-status-card">
                <span>Volgende bestelling</span>

                @if($status['canOrder'])
                    <strong>Nu mogelijk</strong>
                @elseif(! $status['canOrderByRounds'])
                    <strong>Niet meer mogelijk</strong>
                @elseif($status['nextAllowedAt'])
                    <strong>Vanaf {{ $status['nextAllowedAt']->format('H:i') }}</strong>
                @else
                    <strong>Nu mogelijk</strong>
                @endif
            </article>
        </section>

        @if($status['canOrder'])
            <form method="POST" action="{{ route('tablet.store') }}" class="tablet-order-form">
                @csrf

                <input type="hidden" name="table_number" value="{{ $selectedTableNumber }}">

                <section class="tablet-remark-card">
                    <label for="order_remark">
                        Opmerking voor de keuken
                    </label>

                    <textarea
                        id="order_remark"
                        name="order_remark"
                        rows="2"
                        maxlength="255"
                        placeholder="Bijvoorbeeld: geen ui, extra pittig, alles tegelijk brengen..."
                    >{{ old('order_remark') }}</textarea>
                </section>

                <div class="tablet-menu">
                    @forelse($menuGroups as $category => $menuItems)
                        <section class="tablet-category">
                            <h2>{{ \Illuminate\Support\Str::upper($category) }}</h2>

                            <div class="tablet-items">
                                @foreach($menuItems as $menuItem)
                                    <article class="tablet-item">
                                        <input
                                            type="hidden"
                                            name="items[{{ $menuItem->id }}][id]"
                                            value="{{ $menuItem->id }}"
                                        >

                                        <div class="tablet-item-main">
                                            <h3>
                                                @if($menuItem->menunummer)
                                                    <span>{{ $menuItem->menunummer }}{{ $menuItem->menu_toevoeging }}</span>
                                                @endif

                                                {{ $menuItem->naam }}
                                            </h3>

                                            @if($menuItem->beschrijving)
                                                <p>{{ $menuItem->beschrijving }}</p>
                                            @endif

                                            <strong class="tablet-price">
                                                € {{ number_format((float) $menuItem->price, 2, ',', ' ') }}
                                            </strong>
                                        </div>

                                        <div class="tablet-quantity">
                                            <button
                                                type="button"
                                                class="quantity-button"
                                                data-action="minus"
                                                data-target="amount-{{ $menuItem->id }}"
                                                aria-label="Aantal verlagen"
                                            >
                                                −
                                            </button>

                                            <input
                                                id="amount-{{ $menuItem->id }}"
                                                type="number"
                                                name="items[{{ $menuItem->id }}][amount]"
                                                value="{{ old('items.' . $menuItem->id . '.amount', 0) }}"
                                                min="0"
                                                max="20"
                                                inputmode="numeric"
                                            >

                                            <button
                                                type="button"
                                                class="quantity-button"
                                                data-action="plus"
                                                data-target="amount-{{ $menuItem->id }}"
                                                aria-label="Aantal verhogen"
                                            >
                                                +
                                            </button>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @empty
                        <article class="tablet-empty">
                            <h2>Geen gerechten beschikbaar</h2>
                            <p>Er staan op dit moment geen actieve gerechten op de menukaart.</p>
                        </article>
                    @endforelse
                </div>

                <section class="tablet-submit-bar">
                    <div>
                        <strong>Tafel {{ $selectedTableNumber }}</strong>
                        <span id="selected-count">Geen gerechten geselecteerd</span>
                    </div>

                    <button type="submit">
                        Bestelling plaatsen
                    </button>
                </section>
            </form>
        @else
            <section class="tablet-blocked">
                @if(! $status['canOrderByRounds'])
                    <h2>Maximaal aantal rondes bereikt</h2>

                    <p>
                        Tafel {{ $selectedTableNumber }} heeft vandaag al {{ $maxRounds }} rondes besteld.
                    </p>
                @else
                    <h2>Nog even wachten</h2>

                    <p>
                        Je kunt maximaal één keer per {{ $minutesBetweenOrders }} minuten bestellen.
                        De volgende bestelling kan vanaf
                        <strong>{{ $status['nextAllowedAt']->format('H:i') }}</strong>.
                    </p>
                @endif
            </section>
        @endif
    @else
        <section class="tablet-blocked">
            <h2>Kies eerst je tafel</h2>

            <p>
                Selecteer bovenaan je tafelnummer om te beginnen met bestellen.
            </p>
        </section>
    @endif

    @if($selectedTableNumber !== '')
        <section class="tablet-reset-bottom">
            <form
                method="POST"
                action="{{ route('tablet.reset') }}"
                onsubmit="return confirm('Weet je zeker dat je deze tafel wilt resetten?')"
            >
                @csrf

                <input type="hidden" name="table_number" value="{{ $selectedTableNumber }}">

                <button type="submit">
                    Reset tafel
                </button>
            </form>
        </section>
    @endif
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const quantityButtons = document.querySelectorAll('.quantity-button');
        const quantityInputs = document.querySelectorAll('.tablet-quantity input');
        const selectedCount = document.getElementById('selected-count');

        function updateSelectedCount() {
            if (!selectedCount) {
                return;
            }

            let total = 0;

            quantityInputs.forEach(function (input) {
                total += parseInt(input.value || '0', 10);
            });

            if (total === 0) {
                selectedCount.textContent = 'Geen gerechten geselecteerd';
            } else if (total === 1) {
                selectedCount.textContent = '1 gerecht geselecteerd';
            } else {
                selectedCount.textContent = total + ' gerechten geselecteerd';
            }
        }

        quantityButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-target');
                const action = button.getAttribute('data-action');
                const input = document.getElementById(targetId);

                if (!input) {
                    return;
                }

                const min = parseInt(input.getAttribute('min') || '0', 10);
                const max = parseInt(input.getAttribute('max') || '20', 10);
                let value = parseInt(input.value || '0', 10);

                if (action === 'plus') {
                    value += 1;
                }

                if (action === 'minus') {
                    value -= 1;
                }

                if (value < min) {
                    value = min;
                }

                if (value > max) {
                    value = max;
                }

                input.value = value;
                updateSelectedCount();
            });
        });

        quantityInputs.forEach(function (input) {
            input.addEventListener('input', updateSelectedCount);
        });

        updateSelectedCount();
    });
</script>
</body>
</html>
