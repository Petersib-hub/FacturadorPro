<?php

namespace App\Http\Controllers\Verifactu;

use App\Jobs\Verifactu\CreateOnboardingEntities;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OnboardingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'organization' => 'required|array',
            'taxpayer' => 'required|array',
            'signer' => 'required|array',
            'client' => 'required|array',
        ]);

        CreateOnboardingEntities::dispatch($data, $request->user()?->tenant ?? null);

        return response()->json(['ok' => true, 'message' => 'Onboarding en proceso']);
    }
}
