<?php

namespace App\Support;

use App\Models\UserSetting;

class Branding
{
    /** Logo global de la app (layout, fallback) */
    public static function appLogoUrl(): string
    {
        return asset('logo_facturador.png');
    }

    /** URL pública del logo del tenant para web/emails (fallback al global) */
    public static function tenantLogoUrl(?int $userId): string
    {
        if ($userId) {
            $s = UserSetting::firstWhere('user_id', $userId);
            if ($s?->logo_path) {
                return asset('storage/' . ltrim($s->logo_path, '/'));
            }
        }
        return self::appLogoUrl();
    }

    /** Ruta absoluta del logo para DOMPDF (usa public_path). Fallback al global. */
    public static function tenantLogoDiskPathForPdf(?int $userId): string
    {
        if ($userId) {
            $s = UserSetting::firstWhere('user_id', $userId);
            if ($s?->logo_path) {
                $path = public_path('storage/' . ltrim($s->logo_path, '/'));
                if (file_exists($path)) {
                    return $path;
                }
            }
        }
        return public_path('logo_facturador.png');
    }
}

