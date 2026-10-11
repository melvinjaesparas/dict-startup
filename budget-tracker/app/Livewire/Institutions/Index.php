<?php

namespace App\Livewire\Institutions;

use App\Livewire\Concerns\HasFormModal;
use App\Models\Institution;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Banks & E-wallets')]
class Index extends Component
{
    use HasFormModal, WithFileUploads;

    public string $name = '';

    public string $type = 'bank';

    public string $color = '#334155';

    public string $text_color = '#ffffff';

    public string $short_code = '';

    public $logo = null;

    public bool $remove_logo = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'type' => ['required', 'in:'.implode(',', array_keys(Institution::TYPES))],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'text_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'short_code' => ['nullable', 'string', 'max:6'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:1024'],
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->editingId = $id;
        $this->logo = null;
        $this->remove_logo = false;

        if ($id) {
            $i = Institution::findOrFail($id);
            $this->fill(['name' => $i->name, 'type' => $i->type, 'color' => $i->color, 'text_color' => $i->text_color, 'short_code' => (string) $i->short_code]);
        } else {
            $this->fill(['name' => '', 'type' => 'bank', 'color' => '#334155', 'text_color' => '#ffffff', 'short_code' => '']);
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        unset($data['logo']);
        $data['short_code'] = $data['short_code'] ?: null;

        $institution = $this->editingId ? Institution::findOrFail($this->editingId) : new Institution;

        if (! $this->editingId) {
            $base = Str::slug($data['name']) ?: 'institution';
            $slug = $base;
            $n = 1;
            while (Institution::where('slug', $slug)->exists()) {
                $slug = $base.'-'.(++$n);
            }
            $data['slug'] = $slug;
            $data['sort_order'] = 50;
        }

        if ($this->logo) {
            $data['logo_path'] = $this->logo->store('institution-logos', 'public');
        } elseif ($this->remove_logo) {
            $data['logo_path'] = null;
        }

        $institution->fill($data)->save();
        $this->toast($this->editingId ? 'Updated' : 'Added '.$institution->name);
        $this->closeForm();
    }

    public function delete(int $id): void
    {
        $i = Institution::withCount(['accounts', 'loans'])->findOrFail($id);

        if ($i->accounts_count || $i->loans_count) {
            $this->toast('Remove its accounts and loans first.', 'error');

            return;
        }

        $i->delete();
        $this->toast('Deleted');
    }

    public function render()
    {
        return view('livewire.institutions.index', [
            'institutions' => Institution::withCount(['accounts', 'loans'])->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }
}
