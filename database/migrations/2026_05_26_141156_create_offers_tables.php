<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aanbiedingen', function (Blueprint $table) {
            $table->id();
            $table->string('titel', 150);
            $table->text('beschrijving')->nullable();
            $table->date('startdatum');
            $table->date('einddatum');
            $table->boolean('is_actief')->default(true);
            $table->timestamps();
        });

        Schema::create('aanbieding_menu', function (Blueprint $table) {
            $table->id();

            $table->foreignId('aanbieding_id')
                ->constrained('aanbiedingen')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->unsignedInteger('menu_item_id');

            $table->foreign('menu_item_id')
                ->references('id')
                ->on('menu')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('actieprijs', 8, 2);
            $table->timestamps();

            $table->unique(['aanbieding_id', 'menu_item_id'], 'aanbieding_menu_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aanbieding_menu');
        Schema::dropIfExists('aanbiedingen');
    }
};
