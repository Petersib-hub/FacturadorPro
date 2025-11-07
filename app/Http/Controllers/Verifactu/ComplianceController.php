<?php

namespace App\Http\Controllers\Verifactu;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Services\Verifactu\VerifactuService;
use App\Models\Verifactu\ExportBatch;
use App\Models\Invoice;

class ComplianceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::query()->latest('issue_date')->limit(50)->get();
        return view('verifactu.compliance', compact('invoices'));
    }

    public function verify(Request $request, Invoice $invoice, VerifactuService $service)
    {
        $service->verify($invoice);
        return back()->with('status', 'Verificación ejecutada');
    }

    public function export(Request $request, VerifactuService $service)
    {
        $data = $request->validate(['period' => 'required|date_format:Y-m']);
        $path = $service->exportMonthly($data['period'], $request->user());
        return back()->with('status', 'Export generado: ' . $path);
    }
}
