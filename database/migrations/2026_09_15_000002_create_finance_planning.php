<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->restrictOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->foreignId('ledger_account_id')->constrained('ledger_accounts')->restrictOnDelete();
            $table->string('direction', 12); // given|taken
            $table->string('title', 140)->nullable();
            $table->decimal('principal', 20, 4);
            $table->decimal('outstanding_principal', 20, 4);
            $table->decimal('interest_rate', 9, 4)->default(0);
            $table->string('interest_type', 20)->default('none');
            $table->date('start_date');
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'direction', 'status']);
            $table->index(['user_id', 'due_date']);
        });

        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('ledger_accounts')->restrictOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->string('title', 120);
            $table->decimal('target_amount', 20, 4);
            $table->decimal('allocated_amount', 20, 4)->default(0);
            $table->date('target_date')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_completed']);
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('amount', 20, 4);
            $table->unsignedTinyInteger('alert_percent')->default(80);
            $table->unsignedTinyInteger('last_alert_level')->default(0); // 0 none, 1 warning, 2 over budget
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'category_id', 'period_start', 'period_end']);
            $table->index(['user_id', 'period_start', 'period_end']);
        });

        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->foreignId('source_account_id')->nullable()->constrained('ledger_accounts')->restrictOnDelete();
            $table->foreignId('destination_account_id')->nullable()->constrained('ledger_accounts')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->foreignId('person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->string('type', 40);
            $table->string('title', 140);
            $table->decimal('amount', 20, 4);
            $table->string('frequency', 20); // daily|weekly|monthly|yearly
            $table->unsignedSmallInteger('interval')->default(1);
            $table->timestamp('next_run_at');
            $table->string('mode', 20)->default('remind');
            $table->boolean('is_active')->default(true);
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'next_run_at']);
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('savings_goals');
        Schema::dropIfExists('loans');
    }
};
