<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medewerkers', function (Blueprint $table) {
            $table->id();
            $table->string('naam', 100);
            $table->boolean('is_actief')->default(true);
            $table->timestamps();
        });

        Schema::create('restaurant_tafels', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tafelnummer');
            $table->boolean('is_actief')->default(true);
            $table->timestamps();

            $table->unique('tafelnummer');
        });

        Schema::create('planning_regels', function (Blueprint $table) {
            $table->id();
            $table->date('datum');
            $table->string('dienst', 30);
            $table->foreignId('medewerker_id')
                ->constrained('medewerkers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('opmerking', 255)->nullable();
            $table->timestamps();

            $table->unique(['datum', 'dienst', 'medewerker_id'], 'planning_unique_medewerker_dienst');
        });

        Schema::create('planning_regel_tafel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_regel_id')
                ->constrained('planning_regels')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('restaurant_tafel_id')
                ->constrained('restaurant_tafels')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();

            $table->unique(['planning_regel_id', 'restaurant_tafel_id'], 'planning_tafel_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_regel_tafel');
        Schema::dropIfExists('planning_regels');
        Schema::dropIfExists('restaurant_tafels');
        Schema::dropIfExists('medewerkers');
    }
};
