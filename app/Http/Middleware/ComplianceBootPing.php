<?php

namespace App\Http\Middleware;

use App\Support\Compliance;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ComplianceBootPing
{
    public function handle(Request $request, Closure $next)
    {
        $key = 'compliance:boot:' . now()->format('Ymd');
        if (Cache::add($key, 1, now()->addDay())) {
            $vf = filter_var(config('verifactu.enabled', env('VERIFACTU_ENABLED', false)), FILTER_VALIDATE_BOOL);
            if ($vf) {
                Compliance::log('art. 9.1.a', 'VERIFACTU_START', 'Sistema iniciado en modo VERI*FACTU', [
                    'env' => config('verifactu.env', env('VERIFACTU_ENV', 'sandbox')),
                ]);
            } else {
                Compliance::log('art. 9.1.a', 'NO_VERIFACTU_START', 'Sistema iniciado en modo NO VERI*FACTU', [
                    'env' => app()->environment(),
                ]);
            }
        }
        return $next($request);
    }
}