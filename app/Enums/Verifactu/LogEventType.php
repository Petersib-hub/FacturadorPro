<?php

namespace App\Enums\Verifactu;

enum LogEventType: string
{
    case INVOICE_CREATED = 'invoice.created';
    case INVOICE_VERIFICATION_REQUESTED = 'invoice.verification_requested';
    case INVOICE_VERIFIED = 'invoice.verified';
    case INVOICE_VERIFICATION_FAILED = 'invoice.verification_failed';
    case EXPORT_GENERATED = 'export.generated';
    case ONBOARDING_COMPLETED = 'onboarding.completed';
}
