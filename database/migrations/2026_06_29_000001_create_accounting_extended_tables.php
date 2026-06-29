<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fiscal Periods
        Schema::create('fiscal_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_name', 50);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('OPEN'); // OPEN, CLOSED
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['start_date', 'end_date']);
        });

        // 2. Trial Balances
        Schema::create('trial_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_period_id')->constrained('fiscal_periods')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->decimal('debit_balance', 18, 2)->default(0);
            $table->decimal('credit_balance', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(['fiscal_period_id', 'account_id']);
        });

        // 3. Journal Templates
        Schema::create('journal_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_code', 50)->unique();
            $table->string('template_name');
            $table->string('event_type'); // SALE, PURCHASE, etc.
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Journal Template Lines
        Schema::create('journal_template_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_template_id')->constrained('journal_templates')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->string('direction'); // DEBIT, CREDIT
            $table->string('formula')->nullable(); // grand_total, subtotal, etc.
            $table->string('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 5. Accounting Rules
        Schema::create('accounting_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name');
            $table->string('event_type');
            $table->foreignId('journal_template_id')->constrained('journal_templates')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_rules');
        Schema::dropIfExists('journal_template_lines');
        Schema::dropIfExists('journal_templates');
        Schema::dropIfExists('trial_balances');
        Schema::dropIfExists('fiscal_periods');
    }
};
