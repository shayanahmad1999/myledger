<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financial_transaction_id')->nullable()->constrained('financial_transactions')->cascadeOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained('loans')->cascadeOnDelete();
            $table->string('disk', 40)->default('local');
            $table->string('path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('color', 20)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });

        Schema::create('financial_transaction_tag', function (Blueprint $table) {
            $table->foreignId('financial_transaction_id')->constrained('financial_transactions')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->primary(['financial_transaction_id', 'tag_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('auditable_type', 150);
            $table->unsignedBigInteger('auditable_id');
            $table->string('action', 50);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('base_currency_id')->constrained('currencies')->restrictOnDelete();
            $table->string('theme', 20)->default('system');
            $table->string('locale', 10)->default('en');
            $table->time('daily_reminder_time')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('user_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('financial_transaction_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('attachments');
    }
};
