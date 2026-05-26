<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sales', 'bijgerecht_keuze')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('bijgerecht_keuze', 100)->nullable()->after('opmerking');
            });
        }

        if (! Schema::hasColumn('sales', 'bijgerecht_extra_prijs')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('bijgerecht_extra_prijs', 8, 2)->default(0)->after('bijgerecht_keuze');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales', 'bijgerecht_extra_prijs')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('bijgerecht_extra_prijs');
            });
        }

        if (Schema::hasColumn('sales', 'bijgerecht_keuze')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('bijgerecht_keuze');
            });
        }
    }
};
