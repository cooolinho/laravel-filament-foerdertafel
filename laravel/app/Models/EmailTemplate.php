<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * App\Models\EmailTemplate
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $subject
 * @property string|null $body_text
 * @property string $body_html
 * @property string|null $from_email
 * @property string|null $from_name
 * @property string|null $reply_to
 * @property string|null $description
 * @property array|null $available_variables
 * @property string $category
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|EmailTemplate active()
 * @method static Builder|EmailTemplate byCategory(string $category)
 */
class EmailTemplate extends Model
{
    use SoftDeletes;

    // Konstanten für Felder
    const string name = 'name';
    const string slug = 'slug';
    const string subject = 'subject';
    const string body_text = 'body_text';
    const string body_html = 'body_html';
    const string from_email = 'from_email';
    const string from_name = 'from_name';
    const string reply_to = 'reply_to';
    const string description = 'description';
    const string available_variables = 'available_variables';
    const string category = 'category';
    const string is_active = 'is_active';

    // Konstanten für Kategorien
    const string CATEGORY_RENTAL = 'rental';
    const string CATEGORY_INQUIRY = 'inquiry';
    const string CATEGORY_SYSTEM = 'system';
    const string CATEGORY_MARKETING = 'marketing';

    protected $fillable = [
        self::name,
        self::slug,
        self::subject,
        self::body_text,
        self::body_html,
        self::from_email,
        self::from_name,
        self::reply_to,
        self::description,
        self::available_variables,
        self::category,
        self::is_active,
    ];

    protected $casts = [
        self::available_variables => 'array',
        self::is_active => 'boolean',
    ];

    // Automatisches Generieren des Slugs
    protected static function booted(): void
    {
        static::creating(function (EmailTemplate $template) {
            if (!$template->slug) {
                $template->slug = Str::slug($template->name);
            }
        });

        static::updating(function (EmailTemplate $template) {
            if ($template->isDirty(self::name) && !$template->isDirty(self::slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    // Beziehungen
    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    /**
     * Dokumente, die bei E-Mails dieser Vorlage angehängt werden sollen.
     */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_email_template')
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(self::is_active, true);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where(self::category, $category);
    }

    // Helper Methoden
    public function render(array $variables = []): array
    {
        $subject = $this->replaceVariables($this->subject, $variables);
        $bodyText = $this->body_text ? $this->replaceVariables($this->body_text, $variables) : null;
        $bodyHtml = $this->replaceVariables($this->body_html, $variables);

        return [
            'subject' => $subject,
            'body_text' => $bodyText,
            'body_html' => $bodyHtml,
        ];
    }

    protected function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
            $content = str_replace("{{ " . $key . " }}", $value, $content);
        }

        return $content;
    }

    public static function getCategoryOptions(): array
    {
        return [
            self::CATEGORY_RENTAL => 'Vermietung',
            self::CATEGORY_INQUIRY => 'Anfrage',
            self::CATEGORY_SYSTEM => 'System',
            self::CATEGORY_MARKETING => 'Marketing',
        ];
    }

    public static function getDefaultVariables(): array
    {
        return [
            'customer_name' => 'Name des Kunden',
            'customer_email' => 'E-Mail des Kunden',
            'rental_id' => 'Vermietungs-ID',
            'rental_start' => 'Mietbeginn',
            'rental_end' => 'Mietende',
            'board_name' => 'Tafel-Name',
            'location_name' => 'Standort-Name',
            'total_price' => 'Gesamtpreis',
            'company_name' => 'Firmenname',
            'company_email' => 'Firmen-E-Mail',
            'company_phone' => 'Firmen-Telefon',
        ];
    }
}
