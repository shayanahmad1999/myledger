<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->char('code', 3)->unique();
            $table->string('name', 80);
            $table->string('symbol', 12);
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('ledger_accounts')->nullOnDelete();
            $table->string('kind', 20);
            $table->string('type', 40);
            $table->string('name', 120);
            $table->string('institution', 120)->nullable();
            $table->string('last_four', 4)->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('include_in_net_worth')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'kind', 'type']);
            $table->index(['user_id', 'is_archived']);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_account_id')->constrained('ledger_accounts')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('type', 20); // income|expense
            $table->string('name', 100);
            $table->string('icon', 50)->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'type', 'name']);
            $table->index(['user_id', 'type', 'is_active']);
        });

        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('phone', 40)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('ledger_accounts');
        Schema::dropIfExists('currencies');
    }
};
