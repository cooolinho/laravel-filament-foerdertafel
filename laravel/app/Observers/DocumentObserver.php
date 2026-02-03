<?php

namespace App\Observers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentObserver
{
    /**
     * Handle the Document "creating" event.
     */
    public function creating(Document $document): void
    {
        $this->setFileMetadata($document);
    }

    /**
     * Handle the Document "updating" event.
     */
    public function updating(Document $document): void
    {
        // Nur aktualisieren wenn file_path geändert wurde
        if ($document->isDirty(Document::file_path)) {
            $this->setFileMetadata($document);
        }
    }

    /**
     * Set file metadata from the uploaded file.
     */
    protected function setFileMetadata(Document $document): void
    {
        if (!$document->{Document::file_path}) {
            return;
        }

        $filePath = $document->{Document::file_path};

        // MIME-Type setzen falls noch nicht gesetzt
        if (empty($document->{Document::mime_type})) {
            $document->{Document::mime_type} = Storage::disk('public')->mimeType($filePath) ?? 'application/pdf';
        }

        // Dateigröße setzen falls noch nicht gesetzt
        if (empty($document->{Document::file_size})) {
            $document->{Document::file_size} = Storage::disk('public')->size($filePath) ?? 0;
        }

        // Dateiname setzen falls noch nicht gesetzt (aus dem Pfad extrahieren)
        if (empty($document->{Document::file_name})) {
            $document->{Document::file_name} = basename($filePath);
        }
    }
}
