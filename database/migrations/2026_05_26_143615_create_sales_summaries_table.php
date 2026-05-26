<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('summary_date')->unique();
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedInteger('total_sales_lines')->default(0);
            $table->unsignedInteger('total_items')->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->decimal('total_ex_vat', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_summaries');
    }
};
