<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesSummary extends Model
{
    protected $table = 'sales_summaries';

    protected $fillable = [
        'summary_date',
        'file_name',
        'file_path',
        'total_sales_lines',
        'total_items',
        'total_revenue',
        'total_ex_vat',
        'vat_amount',
        'generated_at',
    ];

    protected $casts = [
        'summary_date' => 'date',
        'total_revenue' => 'decimal:2',
        'total_ex_vat' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'generated_at' => 'datetime',
    ];
}
