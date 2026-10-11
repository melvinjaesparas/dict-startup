<?php

use App\Models\Profile;
use App\Models\Setting;

if (! function_exists('money')) {
    /** Format a PHP peso amount, e.g. ₱12,345.00 */
    function money(float|int|string|null $amount, string $currency = 'PHP'): string
    {
        $amount = (float) ($amount ?? 0);
        $symbol = match ($currency) {
            'USD' => '$',
            'EUR' => '€',
            default => '₱',
        };
        $sign = $amount < 0 ? '-' : '';

        return $sign.$symbol.number_format(abs($amount), 2);
    }
}

if (! function_exists('to_php')) {
    /** Convert an amount in the given currency into PHP using the stored rate. */
    function to_php(float|int|string|null $amount, string $currency = 'PHP'): float
    {
        $amount = (float) ($amount ?? 0);

        return match ($currency) {
            'USD' => round($amount * Setting::usdRate(), 2),
            default => $amount,
        };
    }
}

if (! function_exists('current_profile_id')) {
    /** The profile currently selected in the sidebar switcher (null = all profiles). */
    function current_profile_id(): ?int
    {
        $id = session('profile_id');

        if ($id && Profile::whereKey($id)->exists()) {
            return (int) $id;
        }

        return null;
    }
}
