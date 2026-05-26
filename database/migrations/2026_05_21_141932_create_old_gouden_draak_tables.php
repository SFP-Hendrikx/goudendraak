<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gebruiker', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('wachtwoord', 45);
            $table->tinyInteger('isAdmin');
        });

        Schema::create('menu', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('menunummer')->nullable();
            $table->text('menu_toevoeging')->nullable();
            $table->longText('naam');
            $table->float('price')->nullable();
            $table->string('soortgerecht', 45)->nullable();
            $table->mediumText('beschrijving')->nullable();
        });

        Schema::create('menu_pdf', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->longText('menu_pdf')->nullable();
        });

        Schema::create('rijst_enzo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('beschrijving', 45)->nullable();
            $table->integer('extra_prijs')->nullable();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('itemId');
            $table->integer('amount');
            $table->dateTime('saleDate', 4);
        });

        Schema::create('specialiteiten', function (Blueprint $table) {
            $table->string('id', 10)->primary();
            $table->string('name', 45);
            $table->mediumText('informatie');
            $table->decimal('prijs', 10, 0);
            $table->unsignedInteger('rijst_keuze_etc');

            $table->foreign('rijst_keuze_etc')
                ->references('id')
                ->on('rijst_enzo')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialiteiten');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('rijst_enzo');
        Schema::dropIfExists('menu_pdf');
        Schema::dropIfExists('menu');
        Schema::dropIfExists('gebruiker');
    }
};