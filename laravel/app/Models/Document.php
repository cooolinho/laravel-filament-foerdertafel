<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string|null $description
 * @property string $file_path
 * @property string $file_name
 * @property string $mime_type
 * @property int $file_size
 * @property int $version
 * @property int|null $parent_document_id
 * @property bool $is_current_version
 * @property string $documentable_type
 * @property int $documentable_id
 * @property array|null $metadata
 * @property int|null $uploaded_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read Model&MorphTo $documentable
 * @property-read User|null $uploadedBy
 * @property-read Document|null $parentDocument
 * @property-read Collection&HasMany $versions
 */
class Document extends Model
{
    use SoftDeletes;

    // Dokumententypen
    const string TYPE_CONTRACT = 'contract';
    const string TYPE_INVOICE = 'invoice';
    const string TYPE_SEPA_MANDATE = 'sepa_mandate';
    const string TYPE_REVOCATION_POLICY = 'revocation_policy';
    const string TYPE_INFO_BROCHURE = 'info_brochure';
    const string TYPE_TERMS_CONDITIONS = 'terms_conditions';
    const string TYPE_OTHER = 'other';

    // Feldnamen
    const string type = 'type';
    const string title = 'title';
    const string description = 'description';
    const string file_path = 'file_path';
    const string file_name = 'file_name';
    const string mime_type = 'mime_type';
    const string file_size = 'file_size';
    const string version = 'version';
    const string parent_document_id = 'parent_document_id';
    const string is_current_version = 'is_current_version';
    const string documentable_type = 'documentable_type';
    const string documentable_id = 'documentable_id';
    const string metadata = 'metadata';
    const string uploaded_by = 'uploaded_by';

    protected $fillable = [
        self::type,
        self::title,
        self::description,
        self::file_path,
        self::file_name,
        self::mime_type,
        self::file_size,
        self::version,
        self::parent_document_id,
        self::is_current_version,
        self::documentable_type,
        self::documentable_id,
        self::metadata,
        self::uploaded_by,
    ];

    protected $casts = [
        self::version => 'integer',
        self::file_size => 'integer',
        self::is_current_version => 'boolean',
        self::metadata => 'array',
    ];

    /**
     * Get the parent entity (Customer, Location, Board, Field, Rental).
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who uploaded this document.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, self::uploaded_by);
    }

    /**
     * Get the parent document (for versioning).
     */
    public function parentDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, self::parent_document_id);
    }

    /**
     * Get all versions of this document.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(Document::class, self::parent_document_id);
    }

    /**
     * Get the emails this document is attached to.
     */
    public function emails(): BelongsToMany
    {
        return $this->belongsToMany(Email::class, 'document_email')
            ->withTimestamps();
    }

    /**
     * Get all document types as array.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_CONTRACT => 'Mietvertrag',
            self::TYPE_INVOICE => 'Rechnung',
            self::TYPE_SEPA_MANDATE => 'SEPA-Mandat',
            self::TYPE_REVOCATION_POLICY => 'Widerrufsbelehrung',
            self::TYPE_INFO_BROCHURE => 'Info-Broschüre',
            self::TYPE_TERMS_CONDITIONS => 'AGB',
            self::TYPE_OTHER => 'Sonstiges',
        ];
    }

    /**
     * Get the type label.
     */
    public function getTypeLabel(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    /**
     * Get file size in human readable format.
     */
    public function getFileSizeHuman(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the full storage path.
     */
    public function getFullPath(): string
    {
        return Storage::path($this->file_path);
    }

    /**
     * Get the download URL.
     */
    public function getDownloadUrl(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Check if document is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Check if document is assigned to an entity.
     */
    public function isAssigned(): bool
    {
        return !empty($this->documentable_type) && !empty($this->documentable_id);
    }

    /**
     * Check if document is a general/standalone document.
     */
    public function isGeneralDocument(): bool
    {
        return !$this->isAssigned();
    }

    /**
     * Scope to get only general documents (not assigned to any entity).
     */
    public function scopeGeneral($query)
    {
        return $query->whereNull(self::documentable_type)
                    ->whereNull(self::documentable_id);
    }

    /**
     * Scope to get only assigned documents.
     */
    public function scopeAssigned($query)
    {
        return $query->whereNotNull(self::documentable_type)
                    ->whereNotNull(self::documentable_id);
    }

    /**
     * Create a new version of this document.
     */
    public function createNewVersion(array $data): self
    {
        // Mark current document as not current
        $this->update([self::is_current_version => false]);

        // Create new version
        // Note: mime_type, file_size, and file_name will be set automatically by the DocumentObserver
        $newVersion = self::create([
            self::type => $this->type,
            self::title => $data[self::title] ?? $this->title,
            self::description => $data[self::description] ?? $this->description,
            self::file_path => $data[self::file_path],
            self::version => $this->version + 1,
            self::parent_document_id => $this->parent_document_id ?? $this->id,
            self::is_current_version => true,
            self::documentable_type => $this->documentable_type,
            self::documentable_id => $this->documentable_id,
            self::metadata => $data[self::metadata] ?? $this->metadata,
            self::uploaded_by => $data[self::uploaded_by] ?? auth()->id(),
        ]);

        return $newVersion;
    }

    /**
     * Get the latest version of this document.
     */
    public function getLatestVersion(): self
    {
        if ($this->is_current_version) {
            return $this;
        }

        $parentId = $this->parent_document_id ?? $this->id;

        return self::where(self::parent_document_id, $parentId)
            ->orWhere('id', $parentId)
            ->where(self::is_current_version, true)
            ->first() ?? $this;
    }

    /**
     * Get all versions including the root document.
     */
    public function getAllVersions()
    {
        $parentId = $this->parent_document_id ?? $this->id;

        return self::where(self::parent_document_id, $parentId)
            ->orWhere('id', $parentId)
            ->orderBy(self::version, 'desc')
            ->get();
    }
}
