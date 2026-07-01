@extends('layouts.app', ['title' => 'De Gouden Draak - Menukaart'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
@endpush

@section('content')
    <section class="menu-card">
        <header class="menu-header">
            <p class="menu-subtitle">Chinees Indische Specialiteiten</p>

            <h2>Menukaart</h2>

        </header>

        <div class="menu-actions">
            <a href="{{ route('menu.pdf') }}" class="menu-pdf-button" target="_blank" rel="noopener">
                Opslaan als PDF
            </a>
        </div>

        <div class="menu-sections">
            @forelse($menuGroups as $category => $menuItems)
                <section
                    class="menu-category"
                    aria-labelledby="menu-category-{{ \Illuminate\Support\Str::slug($category) }}"
                >
                    <h3 id="menu-category-{{ \Illuminate\Support\Str::slug($category) }}">
                        {{ \Illuminate\Support\Str::upper($category) }}
                    </h3>

                    <ul class="menu-items">
                        @foreach($menuItems as $menuItem)
                            <li class="menu-item {{ $menuItem['has_offer'] ? 'has-offer' : '' }}">
                                <div class="menu-row">
                                    <span class="menu-dish">
                                        <span class="menu-number">{{ $menuItem['number'] }}.</span>
                                        <span class="menu-name">{{ $menuItem['name'] }}</span>
                                    </span>

                                    <span class="menu-dots" aria-hidden="true"></span>

                                    <span class="menu-prices">
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
                                    </span>
                                </div>

                                @if($menuItem['description'])
                                    <p class="menu-description">
                                        {{ $menuItem['description'] }}
                                    </p>
                                @endif

                                @if($menuItem['has_offer'])
                                    <p class="menu-offer-label">
                                        <strong>Aanbieding:</strong> {{ $menuItem['offer']['title'] }}

                                        <span>
                                            geldig van {{ $menuItem['offer']['start_date']->format('d-m-Y') }}
                                            t/m {{ $menuItem['offer']['end_date']->format('d-m-Y') }}
                                        </span>
                                    </p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @empty
                <article class="empty-menu">
                    <h3>Geen gerechten beschikbaar</h3>
                    <p>Er staan op dit moment geen actieve gerechten op de menukaart.</p>
                </article>
            @endforelse
        </div>
    </section>

    <div class="bottom-link">
        <a href="{{ route('offers') }}">Bekijk alle actuele aanbiedingen</a>
    </div>
@endsection
