<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        // Identidad
        'legal_name', 'tax_id', 'logo_path', 'phone',
        'address', 'zip', 'city', 'country',

        // Configuración general
        'currency_code', 'locale', 'timezone', 'pdf_template',

        // Datos bancarios / notas de cobro
        'bank_name', 'bank_holder', 'bank_account', 'billing_notes',
        'show_bank_on_invoices', 'show_bank_on_budgets',

        // Recordatorios
        'reminders_enabled',
        'reminder_days_before_first',
        'reminder_days_after_due',
        'reminder_repeat_every_days',
        'reminder_max_times',
    ];

    protected $casts = [
        'reminders_enabled'          => 'boolean',
        'reminder_days_before_first' => 'int',
        'reminder_days_after_due'    => 'int',
        'reminder_repeat_every_days' => 'int',
        'reminder_max_times'         => 'int',

        'show_bank_on_invoices' => 'boolean',
        'show_bank_on_budgets'  => 'boolean',
    ];

    protected $attributes = [
        'pdf_template'                => 'classic',
        'reminders_enabled'           => false,
        'reminder_days_before_first'  => 7,
        'reminder_days_after_due'     => 1,
        'reminder_repeat_every_days'  => 7,
        'reminder_max_times'          => 3,
    ];

    /* ========== Relaciones ========== */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ========== Scopes ========== */
    public function scopeForAuthUser($q)
    {
        return $q->where('user_id', auth()->id());
    }

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    /* ========== Helpers ========== */
    public function getDisplayNameAttribute(): string
    {
        return $this->legal_name ?: optional($this->user)->name ?: 'Mi Empresa';
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) return null;

        if (Storage::disk('public')->exists($this->logo_path)) {
            return Storage::disk('public')->url($this->logo_path);
        }
        return asset($this->logo_path);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            trim(($this->zip ? $this->zip.' ' : '').($this->city ?: '')),
            $this->country,
        ]);
        return implode(' · ', $parts);
    }

    public function getCurrencyAttribute(): string
    {
        return $this->currency_code ?: 'EUR';
    }

    public function reminderConfig(): array
    {
        return [
            'enabled'           => (bool) $this->reminders_enabled,
            'days_before_first' => max(0, (int) ($this->reminder_days_before_first ?? 7)),
            'days_after_due'    => max(0, (int) ($this->reminder_days_after_due ?? 1)),
            'repeat_every'      => max(1, (int) ($this->reminder_repeat_every_days ?? 7)),
            'max_times'         => max(1, (int) ($this->reminder_max_times ?? 3)),
        ];
    }

    public function remindersEnabled(): bool
    {
        return (bool) $this->reminders_enabled;
    }
}
