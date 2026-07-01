<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Menukaart - De Gouden Draak</title>

    <style>
        @page {
            margin-top: 14px;
            margin-right: 14px;
            margin-bottom: 14px;
            margin-left: 10px;
        }

        body {
            margin: 0;
            padding: 0;
            background: #ffffd2;
            color: #1f1f1f;
            font-family: DejaVu Serif, serif;
            font-size: 10px;
            line-height: 1.22;
        }

        .menu-card {
            margin: 0;
            padding: 14px 10px 14px 8px;
            border: 3px double #2f6b2f;
            background: #ffffd2;
        }

        .menu-header {
            margin: 0 0 12px;
            padding: 0 0 10px;
            border-bottom: 2px double #2f6b2f;
            text-align: center;
        }

        .menu-subtitle {
            margin: 0 0 4px;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.04em;
        }

        .menu-title {
            margin: 0;
            font-size: 28px;
            line-height: 1;
            letter-spacing: 0.08em;
            text-decoration: underline;
        }

        .menu-note {
            margin: 8px 0 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            line-height: 1.35;
        }

        .menu-generated {
            margin: 4px 0 0;
            color: #555;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
        }

        .menu-category {
            margin: 0 0 10px;
            page-break-inside: avoid;
        }

        .menu-category-title {
            margin: 0 0 5px;
            padding: 4px 6px;
            border-top: 1px solid #2f6b2f;
            border-bottom: 1px solid #2f6b2f;
            background: #fff5a8;
            font-size: 12px;
            line-height: 1.15;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .menu-items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .menu-item td {
            vertical-align: top;
            padding-top: 2px;
            padding-bottom: 3px;
        }

        .menu-number {
            width: 34px;
            padding-right: 4px;
            font-weight: bold;
            white-space: nowrap;
        }

        .menu-info {
            width: 430px;
            padding-right: 6px;
        }

        .menu-name {
            font-weight: bold;
            word-wrap: break-word;
        }

        .menu-description {
            margin: 1px 0 0;
            color: #333;
            font-size: 8px;
            font-style: italic;
            font-weight: normal;
            line-height: 1.2;
            word-wrap: break-word;
        }

        .menu-price {
            width: 58px;
            text-align: right;
            white-space: nowrap;
            font-weight: bold;
            padding-left: 2px;
        }

        .menu-price-normal {
            font-size: 9px;
        }

        .menu-price-old {
            display: block;
            color: #555;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
            line-height: 1;
            text-decoration: line-through;
        }

        .menu-price-new {
            display: block;
            margin-top: 1px;
            color: #b00000;
            font-size: 10px;
            line-height: 1.05;
            font-weight: bold;
        }

        .menu-offer-label {
            margin: 3px 0 0;
            padding: 3px 4px;
            border: 1px solid #b00000;
            background: #fff1b8;
            color: #8b0000;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
            line-height: 1.25;
            word-wrap: break-word;
        }

        .empty-menu {
            padding: 18px;
            border: 1px solid #2f6b2f;
            background: #fff5a8;
            text-align: center;
        }

        .empty-menu-title {
            margin: 0 0 6px;
            font-size: 15px;
        }

        .empty-menu-text {
            margin: 0;
        }
    </style>
</head>
<body>
<main class="menu-card">
    <header class="menu-header">
        <p class="menu-subtitle">Chinees Indische Specialiteiten</p>

        <h1 class="menu-title">Menukaart</h1>
    </header>

    @forelse($menuGroups as $category => $menuItems)
        <section class="menu-category">
            <h2 class="menu-category-title">
                {{ \Illuminate\Support\Str::upper($category) }}
            </h2>

            <table class="menu-items">
                <tbody>
                @foreach($menuItems as $menuItem)
                    <tr class="menu-item">
                        <td class="menu-number">
                            {{ $menuItem['number'] }}.
                        </td>

                        <td class="menu-info">
                            <div class="menu-name">
                                {{ $menuItem['name'] }}
                            </div>

                            @if($menuItem['description'])
                                <p class="menu-description">
                                    {{ $menuItem['description'] }}
                                </p>
                            @endif

                            @if($menuItem['has_offer'])
                                <p class="menu-offer-label">
                                    <strong>Aanbieding:</strong> {{ $menuItem['offer']['title'] }}
                                    <br>
                                    Geldig van {{ $menuItem['offer']['start_date']->format('d-m-Y') }}
                                    t/m {{ $menuItem['offer']['end_date']->format('d-m-Y') }}
                                </p>
                            @endif
                        </td>

                        <td class="menu-price">
                            @if($menuItem['has_offer'])
                                <span class="menu-price-old">
                                            € {{ number_format($menuItem['normal_price'], 2, ',', ' ') }}
                                        </span>

                                <span class="menu-price-new">
                                            € {{ number_format($menuItem['display_price'], 2, ',', ' ') }}
                                        </span>
                            @else
                                <span class="menu-price-normal">
                                            € {{ number_format($menuItem['display_price'], 2, ',', ' ') }}
                                        </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    @empty
        <article class="empty-menu">
            <h2 class="empty-menu-title">Geen gerechten beschikbaar</h2>
            <p class="empty-menu-text">
                Er staan op dit moment geen actieve gerechten op de menukaart.
            </p>
        </article>
    @endforelse
</main>
</body>
</html>
