@php $isSub = $category === 'subscription'; @endphp
<x-modal :title="($editingId ? 'Edit ' : 'Add ').($isSub ? 'subscription' : 'bill')" maxWidth="max-w-2xl">
    <form wire:submit="save" class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-[1fr_auto_auto]">
            <x-field label="Name" for="name"><input id="name" wire:model="name" class="input" placeholder="{{ $isSub ? 'e.g. Claude Pro, ChatGPT Plus, Cursor' : 'e.g. Meralco, Rent, Internet' }}"></x-field>
            <x-field label="Icon" for="icon"><input id="icon" wire:model="icon" maxlength="4" class="input w-20 text-center text-lg" placeholder="🧾"></x-field>
            <x-field label="Profile" for="profile_id">
                <select id="profile_id" wire:model="profile_id" class="input">@foreach ($profiles as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
            </x-field>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <x-field label="Vendor / payee" for="vendor"><input id="vendor" wire:model="vendor" class="input" placeholder="{{ $isSub ? 'Anthropic, OpenAI…' : 'Meralco, Maynilad…' }}"></x-field>
            @if ($isSub)
                <x-field label="Subscription type" for="subcategory">
                    <select id="subcategory" wire:model="subcategory" class="input">@foreach (\App\Models\Bill::SUBSCRIPTION_TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                </x-field>
            @else
                <x-field label="Category" for="category">
                    <select id="category" wire:model="category" class="input">@foreach (\App\Models\Bill::CATEGORIES as $k => $v)@if ($k !== 'subscription')<option value="{{ $k }}">{{ $v }}</option>@endif @endforeach</select>
                </x-field>
            @endif
            <x-field label="Frequency" for="frequency">
                <select id="frequency" wire:model="frequency" class="input">@foreach (\App\Models\Bill::FREQUENCIES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
            </x-field>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <x-field label="Amount" for="amount">
                <div class="flex">
                    <select wire:model="currency" class="input w-24 rounded-r-none border-r-0"><option value="PHP">₱ PHP</option><option value="USD">$ USD</option></select>
                    <input id="amount" type="number" step="0.01" min="0" wire:model="amount" class="input rounded-l-none">
                </div>
            </x-field>
            <x-field label="Next due date" for="next_due_date"><input id="next_due_date" type="date" wire:model="next_due_date" class="input"></x-field>
            <x-field label="Paid from" for="account_id">
                <select id="account_id" wire:model="account_id" class="input">
                    <option value="">— none —</option>
                    @foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->institution->name }} · {{ $a->name }}</option>@endforeach
                </select>
            </x-field>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field label="Show logo of (optional)" for="institution_id" hint="Pick a bank/e-wallet to show its logo instead of the icon.">
                <select id="institution_id" wire:model="institution_id" class="input">
                    <option value="">— use icon —</option>
                    @foreach ($institutions as $i)<option value="{{ $i->id }}">{{ $i->name }}</option>@endforeach
                </select>
            </x-field>
            <x-field label="Website / billing page" for="website"><input id="website" wire:model="website" class="input" placeholder="https://"></x-field>
        </div>
        <div class="flex flex-wrap gap-4 text-sm">
            <label class="flex items-center gap-2"><input type="checkbox" wire:model="auto_pay" class="rounded"> Auto-charged (auto-debit)</label>
            <label class="flex items-center gap-2"><input type="checkbox" wire:model="is_active" class="rounded"> Active</label>
        </div>
        <x-field label="Notes" for="notes"><textarea id="notes" wire:model="notes" rows="2" class="input"></textarea></x-field>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" class="btn-secondary" wire:click="closeForm">Cancel</button>
            <button type="submit" class="btn-primary">{{ $editingId ? 'Save changes' : 'Add' }}</button>
        </div>
    </form>
</x-modal>
