<?php

namespace App\Jobs;

use App\Mail\BudgetPdfMail;
use App\Models\Budget;
use App\Models\UserSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBudgetPdfMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $budgetId, public string $toEmail) {}

    public function handle(): void
    {
        $budget = Budget::with('client')->findOrFail($this->budgetId);
        $settings = UserSetting::firstWhere('user_id', $budget->user_id);

        Mail::to($this->toEmail)
            ->send(new BudgetPdfMail(
                budget: $budget,
                bankAccount: $settings?->bank_account,
                billingNotes: $settings?->billing_notes
            ));
    }
}
