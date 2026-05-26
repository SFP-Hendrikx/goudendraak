<?php

namespace App\Http\Controllers\Kassa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kassa\OfferRequest;
use App\Models\MenuItem;
use App\Models\Offer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function index(Request $request): View
    {
        $query = Offer::query()
            ->withCount('menuItems');

        if (! $request->boolean('toon_inactief')) {
            $query->where('is_actief', true);
        }

        if ($request->filled('zoek')) {
            $search = $request->string('zoek')->toString();

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('titel', 'like', "%{$search}%")
                    ->orWhere('beschrijving', 'like', "%{$search}%");
            });
        }

        $offers = $query
            ->orderByDesc('startdatum')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('kassa.aanbiedingen.index', [
            'offers' => $offers,
        ]);
    }

    public function create(): View
    {
        return view('kassa.aanbiedingen.create', [
            'menuItems' => $this->getMenuItems(),
        ]);
    }

    public function store(OfferRequest $request): RedirectResponse
    {
        $offer = Offer::create($request->offerData());

        $offer->menuItems()->sync($request->offerMenuItems());

        return redirect()
            ->route('kassa.aanbiedingen')
            ->with('success', 'Aanbieding is aangemaakt.');
    }

    public function edit(Offer $offer): View
    {
        $offer->load('menuItems');

        return view('kassa.aanbiedingen.edit', [
            'offer' => $offer,
            'menuItems' => $this->getMenuItems(),
            'selectedMenuItems' => $offer->menuItems
                ->keyBy('id')
                ->map(function (MenuItem $menuItem) {
                    return [
                        'actieprijs' => $menuItem->pivot->actieprijs,
                    ];
                }),
        ]);
    }

    public function update(OfferRequest $request, Offer $offer): RedirectResponse
    {
        $offer->update($request->offerData());

        $offer->menuItems()->sync($request->offerMenuItems());

        return redirect()
            ->route('kassa.aanbiedingen')
            ->with('success', 'Aanbieding is bijgewerkt.');
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        $offer->update([
            'is_actief' => false,
        ]);

        return redirect()
            ->route('kassa.aanbiedingen')
            ->with('success', 'Aanbieding is gedeactiveerd.');
    }

    public function restore(Offer $offer): RedirectResponse
    {
        $offer->update([
            'is_actief' => true,
        ]);

        return redirect()
            ->route('kassa.aanbiedingen', ['toon_inactief' => 1])
            ->with('success', 'Aanbieding is opnieuw actief gemaakt.');
    }

    private function getMenuItems()
    {
        return MenuItem::query()
            ->where('is_actief', true)
            ->orderBy('soortgerecht')
            ->orderBy('menunummer')
            ->orderBy('menu_toevoeging')
            ->orderBy('naam')
            ->get();
    }
}
