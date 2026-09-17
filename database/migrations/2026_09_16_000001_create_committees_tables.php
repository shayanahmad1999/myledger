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
        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained();
            $table->string('name');
            $table->decimal('contribution_amount', 15, 2);
            $table->integer('total_members');
            $table->decimal('total_pool_amount', 15, 2);
            $table->enum('frequency', ['monthly', 'weekly', 'biweekly'])->default('monthly');
            $table->date('start_date');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->enum('my_role', ['manager', 'member'])->default('manager');
            $table->foreignId('my_person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('committee_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->string('name');
            $table->integer('slot_number');
            $table->integer('payout_round_no')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['committee_id', 'slot_number']);
        });

        Schema::create('committee_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')->constrained()->cascadeOnDelete();
            $table->integer('round_number');
            $table->date('due_date');
            $table->foreignId('winner_member_id')->nullable()->constrained('committee_members')->nullOnDelete();
            $table->decimal('total_expected', 15, 2);
            $table->decimal('total_collected', 15, 2)->default(0);
            $table->decimal('payout_amount', 15, 2);
            $table->enum('payout_status', ['pending', 'paid'])->default('pending');
            $table->date('payout_date')->nullable();
            $table->foreignId('payout_account_id')->nullable()->constrained('ledger_accounts')->nullOnDelete();
            $table->foreignId('payout_transaction_id')->nullable()->constrained('financial_transactions')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['committee_id', 'round_number']);
        });

        Schema::create('committee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('committee_member_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('paid_at')->nullable();
            $table->foreignId('account_id')->nullable()->constrained('ledger_accounts')->nullOnDelete();
            $table->foreignId('financial_transaction_id')->nullable()->constrained('financial_transactions')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['committee_round_id', 'committee_member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_payments');
        Schema::dropIfExists('committee_rounds');
        Schema::dropIfExists('committee_members');
        Schema::dropIfExists('committees');
    }
};
