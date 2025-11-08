<?php

namespace App\Http\Controllers;

use App\Support\Compliance;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ComplianceController extends Controller
{
    public function checklist()
    {
        $status = Compliance::checklistStatus();
        return view('compliance.checklist', compact('status'));
    }

    public function declaracion()
    {
        // Datos de empresa para rellenar la declaración
        $company = UserSetting::query()->where('user_id', auth()->id())->first();
        return view('compliance.declaracion', compact('company'));
    }

    public function declaracionPdf()
    {
        $company = \App\Models\UserSetting::query()->where('user_id', auth()->id())->first();
        $pdf = Pdf::loadView('compliance.declaracion', compact('company'));
        return $pdf->download('DECLARACION-RESPONSABLE.pdf');
    }
}