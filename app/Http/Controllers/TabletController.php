<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\RestaurantTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TabletController extends Controller
{
    private const MINUTES_BETWEEN_ORDERS = 10;
    private const MAX_ROUNDS_PER_DAY = 5;

    public function index(Request $request): View
    {
        $tables = $this->getTables();

        $selectedTableNumber = trim((string) $request->query('tafel', ''));

        if ($selectedTableNumber !== '' && ! $this->tableNumberExists($selectedTableNumber)) {
            $selectedTableNumber = '';
        }

        $status = $selectedTableNumber !== ''
            ? $this->getOrderStatus($selectedTableNumber)
            : null;

        return view('tablet.index', [
            'tables' => $tables,
            'selectedTableNumber' => $selectedTableNumber,
            'status' => $status,
            'menuGroups' => $this->getMenuGroups(),
            'maxRounds' => self::MAX_ROUNDS_PER_DAY,
            'minutesBetweenOrders' => self::MINUTES_BETWEEN_ORDERS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:50'],
            'order_remark' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:menu,id'],
            'items.*.amount' => ['required', 'integer', 'min:0', 'max:20'],
        ], [
            'table_number.required' => 'Kies eerst een tafel.',
            'items.required' => 'Kies minimaal één gerecht.',
            'items.array' => 'Ongeldige bestelling.',
            'items.*.id.required' => 'Gerecht ontbreekt.',
            'items.*.id.integer' => 'Ongeldig gerecht.',
            'items.*.id.exists' => 'Een gekozen gerecht bestaat niet meer.',
            'items.*.amount.required' => 'Aantal ontbreekt.',
            'items.*.amount.integer' => 'Aantal moet een getal zijn.',
            'items.*.amount.min' => 'Aantal mag niet lager zijn dan 0.',
            'items.*.amount.max' => 'Aantal mag maximaal 20 zijn.',
            'order_remark.max' => 'Opmerking mag maximaal 255 tekens zijn.',
        ]);

        $tableNumber = trim($validated['table_number']);

        if (! $this->tableNumberExists($tableNumber)) {
            return redirect()
                ->route('tablet.index')
                ->with('error', 'Deze tafel bestaat niet of is niet actief.');
        }

        $status = $this->getOrderStatus($tableNumber);

        if ($status['roundsUsed'] >= self::MAX_ROUNDS_PER_DAY) {
            return redirect()
                ->route('tablet.index', ['tafel' => $tableNumber])
                ->withInput()
                ->with('error', 'Deze tafel heeft vandaag al het maximale aantal rondes besteld.');
        }

        if (! $status['canOrder']) {
            return redirect()
                ->route('tablet.index', ['tafel' => $tableNumber])
                ->withInput()
                ->with('error', 'Je kunt nog niet opnieuw bestellen. Wacht tot ' . $status['nextAllowedAt']->format('H:i') . '.');
        }

        $submittedItems = collect($validated['items'])
            ->map(function (array $item) {
                return [
                    'id' => (int) $item['id'],
                    'amount' => (int) $item['amount'],
                ];
            })
            ->filter(function (array $item) {
                return $item['amount'] > 0;
            })
            ->values();

        if ($submittedItems->isEmpty()) {
            return redirect()
                ->route('tablet.index', ['tafel' => $tableNumber])
                ->withInput()
                ->with('error', 'Kies minimaal één gerecht.');
        }

        $menuItemIds = $submittedItems
            ->pluck('id')
            ->unique()
            ->values();

        $menuItems = MenuItem::query()
            ->where('is_actief', true)
            ->whereIn('id', $menuItemIds)
            ->get()
            ->keyBy('id');

        $orderRemark = trim((string) ($validated['order_remark'] ?? ''));

        $orderLines = $submittedItems
            ->map(function (array $submittedItem) use ($menuItems, $orderRemark) {
                $menuItem = $menuItems->get($submittedItem['id']);

                if (! $menuItem) {
                    return null;
                }

                return [
                    'id' => $menuItem->id,
                    'number' => $this->formatMenuNumber($menuItem),
                    'name' => $menuItem->naam,
                    'amount' => $submittedItem['amount'],
                    'price' => (float) $menuItem->price,
                    'remark' => $orderRemark,
                ];
            })
            ->filter()
            ->values();

        if ($orderLines->isEmpty()) {
            return redirect()
                ->route('tablet.index', ['tafel' => $tableNumber])
                ->withInput()
                ->with('error', 'De gekozen gerechten zijn niet meer actief.');
        }

        $round = $status['roundsUsed'] + 1;
        $orderedAt = now();
        $totalItems = $orderLines->sum('amount');

        DB::transaction(function () use ($tableNumber, $round, $orderedAt, $totalItems, $orderLines, $orderRemark) {
            DB::table('tablet_orders')->insert([
                'tafelnummer' => $tableNumber,
                'ronde' => $round,
                'totaal_items' => $totalItems,
                'bestelling_json' => json_encode($orderLines->toArray()),
                'besteld_op' => $orderedAt,
                'created_at' => $orderedAt,
                'updated_at' => $orderedAt,
            ]);

            foreach ($orderLines as $line) {
                $saleData = [
                    'itemId' => $line['id'],
                    'amount' => $line['amount'],
                    'saleDate' => $orderedAt,
                ];

                if (Schema::hasColumn('sales', 'opmerking')) {
                    $saleData['opmerking'] = $this->buildSalesRemark(
                        tableNumber: $tableNumber,
                        round: $round,
                        orderRemark: $orderRemark
                    );
                }

                if (Schema::hasColumn('sales', 'bijgerecht_keuze')) {
                    $saleData['bijgerecht_keuze'] = 'Geen bijgerecht';
                }

                if (Schema::hasColumn('sales', 'bijgerecht_extra_prijs')) {
                    $saleData['bijgerecht_extra_prijs'] = 0.00;
                }

                DB::table('sales')->insert($saleData);
            }
        });

        return redirect()
            ->route('tablet.index', ['tafel' => $tableNumber])
            ->with('success', 'Bestelling geplaatst. Dit was ronde ' . $round . ' van ' . self::MAX_ROUNDS_PER_DAY . '.');
    }

    public function resetTable(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:50'],
        ], [
            'table_number.required' => 'Kies eerst een tafel.',
        ]);

        $tableNumber = trim($validated['table_number']);

        if (! $this->tableNumberExists($tableNumber)) {
            return redirect()
                ->route('tablet.index')
                ->with('error', 'Deze tafel bestaat niet of is niet actief.');
        }

        DB::table('tablet_orders')
            ->where('tafelnummer', $tableNumber)
            ->whereBetween('besteld_op', [
                now()->startOfDay(),
                now()->endOfDay(),
            ])
            ->delete();

        return redirect()
            ->route('tablet.index', ['tafel' => $tableNumber])
            ->with('success', 'Tafel ' . $tableNumber . ' is gereset. Nieuwe gasten starten weer bij ronde 1.');
    }

    private function getTables(): Collection
    {
        return RestaurantTable::query()
            ->where('is_actief', true)
            ->orderBy('tafelnummer')
            ->get();
    }

    private function tableNumberExists(string $tableNumber): bool
    {
        return RestaurantTable::query()
            ->where('is_actief', true)
            ->where('tafelnummer', $tableNumber)
            ->exists();
    }
