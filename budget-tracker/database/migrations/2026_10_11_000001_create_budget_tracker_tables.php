<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Personal vs business ledgers (Justine Tabunan / Astin Tech Solutions).
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 20)->default('personal'); // personal | business
            $table->string('color', 20)->default('#2563eb');
            $table->timestamps();
        });

        // Banks, e-wallets, BNPL providers. Seeded with GCash, Maya, Maribank, etc.
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 30)->default('bank'); // ewallet | digital_bank | bank | bnpl | other
            $table->string('color', 20)->default('#334155');
            $table->string('text_color', 20)->default('#ffffff');
            $table->string('logo_path')->nullable();
            $table->string('short_code', 6)->nullable();
            $table->unsignedInteger('sort_order')->default(100);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('type', 30)->default('wallet'); // wallet | savings | checking | credit_card | cash | other
            $table->decimal('balance', 14, 2)->default(0);
            $table->string('currency', 3)->default('PHP');
            $table->boolean('is_active')->default(true);
            $table->boolean('include_in_net_worth')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained()->restrictOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete(); // default pay-from account
            $table->string('name'); // GLoan, GCredit, GGives, Maya Credit...
            $table->string('type', 30)->default('term'); // term | credit_line | installment | bnpl | credit_card
            $table->decimal('principal', 14, 2)->default(0);
            $table->decimal('outstanding_balance', 14, 2)->default(0);
            $table->decimal('credit_limit', 14, 2)->nullable();
            $table->decimal('interest_rate', 6, 2)->nullable(); // % per month or year (free text in notes)
            $table->unsignedSmallInteger('term_months')->nullable();
            $table->decimal('monthly_payment', 14, 2)->default(0);
            $table->unsignedTinyInteger('due_day')->nullable(); // 1-31
            $table->date('next_due_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('active'); // active | paid | closed
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete(); // default pay-from
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete(); // vendor logo (optional)
            $table->string('name');
            $table->string('vendor')->nullable();
            $table->string('category', 30)->default('other'); // housing | utilities | internet | subscription | insurance | education | other
            $table->string('subcategory', 30)->nullable(); // for subscriptions: ai | dev_tools | cloud | entertainment | productivity | other
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('currency', 3)->default('PHP');
            $table->string('frequency', 20)->default('monthly'); // weekly | monthly | quarterly | yearly | once
            $table->date('next_due_date');
            $table->boolean('auto_pay')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('icon', 10)->nullable();
            $table->string('website')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20); // income | expense | transfer_in | transfer_out
            $table->string('category', 40)->default('other');
            $table->decimal('amount', 14, 2);
            $table->date('date');
            $table->string('description')->nullable();
            $table->nullableMorphs('reference'); // bill_payment / loan_payment / savings deposit
            $table->timestamps();
        });

        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('period_date'); // the due date this payment covers
            $table->date('paid_at');
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('paid_at');
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete(); // where the money sits
            $table->string('name');
            $table->string('icon', 10)->nullable();
            $table->decimal('target_amount', 14, 2);
            $table->decimal('saved_amount', 14, 2)->default(0);
            $table->date('deadline')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Allowance, Food, Transport...
            $table->string('category', 40); // transaction category it tracks
            $table->string('period', 20)->default('monthly'); // weekly | monthly
            $table->decimal('amount', 14, 2);
            $table->string('icon', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'budgets', 'savings_goals', 'loan_payments', 'bill_payments', 'transactions', 'bills', 'loans', 'accounts', 'institutions', 'profiles'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
