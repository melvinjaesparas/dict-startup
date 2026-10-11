<div class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 p-4 backdrop-blur-sm sm:items-center" x-data x-on:keydown.escape.window="$wire.closePayment()">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl" @click.outside="$wire.closePayment()">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="font-bold">Mark as paid</h2>
            <button type="button" wire:click="closePayment" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">✕</button>
        </div>
        <form wire:submit="recordPayment" class="space-y-4 px-5 py-4">
            <div class="grid grid-cols-2 gap-4">
                <x-field label="Amount paid (₱)" for="pay_amount"><input id="pay_amount" type="number" step="0.01" min="0" wire:model="pay_amount" class="input"></x-field>
                <x-field label="Date paid" for="pay_date"><input id="pay_date" type="date" wire:model="pay_date" class="input"></x-field>
            </div>
            <x-field label="Paid from account" for="pay_account_id" hint="Deducts from this account and logs a transaction. Leave empty to only roll the due date.">
                <select id="pay_account_id" wire:model="pay_account_id" class="input">
                    <option value="">— don't touch any account —</option>
                    @foreach ($accounts as $a)<option value="{{ $a->id }}">{{ $a->institution->name }} · {{ $a->name }} ({{ money($a->balance) }})</option>@endforeach
                </select>
            </x-field>
            <x-field label="Note" for="pay_note"><input id="pay_note" wire:model="pay_note" class="input" placeholder="Reference no., etc."></x-field>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn-secondary" wire:click="closePayment">Cancel</button>
                <button type="submit" class="btn-primary">Mark paid</button>
            </div>
        </form>
    </div>
</div>
