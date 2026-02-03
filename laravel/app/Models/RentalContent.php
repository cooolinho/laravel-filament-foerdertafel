<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * App\Models\RentalContent
 *
 * @property int $id
 * @property int $rental_id
 * @property string $access_code
 * @property string|null $company_logo
 * @property string|null $title
 * @property string|null $description
 * @property string|null $website_url
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property bool $is_private_person
 * @property bool $is_published
 * @property Carbon|null $last_accessed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Rental $rental
 */
class RentalContent extends Model
{
    const string rental_id = 'rental_id';
    const string access_code = 'access_code';
    const string company_logo = 'company_logo';
    const string title = 'title';
    const string description = 'description';
    const string website_url = 'website_url';
    const string contact_email = 'contact_email';
    const string contact_phone = 'contact_phone';
    const string is_private_person = 'is_private_person';
    const string is_published = 'is_published';
    const string last_accessed_at = 'last_accessed_at';

    protected $fillable = [
        self::rental_id,
        self::access_code,
        self::company_logo,
        self::title,
        self::description,
        self::website_url,
        self::contact_email,
        self::contact_phone,
        self::is_private_person,
        self::is_published,
        self::last_accessed_at,
    ];

    protected $casts = [
        self::is_private_person => 'boolean',
        self::is_published => 'boolean',
        self::last_accessed_at => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatisch einen einmaligen Zugangscode generieren
        static::creating(function ($rentalContent) {
            if (empty($rentalContent->access_code)) {
                $rentalContent->access_code = self::generateUniqueAccessCode();
            }
        });
    }

    /**
     * Generiere einen einmaligen Zugangscode.
     */
    public static function generateUniqueAccessCode(): string
    {
        do {
            // Generiere einen 12-stelligen alphanumerischen Code
            $code = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (self::where(self::access_code, $code)->exists());

        return $code;
    }

    /**
     * Finde RentalContent anhand des Zugangscodes.
     */
    public static function findByAccessCode(string $accessCode): ?self
    {
        return self::where(self::access_code, $accessCode)->first();
    }

    /**
     * Get the rental that owns the content.
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /**
     * Aktualisiere den letzten Zugriffszeitpunkt.
     */
    public function updateLastAccessed(): void
    {
        $this->last_accessed_at = now();
        $this->save();
    }

    /**
     * Prüfe ob ein Logo hochgeladen werden kann.
     */
    public function canUploadLogo(): bool
    {
        return !$this->is_private_person;
    }

    /**
     * Prüfe ob der Content veröffentlicht ist.
     */
    public function isPublished(): bool
    {
        return $this->is_published;
    }

    /**
     * Prüfe ob der Content kürzlich zugegriffen wurde (innerhalb der letzten 7 Tage).
     */
    public function isRecentlyAccessed(): bool
    {
        return $this->last_accessed_at && $this->last_accessed_at->isAfter(now()->subDays(7));
    }
}
