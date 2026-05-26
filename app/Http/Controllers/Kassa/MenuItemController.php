<?php

namespace App\Http\Controllers\Kassa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kassa\MenuItemRequest;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(Request $request): View
    {
        $query = MenuItem::query();

        if (! $request->boolean('toon_inactief')) {
            $query->where('is_actief', true);
        }

        if ($request->filled('zoek')) {
            $search = $request->string('zoek')->toString();

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('naam', 'like', "%{$search}%")
                    ->orWhere('beschrijving', 'like', "%{$search}%")
                    ->orWhere('soortgerecht', 'like', "%{$search}%")
                    ->orWhere('menunummer', 'like', "%{$search}%")
                    ->orWhere('menu_toevoeging', 'like', "%{$search}%");
            });
        }

        if ($request->filled('categorie')) {
            $query->where('soortgerecht', $request->string('categorie')->toString());
        }

        $menuItems = $query
            ->orderBy('soortgerecht')
            ->orderBy('menunummer')
            ->orderBy('menu_toevoeging')
            ->orderBy('naam')
            ->paginate(25)
            ->withQueryString();

        return view('kassa.gerechten', [
            'menuItems' => $menuItems,
            'categories' => $this->getCategories(),
        ]);
    }

    public function create(): View
    {
        return view('kassa.gerechten-create', [
            'categories' => $this->getCategories(),
        ]);
    }

    public function store(MenuItemRequest $request): RedirectResponse
    {
        MenuItem::create([
            ...$request->validated(),
            'is_actief' => true,
        ]);

        return redirect()
            ->route('kassa.gerechten')
            ->with('success', 'Gerecht is toegevoegd.');
    }

    public function update(MenuItemRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update($request->validated());

        return redirect()
            ->route('kassa.gerechten', $request->query())
            ->with('success', 'Gerecht is bijgewerkt.');
    }

    public function destroy(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update([
            'is_actief' => false,
        ]);

        return redirect()
            ->route('kassa.gerechten', $request->query())
            ->with('success', 'Gerecht is verwijderd uit de actieve menulijst.');
    }

    public function restore(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update([
            'is_actief' => true,
        ]);

        return redirect()
            ->route('kassa.gerechten', $request->query())
            ->with('success', 'Gerecht is opnieuw actief gemaakt.');
    }

    private function getCategories()
    {
        return MenuItem::query()
            ->select('soortgerecht')
            ->whereNotNull('soortgerecht')
            ->where('soortgerecht', '!=', '')
            ->distinct()
            ->orderBy('soortgerecht')
            ->pluck('soortgerecht');
    }
}
