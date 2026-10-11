<?php

namespace App\Livewire\Subscriptions;

use App\Livewire\Bills\BaseBills;
use App\Models\Setting;
use Livewire\Attributes\Title;

#[Title('Subscriptions')]
class Index extends BaseBills
{
    public string $typeFilter = 'all';

    protected function isSubscriptionPage(): bool
    {
        return true;
    }

    protected function viewName(): string
    {
        return 'livewire.subscriptions.index';
    }

    public function render()
    {
        $data = $this->sharedViewData();

        if ($this->typeFilter !== 'all') {
            $data['bills'] = $data['bills']->where('subcategory', $this->typeFilter);
        }

        $active = $this->baseQuery()->active()->get();
        $data['byType'] = $active->groupBy(fn ($b) => $b->subcategory ?? 'other')->map(fn ($g) => $g->sum->monthlyCostPhp())->sortDesc();
        $data['usdMonthly'] = $active->where('currency', 'USD')->sum(fn ($b) => $b->monthlyCostPhp() / Setting::usdRate());
        $data['usdRate'] = Setting::usdRate();

        return view($this->viewName(), $data);
    }
}
