<?php

namespace App\Livewire\Bills;

use Livewire\Attributes\Title;

#[Title('Bills & Dues')]
class Index extends BaseBills
{
    public string $categoryFilter = 'all';

    protected function isSubscriptionPage(): bool
    {
        return false;
    }

    protected function viewName(): string
    {
        return 'livewire.bills.index';
    }

    public function render()
    {
        $data = $this->sharedViewData();

        if ($this->categoryFilter !== 'all') {
            $data['bills'] = $data['bills']->where('category', $this->categoryFilter);
        }

        $data['byCategory'] = $this->baseQuery()->active()->get()->groupBy('category')->map(fn ($g) => $g->sum->monthlyCostPhp())->sortDesc();

        return view($this->viewName(), $data);
    }
}
