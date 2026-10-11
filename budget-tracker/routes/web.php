<?php

use App\Livewire\Accounts\Index as AccountsIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Bills\Index as BillsIndex;
use App\Livewire\Budgets\Index as BudgetsIndex;
use App\Livewire\Dashboard;
use App\Livewire\Institutions\Index as InstitutionsIndex;
use App\Livewire\Loans\Index as LoansIndex;
use App\Livewire\Savings\Index as SavingsIndex;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Subscriptions\Index as SubscriptionsIndex;
use App\Livewire\Transactions\Index as TransactionsIndex;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/accounts', AccountsIndex::class)->name('accounts');
    Route::get('/loans', LoansIndex::class)->name('loans');
    Route::get('/bills', BillsIndex::class)->name('bills');
    Route::get('/subscriptions', SubscriptionsIndex::class)->name('subscriptions');
    Route::get('/transactions', TransactionsIndex::class)->name('transactions');
    Route::get('/savings', SavingsIndex::class)->name('savings');
    Route::get('/budgets', BudgetsIndex::class)->name('budgets');
    Route::get('/institutions', InstitutionsIndex::class)->name('institutions');
    Route::get('/settings', SettingsIndex::class)->name('settings');

    Route::post('/profile/switch', function (Request $request) {
        $id = $request->input('profile_id');
        if ($id === 'all' || ! Profile::whereKey($id)->exists()) {
            $request->session()->forget('profile_id');
        } else {
            $request->session()->put('profile_id', (int) $id);
        }

        return back();
    })->name('profile.switch');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
