<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            $table->string('symbol')->index();
            $table->string('name')->nullable();

            $table->string('isin', 12)->nullable()->index();

            $table->string('instrument_key')->unique();

            $table->string('exchange_token')->nullable();

            $table->string('exchange', 20)->default('NSE');
            $table->string('segment', 30)->nullable();
            $table->string('instrument_type', 30)->nullable();

            $table->integer('lot_size')->nullable();
            $table->decimal('tick_size', 10, 4)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
