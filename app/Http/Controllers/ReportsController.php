<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Budget;
use App\Models\ComplianceEventLog;

class ReportsController extends Controller
{
    public function invoicesCsv(Request $r)
    {
        $from = $r->get('from'); $to = $r->get('to');
        $rows = Invoice::query()->where('user_id',auth()->id())
            ->when($from, fn($q)=>$q->whereDate('date','>=',$from))
            ->when($to, fn($q)=>$q->whereDate('date','<=',$to))
            ->orderBy('date')->get()->toArray();
        return $this->csvDownload($rows, 'invoices.csv');
    }

    public function budgetsCsv(Request $r)
    {
        $from = $r->get('from'); $to = $r->get('to');
        $rows = Budget::query()->where('user_id',auth()->id())
            ->when($from, fn($q)=>$q->whereDate('date','>=',$from))
            ->when($to, fn($q)=>$q->whereDate('date','<=',$to))
            ->orderBy('date')->get()->toArray();
        return $this->csvDownload($rows, 'budgets.csv');
    }

    public function complianceCsv(Request $r)
    {
        $from = $r->get('from'); $to = $r->get('to');
        $rows = ComplianceEventLog::query()
            ->when($from, fn($q)=>$q->where('created_at','>=',$from.' 00:00:00'))
            ->when($to, fn($q)=>$q->where('created_at','<=',$to.' 23:59:59'))
            ->orderBy('id')->get()->toArray();
        return $this->csvDownload($rows, 'compliance.csv');
    }

    private function csvDownload(array $rows, string $name)
    {
        $out = fopen('php://temp','r+');
        if (!empty($rows)) {
            fputcsv($out, array_keys($rows[0]));
            foreach ($rows as $r) {
                foreach ($r as $k=>$v) if (is_array($v)) $r[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
                fputcsv($out, $r);
            }
        }
        rewind($out);
        $csv = stream_get_contents($out);
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$name.'"'
        ]);
    }
}