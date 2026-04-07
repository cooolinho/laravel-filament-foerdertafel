<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Setting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentViewController extends Controller
{
    /**
     * Prüft, ob ein Dokument zugänglich ist.
     * Angemeldete Benutzer dürfen jedes Dokument sehen.
     * Gäste dürfen nur: is_public = true ODER das AGB-Dokument aus den Einstellungen.
     */
    private function isAccessible(Document $document): bool
    {
        if (auth()->check()) {
            return true;
        }

        if ($document->is_public) {
            return true;
        }

        $termsId = (int) Setting::get(Setting::terms_conditions_document_id);

        return $termsId > 0 && $termsId === $document->id;
    }

    /**
     * Zeigt die HTML-Dokumentenseite mit eingebettetem PDF-Viewer.
     */
    public function show(Document $document): View
    {
        if (!$this->isAccessible($document)) {
            abort(403, 'Dieses Dokument ist nicht öffentlich zugänglich.');
        }

        return view('documents.show', compact('document'));
    }

    /**
     * Liefert die Datei (PDF o.Ä.) als Inline-Stream aus.
     */
    public function file(Document $document): Response|StreamedResponse
    {
        if (!$this->isAccessible($document)) {
            abort(403, 'Dieses Dokument ist nicht öffentlich zugänglich.');
        }

        if (!Storage::disk(Document::STORAGE)->exists($document->file_path)) {
            abort(404, 'Die angeforderte Datei wurde nicht gefunden.');
        }

        $mimeType = $document->mime_type ?? 'application/pdf';
        $fileName = $document->file_name ?? basename($document->file_path);

        return response()->stream(
            function () use ($document) {
                echo Storage::disk(Document::STORAGE)->get($document->file_path);
            },
            200,
            [
                'Content-Type'        => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control'       => 'private, max-age=3600',
            ]
        );
    }
}
