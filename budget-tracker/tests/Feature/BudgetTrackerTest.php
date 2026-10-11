<?php

namespace Tests\Feature;

use App\Livewire\Accounts\Index as AccountsIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Bills\Index as BillsIndex;
use App\Livewire\Dashboard;
use App\Livewire\Loans\Index as LoansIndex;
use App\Livewire\Savings\Index as SavingsIndex;
use App\Livewire\Transactions\Index as TransactionsIndex;
use App\Models\Account;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Loan;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BudgetTrackerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->user = User::first();
    }

    public static function pages(): array
    {
        return array_map(fn ($p) => [$p], ['/', '/accounts', '/loans', '/bills', '/subscriptions', '/transactions', '/savings', '/budgets', '/institutions', '/settings']);
    }

    #[Test]
    public function guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Sign in');
    }

    #[Test]
    #[DataProvider('pages')]
    public function every_page_renders(string $path): void
    {
        $this->actingAs($this->user)->get($path)->assertOk();
        $this->actingAs($this->user)->withSession(['profile_id' => 1])->get($path)->assertOk();
    }

    #[Test]
    public function profile_switch_is_stored_in_session(): void
    {
        $this->actingAs($this->user)->from('/')->post('/profile/switch', ['profile_id' => 2])->assertRedirect('/')->assertSessionHas('profile_id', 2);
        $this->actingAs($this->user)->withSession(['profile_id' => 2])->post('/profile/switch', ['profile_id' => 'all'])->assertSessionMissing('profile_id');
    }

    #[Test]
    public function paying_a_bill_debits_the_account_and_rolls_the_due_date(): void
    {
        $bill = Bill::where('name', 'Electricity')->firstOrFail();
        $account = $bill->account;
        $before = (float) $account->balance;
        $due = $bill->next_due_date->copy();

        Livewire::actingAs($this->user)->test(BillsIndex::class)
            ->call('openPayment', $bill->id)
            ->assertSet('pay_amount', (string) $bill->amountInPhp())
            ->call('recordPayment')
            ->assertHasNoErrors();

        $bill->refresh();
        $this->assertSame(round($before - 2350, 2), (float) $account->fresh()->balance);
        $this->assertSame($due->addMonthNoOverflow()->toDateString(), $bill->next_due_date->toDateString());
        $this->assertSame(1, $bill->payments()->count());
        $this->assertSame(1, Transaction::where('reference_type', BillPayment::class)->count());
    }

    #[Test]
    public function usd_subscriptions_are_converted_with_the_stored_rate(): void
    {
        $claude = Bill::where('name', 'Claude Pro')->firstOrFail();
        $this->assertSame(1170.0, $claude->amountInPhp()); // 20 * 58.50
        $this->assertSame(1170.0, $claude->monthlyCostPhp());
    }

    #[Test]
    public function loan_payment_reduces_outstanding_balance(): void
    {
        $loan = Loan::where('name', 'GLoan')->firstOrFail();
        $accountBefore = (float) $loan->account->balance;
        $due = $loan->next_due_date->copy();

        Livewire::actingAs($this->user)->test(LoansIndex::class)
            ->call('openPayment', $loan->id)
            ->set('pay_amount', '1985')
            ->call('recordPayment')
            ->assertHasNoErrors();

        $loan->refresh();
        $this->assertSame(13340 - 1985.0, (float) $loan->outstanding_balance);
        $this->assertSame(round($accountBefore - 1985, 2), (float) $loan->account->fresh()->balance);
        $this->assertSame(1, $loan->payments()->count());
        $this->assertSame(15, $loan->next_due_date->day);
        $this->assertTrue($loan->next_due_date->greaterThan($due));
    }

    #[Test]
    public function installment_is_marked_paid_when_balance_hits_zero(): void
    {
        $loan = Loan::where('name', 'like', 'Atome%')->firstOrFail();
        $loan->recordPayment(2500, null, now());
        $this->assertSame('paid', $loan->fresh()->status);
    }

    #[Test]
    public function transfers_move_money_between_accounts(): void
    {
        $from = Account::where('name', 'GCash Wallet')->firstOrFail();
        $to = Account::where('name', 'Maribank Savings')->firstOrFail();

        Livewire::actingAs($this->user)->test(AccountsIndex::class)
            ->call('openTransfer', $from->id)
            ->set('to_account_id', $to->id)
            ->set('transfer_amount', '1000')
            ->call('transfer')
            ->assertHasNoErrors();

        $this->assertSame(3250.75, (float) $from->fresh()->balance);
        $this->assertSame(33500.0, (float) $to->fresh()->balance);
    }

    #[Test]
    public function transactions_can_be_added_edited_and_deleted_with_balance_effects(): void
    {
        $account = Account::where('name', 'GCash Wallet')->firstOrFail();
        $start = (float) $account->balance;

        $c = Livewire::actingAs($this->user)->test(TransactionsIndex::class)
            ->call('openForm', null, 'expense')
            ->set('account_id', $account->id)
            ->set('amount', '250')
            ->set('category', 'allowance')
            ->set('description', 'Snacks')
            ->call('save')->assertHasNoErrors();

        $tx = Transaction::where('description', 'Snacks')->firstOrFail();
        $this->assertSame(round($start - 250, 2), (float) $account->fresh()->balance);

        $c->call('openForm', $tx->id)->set('amount', '100')->call('save')->assertHasNoErrors();
        $this->assertSame(round($start - 100, 2), (float) $account->fresh()->balance);

        $c->call('delete', Transaction::where('description', 'Snacks')->value('id'));
        $this->assertSame($start, (float) $account->fresh()->balance);
    }

    #[Test]
    public function credit_card_expenses_increase_what_is_owed(): void
    {
        $card = Account::where('name', 'BPI Credit Card')->firstOrFail();
        $before = (float) $card->balance;
        Transaction::record(['profile_id' => $card->profile_id, 'account_id' => $card->id, 'type' => 'expense', 'category' => 'subscription', 'amount' => 100, 'date' => now()]);
        $this->assertSame($before + 100, (float) $card->fresh()->balance);
    }

    #[Test]
    public function savings_deposit_moves_money_into_the_linked_account(): void
    {
        $goal = SavingsGoal::where('name', 'Emergency fund')->firstOrFail();
        $from = Account::where('name', 'GCash Wallet')->firstOrFail();
        $linked = $goal->account;
        $linkedBefore = (float) $linked->balance;

        Livewire::actingAs($this->user)->test(SavingsIndex::class)
            ->call('openDeposit', $goal->id)
            ->set('dep_amount', '500')
            ->set('dep_from_account_id', $from->id)
            ->call('deposit')->assertHasNoErrors();

        $this->assertSame(33000.0, (float) $goal->fresh()->saved_amount);
        $this->assertSame(3750.75, (float) $from->fresh()->balance);
        $this->assertSame($linkedBefore + 500, (float) $linked->fresh()->balance);
    }

    #[Test]
    public function dues_can_be_paid_from_the_dashboard(): void
    {
        $bill = Bill::where('name', 'Fiber internet')->firstOrFail();
        $loan = Loan::where('name', 'GCredit')->firstOrFail();

        Livewire::actingAs($this->user)->test(Dashboard::class)
            ->assertSee('reminders')
            ->call('payBill', $bill->id)
            ->call('payLoan', $loan->id);

        $this->assertSame(1, $bill->fresh()->payments()->count());
        $this->assertSame(0.0, (float) $loan->fresh()->outstanding_balance);
    }

    #[Test]
    public function login_rejects_bad_credentials_and_accepts_the_seeded_user(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'justine@astintech.local')->set('password', 'wrong')
            ->call('login')->assertHasErrors('email');

        Livewire::test(Login::class)
            ->set('email', 'justine@astintech.local')->set('password', 'password')
            ->call('login')->assertHasNoErrors()->assertRedirect('/');
        $this->assertAuthenticated();
    }
}
