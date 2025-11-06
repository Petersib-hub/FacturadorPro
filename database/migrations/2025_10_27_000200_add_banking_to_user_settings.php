<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('user_settings')) {
            Schema::table('user_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('user_settings', 'bank_account')) {
                    // IBAN, CCC o texto breve identificativo. Puedes poner "Banco X · ES12 3456 7890 1234 5678 9012"
                    $table->string('bank_account', 190)->nullable()->after('pdf_template');
                }
                if (!Schema::hasColumn('user_settings', 'billing_notes')) {
                    // Notas de pago visibles en PDF (por ejemplo: "Pago por transferencia en 7 días", Bizum, etc.).
                    $table->text('billing_notes')->nullable()->after('bank_account');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_settings')) {
            Schema::table('user_settings', function (Blueprint $table) {
                if (Schema::hasColumn('user_settings', 'bank_account')) {
                    $table->dropColumn('bank_account');
                }
                if (Schema::hasColumn('user_settings', 'billing_notes')) {
                    $table->dropColumn('billing_notes');
                }
            });
        }
    }
};
