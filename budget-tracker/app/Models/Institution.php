<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    public const TYPES = [
        'ewallet' => 'E-wallet',
        'digital_bank' => 'Digital bank',
        'bank' => 'Bank',
        'bnpl' => 'Buy now, pay later',
        'lender' => 'Lender',
        'other' => 'Other',
    ];

    protected $fillable = [
        'name', 'slug', 'type', 'color', 'text_color', 'logo_path', 'short_code', 'sort_order', 'is_system',
    ];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        if (str_starts_with($this->logo_path, 'logos/')) {
            return asset($this->logo_path);
        }

        return asset('storage/'.$this->logo_path);
    }

    public function initials(): string
    {
        if ($this->short_code) {
            return $this->short_code;
        }

        return strtoupper(mb_substr($this->name, 0, 2));
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
