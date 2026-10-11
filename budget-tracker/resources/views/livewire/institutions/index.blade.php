<div class="space-y-6">
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-600">Banks, e-wallets, and lenders you use. Add anything else manually with its own logo and colour.</p>
        <button class="btn-primary" wire:click="openForm">+ Add bank / e-wallet</button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($institutions as $i)
            <div class="card card-pad flex items-center gap-4" wire:key="inst-{{ $i->id }}">
                <x-logo :institution="$i" size="h-14 w-14" rounded="rounded-2xl" />
                <div class="min-w-0 flex-1">
                    <div class="truncate font-bold">{{ $i->name }}</div>
                    <div class="text-xs text-slate-500">{{ $i->typeLabel() }}</div>
                    <div class="mt-1 text-[11px] text-slate-500">{{ $i->accounts_count }} {{ Str::plural('account', $i->accounts_count) }} · {{ $i->loans_count }} {{ Str::plural('loan', $i->loans_count) }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <button class="btn-ghost btn-sm" wire:click="openForm({{ $i->id }})">Edit</button>
                    <button class="btn-ghost btn-sm text-red-600" wire:click="delete({{ $i->id }})" wire:confirm="Delete {{ $i->name }}?">Delete</button>
                </div>
            </div>
        @endforeach
    </div>

    @if ($showForm)
        <x-modal :title="$editingId ? 'Edit bank / e-wallet' : 'Add bank / e-wallet'">
            <form wire:submit="save" class="space-y-4">
                <x-field label="Name" for="name"><input id="name" wire:model="name" class="input" placeholder="e.g. SeaBank, Tonik, Home Credit"></x-field>
                <div class="grid grid-cols-2 gap-4">
                    <x-field label="Type" for="type">
                        <select id="type" wire:model="type" class="input">
                            @foreach (\App\Models\Institution::TYPES as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                        </select>
                    </x-field>
                    <x-field label="Short code" for="short_code" hint="Shown when there is no logo."><input id="short_code" wire:model="short_code" maxlength="6" class="input" placeholder="e.g. SB"></x-field>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <x-field label="Brand colour" for="color">
                        <div class="flex items-center gap-2"><input type="color" wire:model.live="color" class="h-9 w-12 rounded border border-slate-300"><input id="color" wire:model.live="color" class="input"></div>
                    </x-field>
                    <x-field label="Text colour" for="text_color">
                        <div class="flex items-center gap-2"><input type="color" wire:model.live="text_color" class="h-9 w-12 rounded border border-slate-300"><input id="text_color" wire:model.live="text_color" class="input"></div>
                    </x-field>
                </div>
                <x-field label="Logo (PNG, JPG, SVG, WebP · max 1MB)" for="logo">
                    <input id="logo" type="file" wire:model="logo" accept="image/*" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold">
                    <div class="mt-2 flex items-center gap-3">
                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" class="h-12 w-12 rounded-xl object-cover" alt="Preview">
                            <span class="text-xs text-slate-500">New logo preview</span>
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl text-sm font-bold" style="background: {{ $color }}; color: {{ $text_color }}">{{ $short_code ?: strtoupper(mb_substr($name, 0, 2)) ?: 'AB' }}</div>
                            <span class="text-xs text-slate-500">Fallback badge preview</span>
                        @endif
                        <span wire:loading wire:target="logo" class="text-xs text-slate-500">Uploading…</span>
                    </div>
                    @if ($editingId)
                        <label class="mt-2 flex items-center gap-2 text-xs text-slate-600"><input type="checkbox" wire:model="remove_logo" class="rounded"> Remove current logo and use the badge</label>
                    @endif
                </x-field>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-secondary" wire:click="closeForm">Cancel</button>
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="logo,save">Save</button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
