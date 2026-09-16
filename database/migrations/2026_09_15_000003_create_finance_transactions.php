<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->restrictOnDelete();

            $table->foreignId('source_account_id')
                ->nullable()
                ->constrained('ledger_accounts')
                ->restrictOnDelete();

            $table->foreignId('destination_account_id')
                ->nullable()
                ->constrained('ledger_accounts')
                ->restrictOnDelete();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignId('person_id')
                ->nullable()
                ->constrained('people')
                ->nullOnDelete();

            $table->foreignId('loan_id')
                ->nullable()
                ->constrained('loans')
                ->restrictOnDelete();

            $table->foreignId('savings_goal_id')
                ->nullable()
                ->constrained('savings_goals')
                ->restrictOnDelete();

            $table->foreignId('recurring_transaction_id')
                ->nullable()
                ->constrained('recurring_transactions')
                ->nullOnDelete();

            $table->foreignId('reversal_of_id')
                ->nullable()
                ->constrained('financial_transactions')
                ->restrictOnDelete();

            $table->string('type', 50);
            $table->string('reference_no', 60);
            $table->date('transaction_date');
            $table->decimal('amount', 20, 4);
            $table->string('description', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('posted');
            $table->json('metadata')->nullable();
            $table->timestamps();

            /*
             * Explicit short index names.
             */
            $table->unique(
                ['user_id', 'reference_no'],
                'fin_tx_user_reference_unique'
            );

            $table->index(
                ['user_id', 'transaction_date'],
                'fin_tx_user_date_idx'
            );

            $table->index(
                ['user_id', 'type', 'transaction_date'],
                'fin_tx_user_type_date_idx'
            );

            $table->index(
                ['user_id', 'status'],
                'fin_tx_user_status_idx'
            );
        });

        Schema::create('transaction_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('financial_transaction_id')
                ->constrained('financial_transactions')
                ->cascadeOnDelete();

            $table->foreignId('ledger_account_id')
                ->constrained('ledger_accounts')
                ->restrictOnDelete();

            $table->foreignId('loan_id')
                ->nullable()
                ->constrained('loans')
                ->restrictOnDelete();

            $table->decimal('debit', 20, 4)->default(0);
            $table->decimal('credit', 20, 4)->default(0);
            $table->string('memo', 255)->nullable();
            $table->timestamps();

            // Fixes your current MySQL error
            $table->index(
                ['ledger_account_id', 'financial_transaction_id'],
                'tx_entry_ledger_tx_idx'
            );

            $table->index(
                ['loan_id', 'financial_transaction_id'],
                'tx_entry_loan_tx_idx'
            );
        });

        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_id')
                ->constrained('loans')
                ->cascadeOnDelete();

            $table->foreignId('financial_transaction_id')
                ->constrained('financial_transactions')
                ->restrictOnDelete();

            $table->decimal('principal_amount', 20, 4);
            $table->decimal('interest_amount', 20, 4)->default(0);
            $table->date('paid_at');
            $table->timestamps();

            $table->unique(
                'financial_transaction_id',
                'loan_payment_tx_unique'
            );
        });

        Schema::create('savings_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('savings_goal_id')
                ->constrained('savings_goals')
                ->cascadeOnDelete();

            $table->foreignId('financial_transaction_id')
                ->constrained('financial_transactions')
                ->restrictOnDelete();

            // contribution|withdrawal
            $table->string('direction', 20);
            $table->decimal('amount', 20, 4);
            $table->date('allocated_at');
            $table->timestamps();

            $table->unique(
                'financial_transaction_id',
                'saving_allocation_tx_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_allocations');
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('transaction_entries');
        Schema::dropIfExists('financial_transactions');
    }
};
