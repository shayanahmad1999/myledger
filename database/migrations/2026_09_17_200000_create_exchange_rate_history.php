<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rate_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->cascadeOnDelete();
            $table->foreignId('base_currency_id')
                ->constrained('currencies')
                ->cascadeOnDelete();
            $table->date('rate_date');
            $table->decimal('rate', 20, 8);
            $table->timestamps();

            $table->unique(['currency_id', 'base_currency_id', 'rate_date'], 'exch_rate_hist_unique');
            $table->index(['currency_id', 'rate_date'], 'exch_rate_hist_currency_date_idx');
        });

        // Add exchange_rate column to financial_transactions to store rate used for this transaction
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->decimal('exchange_rate', 20, 8)->nullable()->after('original_currency_id');
        });
    }

    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropColumn('exchange_rate');
        });
        Schema::dropIfExists('exchange_rate_history');
    }
};