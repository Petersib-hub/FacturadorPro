<div class="card mb-3">
    <div class="card-header">
        <strong>Pago por transferencia</strong>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="bank_account" class="form-label">Cuenta bancaria (IBAN / texto)</label>
            <input type="text" class="form-control" id="bank_account" name="bank_account"
                value="{{ old('bank_account', $settings->bank_account) }}" maxlength="190">
            <div class="form-text">Ej.: Banco X · ES12 3456 7890 1234 5678 9012</div>
        </div>

        <div class="mb-3">
            <label for="billing_notes" class="form-label">Notas de pago</label>
            <textarea class="form-control" id="billing_notes" name="billing_notes" rows="3">{{ old('billing_notes', $settings->billing_notes) }}</textarea>
            <div class="form-text">Se mostrarán bajo los totales (plazos, Bizum, instrucciones, etc.).</div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input type="hidden" name="show_bank_on_invoices" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="show_bank_on_invoices"
                        name="show_bank_on_invoices" value="1"
                        {{ old('show_bank_on_invoices', $settings->show_bank_on_invoices) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_bank_on_invoices">
                        Mostrar la cuenta bancaria en <strong>Facturas</strong>
                    </label>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input type="hidden" name="show_bank_on_budgets" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="show_bank_on_budgets"
                        name="show_bank_on_budgets" value="1"
                        {{ old('show_bank_on_budgets', $settings->show_bank_on_budgets) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_bank_on_budgets">
                        Mostrar la cuenta bancaria en <strong>Presupuestos</strong>
                    </label>
                </div>
                <div class="form-text">
                    Recomendado: dejar desactivado salvo que solicites anticipos.
                </div>
            </div>
        </div>
    </div>
</div>
