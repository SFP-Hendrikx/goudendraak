<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KassaController extends Controller
{
    public function index(): View
    {
        $menuItems = collect();
        $commonRemarks = collect();
        $sideDishOptions = collect($this->getSideDishOptions());

        if (session('kassa_logged_in')) {
            $menuItems = $this->getMenuItems();
            $commonRemarks = $this->getCommonRemarks();
        }

        return view('kassa.index', [
            'menuItems' => $menuItems,
            'commonRemarks' => $commonRemarks,
            'sideDishOptions' => $sideDishOptions,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employeeNr' => ['required', 'integer', 'min:1'],
            'password' => ['required', 'string'],
        ], [
            'employeeNr.required' => 'Medewerker nummer niet ingevuld.',
            'employeeNr.integer' => 'Medewerker nummer moet een getal zijn.',
            'employeeNr.min' => 'Medewerker nummer moet minimaal 1 zijn.',
            'password.required' => 'Wachtwoord niet ingevuld.',
        ]);

        $user = DB::table('gebruiker')
            ->where('id', $validated['employeeNr'])
            ->where('wachtwoord', $validated['password'])
            ->first();

        if (! $user) {
            return back()
                ->withInput(['employeeNr' => $validated['employeeNr']])
                ->with('login_error', 'Onbekende combinatie medewerker nummer en wachtwoord.');
        }

        session([
            'kassa_logged_in' => true,
            'kassa_employee_id' => $user->id,
            'kassa_is_admin' => (bool) $user->isAdmin,
        ]);

        return redirect()->route('kassa.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'kassa_logged_in',
            'kassa_employee_id',
            'kassa_is_admin',
        ]);

        return redirect()->route('kassa.index');
    }

    public function gerechten(): View
    {
        return view('kassa.gerechten');
    }

    public function verkoopOverzicht(Request $request): View
    {
        $sales = collect();
        $total = 0.0;
        $totalExVat = 0.0;
        $vat = 0.0;

        $hasFilter = $request->filled('begindate') || $request->filled('enddate');

        if ($hasFilter) {
            $validated = $request->validate([
                'begindate' => ['required', 'date'],
                'enddate' => ['required', 'date', 'after_or_equal:begindate'],
            ], [
                'begindate.required' => 'Begindatum niet ingevuld.',
                'begindate.date' => 'Begindatum is geen geldige datum.',
                'enddate.required' => 'Einddatum niet ingevuld.',
                'enddate.date' => 'Einddatum is geen geldige datum.',
                'enddate.after_or_equal' => 'Einddatum is eerder dan begindatum.',
            ]);

            $beginDate = Carbon::parse($validated['begindate'])->startOfDay();
            $endDate = Carbon::parse($validated['enddate'])->endOfDay();

            $query = DB::table('sales')
                ->join('menu', 'menu.id', '=', 'sales.itemId')
                ->whereBetween('sales.saleDate', [$beginDate, $endDate])
                ->orderBy('sales.saleDate');

            $select = [
                'menu.naam',
                'menu.price',
                'sales.amount',
                'sales.saleDate',
            ];

            if (Schema::hasColumn('sales', 'opmerking')) {
                $select[] = 'sales.opmerking';
            }

            if (Schema::hasColumn('sales', 'bijgerecht_keuze')) {
                $select[] = 'sales.bijgerecht_keuze';
            }

            if (Schema::hasColumn('sales', 'bijgerecht_extra_prijs')) {
                $select[] = 'sales.bijgerecht_extra_prijs';
            }

            $sales = $query->select($select)->get();

            $total = $sales->sum(function ($sale) {
                $price = (float) $sale->price;

                $extraPrice = isset($sale->bijgerecht_extra_prijs)
                    ? (float) $sale->bijgerecht_extra_prijs
                    : 0.0;

                return ($price + $extraPrice) * (int) $sale->amount;
            });

            $totalExVat = ($total / 106) * 100;
            $vat = $total - $totalExVat;
        }

        return view('kassa.verkoop-overzicht', [
            'sales' => $sales,
            'hasFilter' => $hasFilter,
            'total' => $total,
            'totalExVat' => $totalExVat,
            'vat' => $vat,
        ]);
    }

    public function pay(Request $request): JsonResponse
    {
        $sideDishOptions = collect($this->getSideDishOptions());
        $allowedSideDishOptionIds = $sideDishOptions->pluck('id')->toArray();

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:menu,id'],
            'items.*.amount' => ['required', 'integer', 'min:1'],
            'items.*.remark' => ['nullable', 'string', 'max:255'],
            'items.*.side_dish_option' => ['nullable', 'string', Rule::in($allowedSideDishOptionIds)],
        ], [
            'items.required' => 'Geen gerechten geselecteerd.',
            'items.array' => 'Ongeldige bestelling.',
            'items.min' => 'Geen gerechten geselecteerd.',

            'items.*.id.required' => 'Gerecht ontbreekt.',
            'items.*.id.integer' => 'Ongeldig gerecht.',
            'items.*.id.exists' => 'Gerecht bestaat niet.',

            'items.*.amount.required' => 'Aantal ontbreekt.',
            'items.*.amount.integer' => 'Aantal moet een getal zijn.',
            'items.*.amount.min' => 'Aantal moet minimaal 1 zijn.',

            'items.*.remark.string' => 'Opmerking moet tekst zijn.',
            'items.*.remark.max' => 'Opmerking mag maximaal 255 tekens zijn.',

            'items.*.side_dish_option.string' => 'Bijgerechtkeuze moet tekst zijn.',
            'items.*.side_dish_option.in' => 'Ongeldige bijgerechtkeuze.',
        ]);

        DB::transaction(function () use ($validated, $sideDishOptions) {
            $saleDate = now();

            foreach ($validated['items'] as $item) {
                $remark = trim($item['remark'] ?? '');
                $sideDishOptionId = $item['side_dish_option'] ?? 'geen_bijgerecht';

                $sideDishOption = $sideDishOptions->firstWhere('id', $sideDishOptionId);

                DB::table('sales')->insert([
                    'itemId' => $item['id'],
                    'amount' => $item['amount'],
                    'opmerking' => $remark !== '' ? $remark : null,
                    'bijgerecht_keuze' => $sideDishOption['name'] ?? 'Geen bijgerecht',
                    'bijgerecht_extra_prijs' => $sideDishOption['extra_price'] ?? 0,
                    'saleDate' => $saleDate,
                ]);
            }
        });

        return response()->json([
            'message' => 'Verkoop succesvol',
        ]);
    }

    private function getMenuItems()
    {
        return DB::table('menu')
            ->orderBy('id')
            ->orderBy('menunummer')
            ->orderBy('menu_toevoeging')
            ->get();
    }

    private function getCommonRemarks()
    {
        if (! Schema::hasTable('sales') || ! Schema::hasColumn('sales', 'opmerking')) {
            return collect();
        }

        return DB::table('sales')
            ->select('opmerking', DB::raw('COUNT(*) as amount_used'))
            ->whereNotNull('opmerking')
            ->where('opmerking', '!=', '')
            ->groupBy('opmerking')
            ->orderByDesc('amount_used')
            ->orderBy('opmerking')
            ->limit(10)
            ->pluck('opmerking');
    }

    private function getSideDishOptions(): array
    {
        return [
            [
                'id' => 'geen_bijgerecht',
                'name' => 'Geen bijgerecht',
                'extra_price' => 0.00,
            ],
            [
                'id' => 'witte_rijst',
                'name' => 'Witte rijst',
                'extra_price' => 0.00,
            ],
            [
                'id' => 'nasi_bami_goreng',
                'name' => 'Nasi/Bami goreng',
                'extra_price' => 0.90,
            ],
            [
                'id' => 'mihoen_goreng',
                'name' => 'Mihoen goreng',
                'extra_price' => 2.50,
            ],
            [
                'id' => 'chinese_bami',
                'name' => 'Chinese bami',
                'extra_price' => 2.50,
            ],
        ];
    }
}
