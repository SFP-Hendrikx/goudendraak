<?php

namespace App\Console\Commands;

use App\Services\SalesSummaryExcelService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateDailySalesSummary extends Command
{
    protected $signature = 'sales:generate-daily-summary {date? : Datum in formaat YYYY-MM-DD}';

    protected $description = 'Genereer een dagelijkse verkoop-samenvatting in Excel-formaat.';

    public function handle(SalesSummaryExcelService $salesSummaryExcelService): int
    {
        $date = $this->argument('date');

        if (! $date) {
            $date = now()->toDateString();
        }

        $summaryDate = Carbon::parse($date)->toDateString();

        $salesSummary = $salesSummaryExcelService->generateForDate($summaryDate);

        $this->info('Verkoop-samenvatting gegenereerd voor ' . $salesSummary->summary_date->format('d-m-Y'));
        $this->info('Bestand: ' . $salesSummary->file_name);

        return self::SUCCESS;
    }
}
