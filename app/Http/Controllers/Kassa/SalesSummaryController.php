<?php

namespace App\Http\Controllers\Kassa;

use App\Http\Controllers\Controller;
use App\Models\SalesSummary;
use App\Services\SalesSummaryExcelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SalesSummaryController extends Controller
{
    public function index(): View
    {
        $salesSummaries = SalesSummary::query()
            ->orderByDesc('summary_date')
            ->paginate(20);

        return view('kassa.sales-summaries.index', [
            'salesSummaries' => $salesSummaries,
        ]);
    }

    public function generate(Request $request, SalesSummaryExcelService $salesSummaryExcelService): RedirectResponse
    {
        $validated = $request->validate([
            'summary_date' => ['required', 'date'],
        ], [
            'summary_date.required' => 'Kies een datum.',
            'summary_date.date' => 'Kies een geldige datum.',
        ]);

        $summaryDate = Carbon::parse($validated['summary_date'])->toDateString();

        $salesSummaryExcelService->generateForDate($summaryDate);

        return redirect()
            ->route('kassa.sales-summaries')
            ->with('success', 'Verkoop-samenvatting is gegenereerd.');
    }

    public function download(SalesSummary $salesSummary): BinaryFileResponse
    {
        $absolutePath = storage_path('app/private/' . $salesSummary->file_path);

        if (! file_exists($absolutePath)) {
            abort(404, 'Het Excel-bestand bestaat niet meer.');
        }

        return response()->download($absolutePath, $salesSummary->file_name);
    }
}
