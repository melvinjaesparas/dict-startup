<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Bill;
use App\Models\Budget;
use App\Models\Institution;
use App\Models\Loan;
use App\Models\Profile;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

/**
 * Example data so the app is not empty on first run. Safe to delete from the UI.
 * Run `php artisan db:seed --class=SampleDataSeeder` again to re-create it.
 */
class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Account::query()->exists()) {
            return; // already seeded
        }

        $personal = Profile::where('slug', 'personal')->firstOrFail();
        $business = Profile::where('slug', 'astin-tech')->firstOrFail();
        $inst = Institution::query()->pluck('id', 'slug');

        // ---- Accounts -------------------------------------------------------
        $gcash = Account::create(['profile_id' => $personal->id, 'institution_id' => $inst['gcash'], 'name' => 'GCash Wallet', 'type' => 'wallet', 'balance' => 4250.75]);
        $gsave = Account::create(['profile_id' => $personal->id, 'institution_id' => $inst['gcash'], 'name' => 'GSave (CIMB)', 'type' => 'savings', 'balance' => 15000]);
        $mari = Account::create(['profile_id' => $personal->id, 'institution_id' => $inst['maribank'], 'name' => 'Maribank Savings', 'type' => 'savings', 'balance' => 32500]);
        $ud = Account::create(['profile_id' => $personal->id, 'institution_id' => $inst['uniondigital'], 'name' => 'UnionDigital Savings', 'type' => 'savings', 'balance' => 8200]);
        $maya = Account::create(['profile_id' => $personal->id, 'institution_id' => $inst['maya'], 'name' => 'Maya Wallet', 'type' => 'wallet', 'balance' => 1890.5]);
        $mayaSavings = Account::create(['profile_id' => $personal->id, 'institution_id' => $inst['maya'], 'name' => 'Maya Savings', 'type' => 'savings', 'balance' => 12000]);
        $bpi = Account::create(['profile_id' => $business->id, 'institution_id' => $inst['bpi'], 'name' => 'BPI Business Savings', 'type' => 'savings', 'balance' => 86400]);
        $bpiCc = Account::create(['profile_id' => $personal->id, 'institution_id' => $inst['bpi'], 'name' => 'BPI Credit Card', 'type' => 'credit_card', 'balance' => 6730, 'include_in_net_worth' => true]);
        $cash = Account::create(['profile_id' => $personal->id, 'institution_id' => $inst['cash'], 'name' => 'Cash on hand', 'type' => 'cash', 'balance' => 1500]);

        // ---- Loans ----------------------------------------------------------
        Loan::create([
            'profile_id' => $personal->id, 'institution_id' => $inst['gcash'], 'account_id' => $gcash->id,
            'name' => 'GLoan', 'type' => 'term', 'principal' => 20000, 'outstanding_balance' => 13340,
            'interest_rate' => 1.59, 'term_months' => 12, 'monthly_payment' => 1985, 'due_day' => 15,
            'next_due_date' => now()->addDays(4)->toDateString(), 'start_date' => now()->subMonths(4)->toDateString(),
            'notes' => '1.59% monthly add-on rate, 12 months.',
        ]);
        Loan::create([
            'profile_id' => $personal->id, 'institution_id' => $inst['gcash'], 'account_id' => $gcash->id,
            'name' => 'GCredit', 'type' => 'credit_line', 'principal' => 3200, 'outstanding_balance' => 3200,
            'credit_limit' => 10000, 'interest_rate' => 5, 'monthly_payment' => 3200, 'due_day' => 21,
            'next_due_date' => now()->addDays(10)->toDateString(), 'notes' => 'Pay full statement to avoid interest.',
        ]);
        Loan::create([
            'profile_id' => $personal->id, 'institution_id' => $inst['gcash'], 'account_id' => $gcash->id,
            'name' => 'GGives - Laptop upgrade', 'type' => 'installment', 'principal' => 18000, 'outstanding_balance' => 9000,
            'term_months' => 6, 'monthly_payment' => 3000, 'due_day' => 5,
            'next_due_date' => now()->subDays(2)->toDateString(), 'notes' => '6 months, 0% promo.',
        ]);
        Loan::create([
            'profile_id' => $personal->id, 'institution_id' => $inst['maya'], 'account_id' => $maya->id,
            'name' => 'Maya Credit', 'type' => 'credit_line', 'principal' => 1450, 'outstanding_balance' => 1450,
            'credit_limit' => 15000, 'monthly_payment' => 1450, 'due_day' => 25,
            'next_due_date' => now()->addDays(14)->toDateString(),
        ]);
        Loan::create([
            'profile_id' => $personal->id, 'institution_id' => $inst['atome'], 'account_id' => $gcash->id,
            'name' => 'Atome - Monitor', 'type' => 'bnpl', 'principal' => 7500, 'outstanding_balance' => 2500,
            'term_months' => 3, 'monthly_payment' => 2500, 'due_day' => 12,
            'next_due_date' => now()->addDays(1)->toDateString(),
        ]);
        Loan::create([
            'profile_id' => $business->id, 'institution_id' => $inst['uniondigital'], 'account_id' => $bpi->id,
            'name' => 'UnionDigital Business Loan', 'type' => 'term', 'principal' => 60000, 'outstanding_balance' => 45000,
            'term_months' => 12, 'monthly_payment' => 5450, 'due_day' => 28,
            'next_due_date' => now()->addDays(17)->toDateString(),
        ]);

        // ---- House & personal bills ------------------------------------------
        Bill::create(['profile_id' => $personal->id, 'account_id' => $gcash->id, 'name' => 'Apartment rent', 'vendor' => 'Landlord', 'category' => 'housing', 'amount' => 8500, 'frequency' => 'monthly', 'icon' => '🏠', 'next_due_date' => now()->addDays(20)->toDateString()]);
        Bill::create(['profile_id' => $personal->id, 'account_id' => $gcash->id, 'name' => 'Electricity', 'vendor' => 'Meralco', 'category' => 'utilities', 'amount' => 2350, 'frequency' => 'monthly', 'icon' => '⚡', 'next_due_date' => now()->addDays(6)->toDateString()]);
        Bill::create(['profile_id' => $personal->id, 'account_id' => $gcash->id, 'name' => 'Water', 'vendor' => 'Maynilad', 'category' => 'utilities', 'amount' => 480, 'frequency' => 'monthly', 'icon' => '💧', 'next_due_date' => now()->addDays(9)->toDateString()]);
        Bill::create(['profile_id' => $personal->id, 'account_id' => $maya->id, 'name' => 'Fiber internet', 'vendor' => 'Converge', 'category' => 'internet', 'amount' => 1500, 'frequency' => 'monthly', 'icon' => '🌐', 'auto_pay' => true, 'next_due_date' => now()->addDays(2)->toDateString()]);
        Bill::create(['profile_id' => $personal->id, 'account_id' => $gcash->id, 'name' => 'Mobile data (prepaid)', 'vendor' => 'Globe', 'category' => 'internet', 'amount' => 599, 'frequency' => 'monthly', 'icon' => '📱', 'next_due_date' => now()->addDays(12)->toDateString()]);
        Bill::create(['profile_id' => $personal->id, 'account_id' => $gcash->id, 'name' => 'Padala to family', 'vendor' => null, 'category' => 'family', 'amount' => 3000, 'frequency' => 'monthly', 'icon' => '👨‍👩‍👧', 'next_due_date' => now()->addDays(19)->toDateString()]);
        Bill::create(['profile_id' => $personal->id, 'account_id' => $gcash->id, 'name' => 'PhilHealth (voluntary)', 'vendor' => 'PhilHealth', 'category' => 'insurance', 'amount' => 1500, 'frequency' => 'quarterly', 'icon' => '🏥', 'next_due_date' => now()->addDays(40)->toDateString()]);

        // ---- Subscriptions (AI + dev tools) -----------------------------------
        $subs = [
            ['Claude Pro', 'Anthropic', 'ai', 20, 'USD', '🤖', 3, $bpiCc],
            ['ChatGPT Plus', 'OpenAI', 'ai', 20, 'USD', '💬', 8, $bpiCc],
            ['Cursor Pro', 'Cursor', 'dev_tools', 20, 'USD', '⌨️', 11, $bpiCc],
            ['GitHub Copilot', 'GitHub', 'dev_tools', 10, 'USD', '🐙', 15, $bpiCc],
            ['Google One 100GB', 'Google', 'cloud', 89, 'PHP', '☁️', 5, $gcash],
            ['DigitalOcean droplet', 'DigitalOcean', 'cloud', 6, 'USD', '🖥️', 1, $bpiCc],
            ['Spotify Premium', 'Spotify', 'entertainment', 149, 'PHP', '🎵', 22, $gcash],
            ['Netflix', 'Netflix', 'entertainment', 399, 'PHP', '🎬', 27, $maya],
        ];
        foreach ($subs as [$name, $vendor, $sub, $amount, $cur, $icon, $days, $acct]) {
            Bill::create([
                'profile_id' => $personal->id, 'account_id' => $acct->id, 'name' => $name, 'vendor' => $vendor,
                'category' => 'subscription', 'subcategory' => $sub, 'amount' => $amount, 'currency' => $cur,
                'frequency' => 'monthly', 'icon' => $icon, 'auto_pay' => true, 'next_due_date' => now()->addDays($days)->toDateString(),
            ]);
        }
        // Business subscriptions
        Bill::create(['profile_id' => $business->id, 'account_id' => $bpi->id, 'name' => 'Claude API credits', 'vendor' => 'Anthropic', 'category' => 'subscription', 'subcategory' => 'ai', 'amount' => 50, 'currency' => 'USD', 'frequency' => 'monthly', 'icon' => '🧠', 'next_due_date' => now()->addDays(13)->toDateString()]);
        Bill::create(['profile_id' => $business->id, 'account_id' => $bpi->id, 'name' => 'Hostinger VPS', 'vendor' => 'Hostinger', 'category' => 'subscription', 'subcategory' => 'cloud', 'amount' => 1299, 'frequency' => 'monthly', 'icon' => '🖥️', 'next_due_date' => now()->addDays(7)->toDateString()]);
        Bill::create(['profile_id' => $business->id, 'account_id' => $bpi->id, 'name' => 'Google Workspace', 'vendor' => 'Google', 'category' => 'subscription', 'subcategory' => 'productivity', 'amount' => 7.2, 'currency' => 'USD', 'frequency' => 'monthly', 'icon' => '📧', 'next_due_date' => now()->addDays(16)->toDateString()]);
        Bill::create(['profile_id' => $business->id, 'account_id' => $bpi->id, 'name' => 'Domain renewals', 'vendor' => 'Namecheap', 'category' => 'subscription', 'subcategory' => 'cloud', 'amount' => 15, 'currency' => 'USD', 'frequency' => 'yearly', 'icon' => '🌍', 'next_due_date' => now()->addDays(120)->toDateString()]);

        // ---- Savings goals -----------------------------------------------------
        SavingsGoal::create(['profile_id' => $personal->id, 'account_id' => $mari->id, 'name' => 'Emergency fund', 'icon' => '🛟', 'target_amount' => 60000, 'saved_amount' => 32500, 'deadline' => now()->addMonths(8)->toDateString()]);
        SavingsGoal::create(['profile_id' => $personal->id, 'account_id' => $ud->id, 'name' => 'New MacBook', 'icon' => '💻', 'target_amount' => 95000, 'saved_amount' => 8200, 'deadline' => now()->addMonths(12)->toDateString()]);
        SavingsGoal::create(['profile_id' => $personal->id, 'account_id' => $mayaSavings->id, 'name' => 'Travel fund', 'icon' => '✈️', 'target_amount' => 25000, 'saved_amount' => 12000]);
        SavingsGoal::create(['profile_id' => $business->id, 'account_id' => $bpi->id, 'name' => 'Astin Tech tax buffer', 'icon' => '🧾', 'target_amount' => 50000, 'saved_amount' => 20000, 'deadline' => now()->addMonths(5)->toDateString()]);

        // ---- Budgets / allowance -----------------------------------------------
        Budget::create(['profile_id' => $personal->id, 'name' => 'Weekly allowance', 'category' => 'allowance', 'period' => 'weekly', 'amount' => 2500, 'icon' => '💸']);
        Budget::create(['profile_id' => $personal->id, 'name' => 'Food & groceries', 'category' => 'food', 'period' => 'monthly', 'amount' => 6000, 'icon' => '🍜']);
        Budget::create(['profile_id' => $personal->id, 'name' => 'Transport', 'category' => 'transport', 'period' => 'monthly', 'amount' => 2000, 'icon' => '🚌']);
        Budget::create(['profile_id' => $personal->id, 'name' => 'Subscriptions', 'category' => 'subscription', 'period' => 'monthly', 'amount' => 6000, 'icon' => '🔁']);
        Budget::create(['profile_id' => $business->id, 'name' => 'Business tools', 'category' => 'business', 'period' => 'monthly', 'amount' => 10000, 'icon' => '🧰']);

        // ---- Some transactions this month (balances already reflect these) -----
        $tx = [
            [$personal, $gcash, 'income', 'client_payment', 25000, 9, 'Client payment - web app milestone'],
            [$business, $bpi, 'income', 'client_payment', 48000, 6, 'Astin Tech - landing page project'],
            [$personal, $gcash, 'expense', 'allowance', 450, 1, 'Lunch + coffee'],
            [$personal, $cash, 'expense', 'allowance', 320, 2, 'Jeep + tricycle fare'],
            [$personal, $gcash, 'expense', 'food', 1850, 3, 'Groceries - SM Supermarket'],
            [$personal, $maya, 'expense', 'allowance', 600, 0, 'Dinner out'],
            [$personal, $gcash, 'expense', 'transport', 380, 4, 'Grab to client meeting'],
            [$personal, $bpiCc, 'expense', 'subscription', 1170, 5, 'Claude Pro (USD 20)'],
            [$personal, $gcash, 'expense', 'loan', 1985, 25, 'GLoan monthly amortization'],
            [$personal, $gcash, 'expense', 'housing', 8500, 28, 'Apartment rent'],
        ];
        foreach ($tx as [$profile, $acct, $type, $cat, $amount, $daysAgo, $desc]) {
            Transaction::create([
                'profile_id' => $profile->id, 'account_id' => $acct->id, 'type' => $type, 'category' => $cat,
                'amount' => $amount, 'date' => now()->subDays($daysAgo)->toDateString(), 'description' => $desc,
            ]);
        }
    }
}
