<?php

namespace App\Http\Controllers\Verifactu;

use App\Jobs\Verifactu\VerifyInvoice;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Invoice;

class InvoiceVerificationController extends Controller
{
    public function update(Request $request, Invoice $invoice)
    {
        VerifyInvoice::dispatch($invoice, $request->user()?->tenant ?? null);
        return response()->json(['ok' => true, 'message' => 'Verificación encolada']);
    }
}
