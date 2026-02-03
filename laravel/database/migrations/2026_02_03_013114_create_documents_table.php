<?php

use App\Models\Document;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Dokumententyp
            $table->string(Document::type); // contract, invoice, sepa_mandate, other

            // Titel und Beschreibung
            $table->string(Document::title);
            $table->text(Document::description)->nullable();

            // Datei-Informationen
            $table->string(Document::file_path);
            $table->string(Document::file_name)->nullable(); // Wird vom Observer gesetzt
            $table->string(Document::mime_type)->nullable(); // Wird vom Observer gesetzt
            $table->unsignedBigInteger(Document::file_size)->nullable(); // Wird vom Observer gesetzt, in bytes

            // Versionierung
            $table->unsignedInteger(Document::version)->default(1);
            $table->foreignId(Document::parent_document_id)->nullable()->constrained('documents')->cascadeOnDelete();
            $table->boolean(Document::is_current_version)->default(true);

            // Polymorphe Beziehung (optional - für Zuordnung zu Customer, Location, etc.)
            $table->nullableMorphs('documentable');

            // Metadaten
            $table->json(Document::metadata)->nullable(); // z.B. Vertragsnummer, Rechnungsnummer, SEPA-Mandate-Referenz

            // Uploadinformationen
            $table->foreignId(Document::uploaded_by)->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indizes (morphs() erstellt bereits einen Index für documentable_type und documentable_id)
            $table->index(Document::type);
            $table->index(Document::is_current_version);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
