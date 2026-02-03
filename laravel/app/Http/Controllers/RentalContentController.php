<?php

namespace App\Http\Controllers;

use App\Models\RentalContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RentalContentController extends Controller
{
    /**
     * Zeige das Zugangscode-Formular.
     */
    public function showAccessForm()
    {
        return view('rental-content.access-form');
    }

    /**
     * Verarbeite den Zugangscode und zeige den Content.
     */
    public function accessWithCode(Request $request, ?string $code = null)
    {
        $accessCode = $code ?? $request->input('access_code');

        if (!$accessCode) {
            return redirect()->route('rental.content.access-form')
                ->with('error', 'Bitte geben Sie einen Zugangscode ein.');
        }

        $rentalContent = RentalContent::findByAccessCode($accessCode);

        if (!$rentalContent) {
            return redirect()->route('rental.content.access-form')
                ->with('error', 'Ungültiger Zugangscode.');
        }

        // Aktualisiere letzten Zugriff
        $rentalContent->updateLastAccessed();

        // Speichere im Session
        session(['rental_content_id' => $rentalContent->id]);

        return redirect()->route('rental.content.manage', ['code' => $accessCode]);
    }

    /**
     * Zeige die Content-Verwaltungsseite.
     */
    public function manage(string $code)
    {
        $rentalContent = RentalContent::findByAccessCode($code);

        if (!$rentalContent) {
            return redirect()->route('rental.content.access-form')
                ->with('error', 'Ungültiger Zugangscode.');
        }

        $rentalContent->load(['rental.customer', 'rental.fields']);

        return view('rental-content.manage', [
            'rentalContent' => $rentalContent,
            'rental' => $rentalContent->rental,
            'customer' => $rentalContent->rental->customer,
            'fields' => $rentalContent->rental->fields,
        ]);
    }

    /**
     * Aktualisiere den Content.
     */
    public function update(Request $request, string $code)
    {
        $rentalContent = RentalContent::findByAccessCode($code);

        if (!$rentalContent) {
            return redirect()->route('rental.content.access-form')
                ->with('error', 'Ungültiger Zugangscode.');
        }

        $rules = [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'website_url' => 'nullable|url|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'is_published' => 'boolean',
        ];

        // Logo-Upload nur für Firmen erlauben
        if ($rentalContent->canUploadLogo()) {
            $rules['company_logo'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        $validated = $request->validate($rules);

        // Logo-Upload verarbeiten
        if ($request->hasFile('company_logo') && $rentalContent->canUploadLogo()) {
            // Lösche altes Logo falls vorhanden
            if ($rentalContent->company_logo) {
                Storage::disk('public')->delete($rentalContent->company_logo);
            }

            $logoPath = $request->file('company_logo')->store('rental-logos', 'public');
            $validated['company_logo'] = $logoPath;
        }

        // Logo löschen wenn checkbox gesetzt
        if ($request->has('delete_logo') && $rentalContent->company_logo) {
            Storage::disk('public')->delete($rentalContent->company_logo);
            $validated['company_logo'] = null;
        }

        $rentalContent->update($validated);

        return redirect()->route('rental.content.manage', ['code' => $code])
            ->with('success', 'Ihre Inhalte wurden erfolgreich aktualisiert.');
    }

    /**
     * Lösche das Logo.
     */
    public function deleteLogo(string $code)
    {
        $rentalContent = RentalContent::findByAccessCode($code);

        if (!$rentalContent) {
            return redirect()->route('rental.content.access-form')
                ->with('error', 'Ungültiger Zugangscode.');
        }

        if ($rentalContent->company_logo) {
            Storage::disk('public')->delete($rentalContent->company_logo);
            $rentalContent->company_logo = null;
            $rentalContent->save();
        }

        return redirect()->route('rental.content.manage', ['code' => $code])
            ->with('success', 'Logo wurde erfolgreich gelöscht.');
    }
}
