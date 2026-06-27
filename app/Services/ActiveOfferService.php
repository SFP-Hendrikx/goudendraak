<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Offer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ActiveOfferService
{
    public function activeOffersForDate(Carbon|string|null $date = null): Collection
    {
        $date = Carbon::parse($date ?? now())->toDateString();

        return Offer::query()
            ->activeOnDate($date)
            ->with(['menuItems' => function ($query) {
                $query
                    ->active()
                    ->orderedForMenu();
            }])
            ->orderBy('startdatum')
            ->orderBy('titel')
            ->get();
    }

    public function groupedMenuForDate(Carbon|string|null $date = null): Collection
    {
        return $this->menuItemsForDate($date)
            ->groupBy(fn (array $menuItem): string => $menuItem['category']);
    }

    public function menuItemsForDate(Carbon|string|null $date = null): Collection
    {
        $date = Carbon::parse($date ?? now())->toDateString();

        return MenuItem::query()
            ->active()
            ->with(['offers' => function ($query) use ($date) {
                $query
                    ->activeOnDate($date)
                    ->orderBy('aanbieding_menu.actieprijs')
                    ->orderBy('aanbiedingen.startdatum')
                    ->orderBy('aanbiedingen.titel');
            }])
            ->orderedForMenu()
            ->get()
            ->map(fn (MenuItem $menuItem): array => $this->toMenuViewData($menuItem));
    }

    public function priceForMenuItem(MenuItem $menuItem, Carbon|string|null $date = null): array
    {
        $date = Carbon::parse($date ?? now())->toDateString();

        $bestOffer = $menuItem->offers()
            ->activeOnDate($date)
            ->orderBy('aanbieding_menu.actieprijs')
            ->orderBy('aanbiedingen.startdatum')
            ->orderBy('aanbiedingen.titel')
            ->first();

        return $this->priceData($menuItem, $bestOffer);
    }

    private function toMenuViewData(MenuItem $menuItem): array
    {
        $bestOffer = $menuItem->offers->first();

        return array_merge([
            'id' => $menuItem->id,
            'number' => $menuItem->fullMenuNumber(),
            'name' => $menuItem->naam,
            'description' => $menuItem->beschrijving,
            'category' => $menuItem->soortgerecht ?: 'Overig',
        ], $this->priceData($menuItem, $bestOffer));
    }

    private function priceData(MenuItem $menuItem, ?Offer $offer): array
    {
        $normalPrice = (float) $menuItem->price;
        $offerPrice = $offer ? (float) $offer->pivot->actieprijs : null;

        return [
            'normal_price' => $normalPrice,
            'display_price' => $offerPrice ?? $normalPrice,
            'has_offer' => $offer !== null,
            'offer' => $offer ? [
                'id' => $offer->id,
                'title' => $offer->titel,
                'description' => $offer->beschrijving,
                'start_date' => $offer->startdatum,
                'end_date' => $offer->einddatum,
                'price' => $offerPrice,
            ] : null,
        ];
    }
}
