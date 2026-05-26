<?php

namespace App\Http\Controllers\Kassa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kassa\PlanningRuleRequest;
use App\Models\Employee;
use App\Models\PlanningRule;
use App\Models\RestaurantTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PlanningController extends Controller
{
    public function index(Request $request): View
    {
        $weekStart = $this->resolveWeekStart($request->query('week_start'));
        $weekEnd = $weekStart->copy()->addDays(6);

        $days = collect(range(0, 6))->map(function (int $dayOffset) use ($weekStart) {
            $date = $weekStart->copy()->addDays($dayOffset);

            return [
                'date' => $date,
                'value' => $date->toDateString(),
                'label' => ucfirst($date->translatedFormat('l d-m-Y')),
            ];
        });

        $planningRules = PlanningRule::query()
            ->with(['employee', 'tables'])
            ->whereBetween('datum', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('datum')
            ->orderByRaw("FIELD(dienst, 'middag', 'avond')")
            ->get()
            ->groupBy(function (PlanningRule $planningRule) {
                return $planningRule->datum->toDateString() . '|' . $planningRule->dienst;
            });

        return view('kassa.planning', [
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'previousWeekStart' => $weekStart->copy()->subWeek()->toDateString(),
            'nextWeekStart' => $weekStart->copy()->addWeek()->toDateString(),
            'days' => $days,
            'employees' => $this->getEmployees(),
            'tables' => $this->getTables(),
            'planningRules' => $planningRules,
            'services' => $this->getServices(),
        ]);
    }

    public function store(PlanningRuleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $planningRule = PlanningRule::updateOrCreate(
            [
                'datum' => $validated['datum'],
                'dienst' => $validated['dienst'],
                'medewerker_id' => $validated['medewerker_id'],
            ],
            [
                'opmerking' => $validated['opmerking'] ?? null,
            ]
        );

        $planningRule->tables()->sync($validated['tafel_ids']);

        return redirect()
            ->route('kassa.planning', ['week_start' => $validated['week_start']])
            ->with('success', 'Planningregel is opgeslagen.');
    }

    public function destroy(Request $request, PlanningRule $planningRule): RedirectResponse
    {
        $weekStart = $this->resolveWeekStart($request->query('week_start'));

        $planningRule->tables()->detach();
        $planningRule->delete();

        return redirect()
            ->route('kassa.planning', ['week_start' => $weekStart->toDateString()])
            ->with('success', 'Planningregel is verwijderd.');
    }

    private function resolveWeekStart(mixed $date): Carbon
    {
        if (! $date) {
            return Carbon::parse(now()->toDateString())->startOfWeek(Carbon::MONDAY);
        }

        return Carbon::parse((string) $date)->startOfWeek(Carbon::MONDAY);
    }

    private function getEmployees()
    {
        return Employee::query()
            ->where('is_actief', true)
            ->orderBy('naam')
            ->get();
    }

    private function getTables()
    {
        return RestaurantTable::query()
            ->where('is_actief', true)
            ->orderBy('tafelnummer')
            ->get();
    }

    private function getServices(): array
    {
        return [
            'middag' => 'Middag',
            'avond' => 'Avond',
        ];
    }
}
