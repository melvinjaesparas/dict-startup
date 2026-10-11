<?php

namespace App\Livewire\Concerns;

use App\Models\Profile;

trait HasFormModal
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->resetValidation();
    }

    /** Profile to preselect in forms: the one chosen in the sidebar, else the first one. */
    protected function defaultProfileId(): int
    {
        return current_profile_id() ?? (int) Profile::query()->orderBy('id')->value('id');
    }

    protected function toast(string $message, string $type = 'success'): void
    {
        $this->dispatch('toast', message: $message, type: $type);
    }
}
