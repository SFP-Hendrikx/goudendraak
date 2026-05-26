<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'GoodPay Kassa' }}</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('css/kassa/shared.css') }}">

    @stack('styles')
</head>
<body>
<header id="menuBar">
    <a href="{{ route('kassa.index') }}" aria-label="GoodPay kassa">
        <img id="logo" src="{{ asset('assets/images/goodpay.png') }}" alt="GoodPay logo">
    </a>

    <div id="buttonBar">
        @if(session('kassa_logged_in'))
            <a
                href="{{ route('kassa.index') }}"
                class="menuButton {{ request()->routeIs('kassa.index') ? 'active' : '' }}"
            >
                Kassa
            </a>

            @if(session('kassa_is_admin'))
                <a
                    href="{{ route('kassa.gerechten') }}"
                    class="menuButton {{ request()->routeIs('kassa.gerechten*') ? 'active' : '' }}"
                >
                    Gerechten
                </a>

                <a
                    href="{{ route('kassa.aanbiedingen') }}"
                    class="menuButton {{ request()->routeIs('kassa.aanbiedingen*') ? 'active' : '' }}"
                >
                    Aanbiedingen
                </a>

                <a
                    href="{{ route('kassa.planning') }}"
                    class="menuButton {{ request()->routeIs('kassa.planning*') ? 'active' : '' }}"
                >
                    Planning
                </a>

                <a
                    href="{{ route('kassa.sales-summaries') }}"
                    class="menuButton {{ request()->routeIs('kassa.sales-summaries*') ? 'active' : '' }}"
                >
                    Samenvattingen
                </a>
            @endif

            <a
                href="{{ route('kassa.sales') }}"
                class="menuButton {{ request()->routeIs('kassa.sales') ? 'active' : '' }}"
            >
                Verkoop Overzicht
            </a>

            <form action="{{ route('kassa.logout') }}" method="POST" class="logoutForm">
                @csrf

                <button type="submit" class="menuButton logoutButton">
                    Log Uit
                </button>
            </form>
        @endif
    </div>
</header>

<main>
    @yield('content')
</main>

@stack('scripts')
</body>
</html>
