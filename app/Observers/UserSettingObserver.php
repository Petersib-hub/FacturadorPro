<?php

namespace App\Observers;

use App\Models\UserSetting;
use Illuminate\Support\Facades\Storage;

class UserSettingObserver
{
    /**
     * Si cambia el logo, elimina el anterior del disco (si ya no se usa).
     */
    public function updating(UserSetting $model): void
    {
        if ($model->isDirty('logo_path')) {
            $original = $model->getOriginal('logo_path');
            if ($original && $original !== $model->logo_path) {
                // Evita borrar si el mismo archivo lo usan otros tenants (poco probable si nombras por user_id)
                if (Storage::disk('public')->exists($original)) {
                    Storage::disk('public')->delete($original);
                }
            }
        }
    }

    /**
     * Hook opcional para registrar auditoría simple (placeholder).
     * Más adelante, cambiaremos a spatie/laravel-activitylog.
     */
    public function updated(UserSetting $model): void
    {
        // logger()->info('UserSetting updated', [
        //     'user_id' => $model->user_id,
        //     'changed' => $model->getChanges(),
        // ]);
    }
}
