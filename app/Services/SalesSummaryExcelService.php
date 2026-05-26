<?php

namespace App\Services;

use App\Models\SalesSummary;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SalesSummaryExcelService
{
    private const CURRENCY_FORMAT = '€ #,##0.00';

    public function generateForDate(Carbon|string $date): SalesSummary
    {
        $summaryDate = Carbon::parse($date)->toDateString();

        $sales = $this->getSalesForDate($summaryDate);

        $totals = $this->calculateTotals($sales);

        $fileName = 'verkoop-samenvatting-' . $summaryDate . '.xlsx';
        $relativePath = 'sales-summaries/' . $fileName;
        $absoluteDirectory = storage_path('app/private/sales-summaries');
        $absolutePath = storage_path('app/private/' . $relativePath);

        File::ensureDirectoryExists($absoluteDirectory);

        $spreadsheet = $this->buildSpreadsheet($summaryDate, $sales, $totals);

        $writer = new Xlsx($spreadsheet);
        $writer->save($absolutePath);

        return SalesSummary::updateOrCreate(
            [
                'summary_date' => $summaryDate,
            ],
            [
                'file_name' => $fileName,
                'file_path' => $relativePath,
                'total_sales_lines' => $totals['total_sales_lines'],
                'total_items' => $totals['total_items'],
                'total_revenue' => $totals['total_revenue'],
                'total_ex_vat' => $totals['total_ex_vat'],
                'vat_amount' => $totals['vat_amount'],
                'generated_at' => now(),
            ]
        );
    }

    private function getSalesForDate(string $summaryDate)
    {
        $startDate = Carbon::parse($summaryDate)->startOfDay();
        $endDate = Carbon::parse($summaryDate)->endOfDay();

        $select = [
            'sales.id',
            'sales.itemId',
            'sales.amount',
            'sales.saleDate',
            'menu.menunummer',
            'menu.menu_toevoeging',
            'menu.naam',
            'menu.price',
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

        return DB::table('sales')
            ->join('menu', 'menu.id', '=', 'sales.itemId')
            ->select($select)
            ->whereBetween('sales.saleDate', [$startDate, $endDate])
            ->orderBy('sales.saleDate')
            ->orderBy('sales.id')
            ->get();
    }

    private function calculateTotals($sales): array
    {
        $totalRevenue = $sales->sum(function ($sale) {
            $price = (float) $sale->price;

            $sideDishExtraPrice = isset($sale->bijgerecht_extra_prijs)
                ? (float) $sale->bijgerecht_extra_prijs
                : 0.0;

            return ($price + $sideDishExtraPrice) * (int) $sale->amount;
        });

        $totalExVat = ($totalRevenue / 106) * 100;
        $vatAmount = $totalRevenue - $totalExVat;

        return [
            'total_sales_lines' => $sales->count(),
            'total_items' => $sales->sum('amount'),
            'total_revenue' => round($totalRevenue, 2),
            'total_ex_vat' => round($totalExVat, 2),
            'vat_amount' => round($vatAmount, 2),
        ];
    }

    private function buildSpreadsheet(string $summaryDate, $sales, array $totals): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Samenvatting');

        $summarySheet->setCellValue('A1', 'Verkoop samenvatting');
        $summarySheet->setCellValue('A3', 'Datum');
        $summarySheet->setCellValue('B3', Carbon::parse($summaryDate)->format('d-m-Y'));

        $summarySheet->setCellValue('A5', 'Aantal verkoopregels');
        $summarySheet->setCellValue('B5', $totals['total_sales_lines']);

        $summarySheet->setCellValue('A6', 'Aantal verkochte items');
        $summarySheet->setCellValue('B6', $totals['total_items']);

        $summarySheet->setCellValue('A7', 'Omzet inclusief btw');
        $summarySheet->setCellValue('B7', $totals['total_revenue']);

        $summarySheet->setCellValue('A8', 'Omzet exclusief btw');
        $summarySheet->setCellValue('B8', $totals['total_ex_vat']);

        $summarySheet->setCellValue('A9', 'Btw-bedrag');
        $summarySheet->setCellValue('B9', $totals['vat_amount']);

        $summarySheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $summarySheet->getStyle('A3:A9')->getFont()->setBold(true);
        $summarySheet->getStyle('B7:B9')->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);

        $summarySheet->getColumnDimension('A')->setWidth(28);
        $summarySheet->getColumnDimension('B')->setWidth(22);

        $salesSheet = $spreadsheet->createSheet();
        $salesSheet->setTitle('Verkoopregels');

        $headers = [
            'A1' => 'Tijd',
            'B1' => 'Menunummer',
            'C1' => 'Gerecht',
            'D1' => 'Aantal',
            'E1' => 'Stukprijs',
            'F1' => 'Bijgerecht',
            'G1' => 'Bijgerecht extra',
            'H1' => 'Regelomzet',
            'I1' => 'Opmerking',
        ];

        foreach ($headers as $cell => $header) {
            $salesSheet->setCellValue($cell, $header);
        }

        $rowNumber = 2;

        foreach ($sales as $sale) {
            $price = (float) $sale->price;

            $sideDishExtraPrice = isset($sale->bijgerecht_extra_prijs)
                ? (float) $sale->bijgerecht_extra_prijs
                : 0.0;

            $amount = (int) $sale->amount;
            $lineRevenue = ($price + $sideDishExtraPrice) * $amount;

            $menuNumber = trim(($sale->menunummer ?? '') . ($sale->menu_toevoeging ?? ''));

            $salesSheet->setCellValue('A' . $rowNumber, Carbon::parse($sale->saleDate)->format('H:i:s'));
            $salesSheet->setCellValue('B' . $rowNumber, $menuNumber);
            $salesSheet->setCellValue('C' . $rowNumber, $sale->naam);
            $salesSheet->setCellValue('D' . $rowNumber, $amount);
            $salesSheet->setCellValue('E' . $rowNumber, $price);
            $salesSheet->setCellValue('F' . $rowNumber, $sale->bijgerecht_keuze ?? 'Geen bijgerecht');
            $salesSheet->setCellValue('G' . $rowNumber, $sideDishExtraPrice);
            $salesSheet->setCellValue('H' . $rowNumber, $lineRevenue);
            $salesSheet->setCellValue('I' . $rowNumber, $sale->opmerking ?? '');

            $rowNumber++;
        }

        $lastRow = max(2, $rowNumber - 1);

        $salesSheet->getStyle('A1:I1')->getFont()->setBold(true);
        $salesSheet->getStyle('A1:I1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFDDEBFF');

        $salesSheet->getStyle('A1:I' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $salesSheet->getStyle('D2:D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $salesSheet->getStyle('E2:E' . $lastRow)->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
        $salesSheet->getStyle('G2:H' . $lastRow)->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);

        foreach (range('A', 'I') as $column) {
            $salesSheet->getColumnDimension($column)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}
