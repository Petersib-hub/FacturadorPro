<?php

namespace App\Http\Controllers\Verifactu;

use App\Models\Verifactu\ExportBatch;
use App\Jobs\Verifactu\GenerateMonthlyExport;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ExportController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'period' => 'required|date_format:Y-m',
        ]);

        $batch = ExportBatch::create([
            'tenant_type' => null,
            'tenant_id' => null,
            'period' => $data['period'],
            'status' => 'pending',
        ]);

        GenerateMonthlyExport::dispatch($batch);

        return response()->json(['ok' => true, 'batch_id' => $batch->id]);
    }
}
