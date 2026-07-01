<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tablet_orders', function (Blueprint $table) {
            $table->id();
            $table->string('tafelnummer', 50);
            $table->unsignedTinyInteger('ronde');
            $table->unsignedInteger('totaal_items')->default(0);
            $table->json('bestelling_json')->nullable();
            $table->timestamp('besteld_op')->index();
            $table->timestamps();

            $table->index(['tafelnummer', 'besteld_op']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tablet_orders');
    }
};