private function getMenuGroups(): Collection
{
    return MenuItem::query()
        ->where('is_actief', true)
        ->orderBy('soortgerecht')
        ->orderBy('menunummer')
        ->orderBy('menu_toevoeging')
        ->orderBy('naam')
        ->get()
        ->groupBy(function (MenuItem $menuItem) {
            return $menuItem->soortgerecht ?: 'Overig';
        });
}

private function getOrderStatus(string $tableNumber): array
{
    $todayStart = now()->startOfDay();
    $todayEnd = now()->endOfDay();

    $ordersToday = DB::table('tablet_orders')
        ->where('tafelnummer', $tableNumber)
        ->whereBetween('besteld_op', [$todayStart, $todayEnd])
        ->orderByDesc('besteld_op')
        ->get();

    $roundsUsed = $ordersToday->count();
    $lastOrder = $ordersToday->first();

    $lastOrderAt = $lastOrder
        ? Carbon::parse($lastOrder->besteld_op)
        : null;

    $nextAllowedAt = $lastOrderAt
        ? $lastOrderAt->copy()->addMinutes(self::MINUTES_BETWEEN_ORDERS)
        : null;

    $canOrderByTime = ! $nextAllowedAt || now()->greaterThanOrEqualTo($nextAllowedAt);
    $canOrderByRounds = $roundsUsed < self::MAX_ROUNDS_PER_DAY;

    return [
        'roundsUsed' => $roundsUsed,
        'roundsLeft' => max(self::MAX_ROUNDS_PER_DAY - $roundsUsed, 0),
        'lastOrderAt' => $lastOrderAt,
        'nextAllowedAt' => $nextAllowedAt,
        'canOrder' => $canOrderByTime && $canOrderByRounds,
        'canOrderByTime' => $canOrderByTime,
        'canOrderByRounds' => $canOrderByRounds,
    ];
}

private function formatMenuNumber(MenuItem $menuItem): string
{
    $number = trim((string) ($menuItem->menunummer ?? ''));
    $addition = trim((string) ($menuItem->menu_toevoeging ?? ''));

    $menuNumber = trim($number . $addition);

    return $menuNumber !== '' ? $menuNumber : (string) $menuItem->id;
}

private function buildSalesRemark(string $tableNumber, int $round, string $orderRemark): string
{
    $remark = 'Tablet tafel ' . $tableNumber . ' - ronde ' . $round;

    if ($orderRemark !== '') {
        $remark .= ' - ' . $orderRemark;
    }

    return mb_substr($remark, 0, 255);
}
}
