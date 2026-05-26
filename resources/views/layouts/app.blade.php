<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'De Gouden Draak' }}</title>

    <link rel="stylesheet" href="{{ asset('css/shared.css') }}">

    @stack('styles')
</head>
<body>
<header class="top-header">
    <div class="brand brand-left">
        <img src="{{ asset('assets/images/dragon-small.png') }}" alt="Gouden draak" class="brand-dragon">
        <span>De Gouden Draak</span>
        <img src="{{ asset('assets/images/dragon-small-flipped.png') }}" alt="Gouden draak" class="brand-dragon">
    </div>

    <a href="{{ route('offers') }}" class="ticker" aria-label="Aanbiedingen">
        <span>Welkom bij De Gouden Draak. Klik op deze tekst om de aanbiedingen van deze week te zien!</span>
    </a>

    <div class="brand brand-right">
        <img src="{{ asset('assets/images/dragon-small.png') }}" alt="Gouden draak" class="brand-dragon">
        <span>De Gouden Draak</span>
        <img src="{{ asset('assets/images/dragon-small-flipped.png') }}" alt="Gouden draak" class="brand-dragon">
    </div>
</header>

<main class="page-frame">
    <div class="frame-corner frame-corner-top-left"></div>
    <div class="frame-corner frame-corner-top-right"></div>
    <div class="frame-corner frame-corner-bottom-left"></div>
    <div class="frame-corner frame-corner-bottom-right"></div>

    <section class="page-content">
        <section class="hero">
            <img
                src="{{ asset('assets/images/dragon-small.png') }}"
                alt="Gouden draak links"
                class="hero-dragon hero-dragon-left"
            >

            <div class="hero-text">
                <p class="hero-subtitle">Chinees Indische Specialiteiten</p>
                <h1>De Gouden Draak</h1>
            </div>

            <img
                src="{{ asset('assets/images/dragon-small-flipped.png') }}"
                alt="Gouden draak rechts"
                class="hero-dragon hero-dragon-right"
            >
        </section>

        <nav class="main-navigation" aria-label="Hoofdnavigatie">
            <a href="{{ route('menu') }}" class="{{ request()->routeIs('menu') ? 'active' : '' }}">
                Menukaart
            </a>

            <a href="{{ route('offers') }}" class="{{ request()->routeIs('offers') ? 'active' : '' }}">
                Aanbiedingen
            </a>

            <a href="{{ route('news') }}" class="{{ request()->routeIs('news') ? 'active' : '' }}">
                Nieuws
            </a>

            <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">
                Contact
            </a>
        </nav>

        @yield('content')
    </section>
</main>
</body>
</html>
