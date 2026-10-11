<?php

namespace App\Livewire\Settings;

use App\Models\Profile;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Settings')]
class Index extends Component
{
    public string $usd_php_rate = '';

    public string $reminder_days = '';

    public array $profiles = [];

    public string $user_name = '';

    public string $user_email = '';

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public string $new_profile_name = '';

    public string $new_profile_type = 'personal';

    public function mount(): void
    {
        $this->usd_php_rate = (string) Setting::usdRate();
        $this->reminder_days = (string) Setting::get('reminder_days', 7);
        $this->loadProfiles();
        $this->user_name = auth()->user()->name;
        $this->user_email = auth()->user()->email;
    }

    protected function loadProfiles(): void
    {
        $this->profiles = Profile::orderBy('id')->get()->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'type' => $p->type, 'color' => $p->color])->all();
    }

    public function saveGeneral(): void
    {
        $this->validate([
            'usd_php_rate' => ['required', 'numeric', 'min:1', 'max:1000'],
            'reminder_days' => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        Setting::set('usd_php_rate', $this->usd_php_rate);
        Setting::set('reminder_days', $this->reminder_days);
        $this->dispatch('toast', message: 'Settings saved');
    }

    public function saveProfiles(): void
    {
        $this->validate([
            'profiles.*.name' => ['required', 'string', 'max:60'],
            'profiles.*.type' => ['required', 'in:personal,business'],
            'profiles.*.color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        foreach ($this->profiles as $row) {
            Profile::whereKey($row['id'])->update(['name' => $row['name'], 'type' => $row['type'], 'color' => $row['color']]);
        }

        $this->dispatch('toast', message: 'Profiles saved');
    }

    public function addProfile(): void
    {
        $this->validate(['new_profile_name' => ['required', 'string', 'max:60'], 'new_profile_type' => ['required', 'in:personal,business']]);

        $base = Str::slug($this->new_profile_name) ?: 'profile';
        $slug = $base;
        $n = 1;
        while (Profile::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$n);
        }

        Profile::create(['name' => $this->new_profile_name, 'slug' => $slug, 'type' => $this->new_profile_type, 'color' => $this->new_profile_type === 'business' ? '#7c3aed' : '#2563eb']);
        $this->new_profile_name = '';
        $this->loadProfiles();
        $this->dispatch('toast', message: 'Profile added');
    }

    public function deleteProfile(int $id): void
    {
        if (Profile::count() <= 1) {
            $this->dispatch('toast', message: 'Keep at least one profile.', type: 'error');

            return;
        }

        $p = Profile::withCount(['accounts', 'loans', 'bills', 'transactions'])->findOrFail($id);
        if ($p->accounts_count || $p->loans_count || $p->bills_count || $p->transactions_count) {
            $this->dispatch('toast', message: 'This profile still has data. Move or delete it first.', type: 'error');

            return;
        }

        $p->delete();
        if (session('profile_id') == $id) {
            session()->forget('profile_id');
        }
        $this->loadProfiles();
        $this->dispatch('toast', message: 'Profile deleted');
    }

    public function saveAccount(): void
    {
        $user = auth()->user();

        $this->validate([
            'user_name' => ['required', 'string', 'max:80'],
            'user_email' => ['required', 'email', 'max:120', 'unique:users,email,'.$user->id],
        ]);

        $user->update(['name' => $this->user_name, 'email' => $this->user_email]);
        $this->dispatch('toast', message: 'Account updated');
    }

    public function changePassword(): void
    {
        $this->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'Current password is incorrect.');

            return;
        }

        auth()->user()->update(['password' => $this->new_password]);
        $this->reset('current_password', 'new_password', 'new_password_confirmation');
        $this->dispatch('toast', message: 'Password changed');
    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}
