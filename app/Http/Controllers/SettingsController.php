<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        $settings = UserSetting::firstOrCreate(['user_id' => $request->user()->id]);
        $taxRates = TaxRate::forAuthUser()->orderByDesc('is_default')->orderBy('rate')->get();

        $locales    = ['es_ES', 'en_US', 'ca_ES', 'eu_ES', 'gl_ES'];
        $timezones  = ['Europe/Madrid', 'UTC', 'America/Mexico_City', 'America/Bogota', 'America/Argentina/Buenos_Aires'];
        $currencies = ['EUR', 'USD', 'MXN', 'COP', 'ARS'];

        return view('settings.edit', compact('settings', 'taxRates', 'locales', 'timezones', 'currencies'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'legal_name'    => ['nullable','string','max:190'],
            'phone'         => ['nullable','string','max:30'],
            'tax_id'        => ['nullable','string','max:40'],
            'address'       => ['nullable','string','max:190'],
            'zip'           => ['nullable','string','max:20'],
            'city'          => ['nullable','string','max:120'],
            'country'       => ['required','string','max:60'], // aceptamos “ES” o nombre país
            'currency_code' => ['required','string','size:3'],
            'locale'        => ['required','string','max:10'],
            'timezone'      => ['required','string','max:64'],
            'pdf_template'  => ['required', Rule::in(['classic','modern','minimal'])],

            // Logo
            'logo'        => ['nullable','image','max:2048'],
            'remove_logo' => ['nullable','boolean'],

            // Recordatorios
            'reminders_enabled'           => ['sometimes','boolean'],
            'reminder_days_before_first'  => ['nullable','integer','min:0','max:60'],
            'reminder_days_after_due'     => ['nullable','integer','min:0','max:60'],
            'reminder_repeat_every_days'  => ['nullable','integer','min:1','max:60'],
            'reminder_max_times'          => ['nullable','integer','min:1','max:12'],

            // Datos bancarios / notas
            'bank_name'    => ['nullable','string','max:120'],
            'bank_holder'  => ['nullable','string','max:120'],
            'bank_account' => ['nullable','string','max:190'],
            'billing_notes'=> ['nullable','string'],
            'show_bank_on_invoices' => ['sometimes','boolean'],
            'show_bank_on_budgets'  => ['sometimes','boolean'],
        ]);

        $settings = UserSetting::firstOrCreate(['user_id' => auth()->id()]);

        // Eliminar logo
        if ($request->boolean('remove_logo') && $settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $data['logo_path'] = null;
        }

        // Subir logo
        if ($request->hasFile('logo')) {
            if (!empty($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo_path'] = $path;
        }

        // Normalizar booleanos
        $data['reminders_enabled']     = $request->boolean('reminders_enabled');
        $data['show_bank_on_invoices'] = $request->boolean('show_bank_on_invoices');
        $data['show_bank_on_budgets']  = $request->boolean('show_bank_on_budgets');

        // Conservar defaults si vienen nulos
        $data['reminder_days_before_first'] = $data['reminder_days_before_first'] ?? ($settings->reminder_days_before_first ?? 7);
        $data['reminder_days_after_due']    = $data['reminder_days_after_due']    ?? ($settings->reminder_days_after_due    ?? 1);
        $data['reminder_repeat_every_days'] = $data['reminder_repeat_every_days'] ?? ($settings->reminder_repeat_every_days ?? 7);
        $data['reminder_max_times']         = $data['reminder_max_times']         ?? ($settings->reminder_max_times         ?? 3);

        $settings->update($data);

        return back()->with('ok', 'Ajustes guardados');
    }
}
