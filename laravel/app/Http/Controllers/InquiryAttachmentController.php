<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InquiryAttachmentController extends Controller
{
    /**
     * Liefert eine Anhang-Datei einer Anfrage als Download.
     */
    public function download(Request $request, int $inquiry, string $filename): StreamedResponse
    {
        // Nur authentifizierte Admin-Nutzer dürfen herunterladen
        abort_unless(auth()->check(), 403);

        // Pfad zur Datei im local-Storage
        $path = "inquiry/{$inquiry}/{$filename}";

        abort_unless(Storage::disk('local')->exists($path), 404, 'Datei nicht gefunden.');

        $mimeType = Storage::disk('local')->mimeType($path) ?: 'application/octet-stream';

        return response()->stream(
            function () use ($path) {
                echo Storage::disk('local')->get($path);
            },
            200,
            [
                'Content-Type'        => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control'       => 'private, max-age=3600',
            ]
        );
    }
}

