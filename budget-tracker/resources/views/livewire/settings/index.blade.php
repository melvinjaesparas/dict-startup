<div class="grid gap-6 lg:grid-cols-2">
    <form wire:submit="saveGeneral" class="card card-pad space-y-4">
        <h2 class="font-bold">General</h2>
        <x-field label="USD → PHP rate" for="usd_php_rate" hint="Used to convert USD subscriptions (Claude, ChatGPT, Cursor…) into pesos. Update it when the rate moves.">
            <input id="usd_php_rate" type="number" step="0.01" min="1" wire:model="usd_php_rate" class="input">
        </x-field>
        <x-field label="Dashboard reminder window (days)" for="reminder_days" hint="Dues inside this window show under “Due soon” on the dashboard.">
            <input id="reminder_days" type="number" min="1" max="60" wire:model="reminder_days" class="input">
        </x-field>
        <div class="flex justify-end"><button class="btn-primary">Save</button></div>
    </form>

    <div class="card card-pad space-y-4">
        <h2 class="font-bold">Profiles</h2>
        <p class="text-xs text-slate-500">Separate personal money from Astin Tech Solutions. Every account, loan, bill, and transaction belongs to one profile.</p>
        <form wire:submit="saveProfiles" class="space-y-3">
            @foreach ($profiles as $i => $p)
                <div class="grid grid-cols-[1fr_auto_auto_auto] items-end gap-2" wire:key="profile-{{ $p['id'] }}">
                    <x-field label="Name" error="profiles.{{ $i }}.name"><input wire:model="profiles.{{ $i }}.name" class="input"></x-field>
                    <x-field label="Type"><select wire:model="profiles.{{ $i }}.type" class="input"><option value="personal">Personal</option><option value="business">Business</option></select></x-field>
                    <x-field label="Colour"><input type="color" wire:model="profiles.{{ $i }}.color" class="h-9 w-12 rounded border border-slate-300"></x-field>
                    <button type="button" class="btn-ghost btn-sm mb-1 text-red-600" wire:click="deleteProfile({{ $p['id'] }})" wire:confirm="Delete this profile?">✕</button>
                </div>
            @endforeach
            <div class="flex justify-end"><button class="btn-primary">Save profiles</button></div>
        </form>
        <form wire:submit="addProfile" class="flex items-end gap-2 border-t border-slate-100 pt-4">
            <x-field label="New profile" for="new_profile_name" class="flex-1"><input id="new_profile_name" wire:model="new_profile_name" class="input" placeholder="e.g. Side project"></x-field>
            <x-field label="Type" for="new_profile_type"><select id="new_profile_type" wire:model="new_profile_type" class="input"><option value="personal">Personal</option><option value="business">Business</option></select></x-field>
            <button class="btn-secondary">Add</button>
        </form>
    </div>

    <form wire:submit="saveAccount" class="card card-pad space-y-4">
        <h2 class="font-bold">Your login</h2>
        <x-field label="Name" for="user_name"><input id="user_name" wire:model="user_name" class="input"></x-field>
        <x-field label="Email" for="user_email"><input id="user_email" type="email" wire:model="user_email" class="input"></x-field>
        <div class="flex justify-end"><button class="btn-primary">Save</button></div>
    </form>

    <form wire:submit="changePassword" class="card card-pad space-y-4">
        <h2 class="font-bold">Change password</h2>
        <x-field label="Current password" for="current_password"><input id="current_password" type="password" wire:model="current_password" class="input" autocomplete="current-password"></x-field>
        <div class="grid grid-cols-2 gap-4">
            <x-field label="New password" for="new_password"><input id="new_password" type="password" wire:model="new_password" class="input" autocomplete="new-password"></x-field>
            <x-field label="Confirm" for="new_password_confirmation"><input id="new_password_confirmation" type="password" wire:model="new_password_confirmation" class="input" autocomplete="new-password"></x-field>
        </div>
        <div class="flex justify-end"><button class="btn-primary">Change password</button></div>
    </form>
</div>
