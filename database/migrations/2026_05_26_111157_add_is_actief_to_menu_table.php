<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('menu', 'is_actief')) {
            Schema::table('menu', function (Blueprint $table) {
                $table->boolean('is_actief')->default(true)->after('beschrijving');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menu', 'is_actief')) {
            Schema::table('menu', function (Blueprint $table) {
                $table->dropColumn('is_actief');
            });
        }
    }
};
