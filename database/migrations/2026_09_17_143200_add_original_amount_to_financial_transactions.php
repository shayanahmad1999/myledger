<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->decimal('original_amount', 20, 4)->nullable()->after('amount');
            $table->foreignId('original_currency_id')->nullable()->after('original_amount')->constrained('currencies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropColumn(['original_amount', 'original_currency_id']);
        });
    }
};