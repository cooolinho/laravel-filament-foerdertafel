<?php

namespace App\Models;

use App\Jobs\SendEmailJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Email
 *
 * @property int $id
 * @property string $direction
 * @property string $status
 * @property string $from_email
 * @property string|null $from_name
 * @property string $to_email
 * @property string|null $to_name
 * @property string|null $cc
 * @property string|null $bcc
 * @property string|null $reply_to
 * @property string|null $subject
 * @property string|null $body_text
 * @property string|null $body_html
 * @property array|null $attachments
 * @property array|null $headers
 * @property string|null $message_id
 * @property string|null $in_reply_to
 * @property string|null $references
 * @property int|null $email_template_id
 * @property int|null $customer_id
 * @property int|null $rental_id
 * @property int|null $user_id
 * @property Carbon|null $sent_at
 * @property Carbon|null $received_at
 * @property Carbon|null $read_at
 * @property string|null $error_message
 * @property array|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Email inbound()
 * @method static Builder|Email outbound()
 * @method static Builder|Email draft()
 * @method static Builder|Email sent()
 * @method static Builder|Email received()
 * @method static Builder|Email unread()
 * @method static Builder|Email read()
 */
class Email extends Model
{
    use SoftDeletes;

    // Konstanten für Felder
    const string direction = 'direction';
    const string status = 'status';
    const string from_email = 'from_email';
    const string from_name = 'from_name';
    const string to_email = 'to_email';
    const string to_name = 'to_name';
    const string cc = 'cc';
    const string bcc = 'bcc';
    const string reply_to = 'reply_to';
    const string subject = 'subject';
    const string body_text = 'body_text';
    const string body_html = 'body_html';
    const string attachments = 'attachments';
    const string headers = 'headers';
    const string message_id = 'message_id';
    const string in_reply_to = 'in_reply_to';
    const string references = 'references';
    const string email_template_id = 'email_template_id';
    const string customer_id = 'customer_id';
    const string rental_id = 'rental_id';
    const string user_id = 'user_id';
    const string sent_at = 'sent_at';
    const string received_at = 'received_at';
    const string read_at = 'read_at';
    const string error_message = 'error_message';
    const string metadata = 'metadata';

    // Konstanten für Direction
    const string DIRECTION_INBOUND = 'inbound';
    const string DIRECTION_OUTBOUND = 'outbound';

    // Konstanten für Status
    const string STATUS_DRAFT = 'draft';
    const string STATUS_SENT = 'sent';
    const string STATUS_RECEIVED = 'received';
    const string STATUS_FAILED = 'failed';
    const string STATUS_READ = 'read';

    protected $fillable = [
        self::direction,
        self::status,
        self::from_email,
        self::from_name,
        self::to_email,
        self::to_name,
        self::cc,
        self::bcc,
        self::reply_to,
        self::subject,
        self::body_text,
        self::body_html,
        self::attachments,
        self::headers,
        self::message_id,
        self::in_reply_to,
        self::references,
        self::email_template_id,
        self::customer_id,
        self::rental_id,
        self::user_id,
        self::sent_at,
        self::received_at,
        self::read_at,
        self::error_message,
        self::metadata,
    ];

    protected $casts = [
        self::attachments => 'array',
        self::headers => 'array',
        self::metadata => 'array',
        self::sent_at => 'datetime',
        self::received_at => 'datetime',
        self::read_at => 'datetime',
    ];

    // Beziehungen
    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_email')
            ->withTimestamps();
    }

    // Scopes
    public function scopeInbound(Builder $query): Builder
    {
        return $query->where(self::direction, self::DIRECTION_INBOUND);
    }

    public function scopeOutbound(Builder $query): Builder
    {
        return $query->where(self::direction, self::DIRECTION_OUTBOUND);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where(self::status, self::STATUS_DRAFT);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where(self::status, self::STATUS_SENT);
    }

    public function scopeReceived(Builder $query): Builder
    {
        return $query->where(self::status, self::STATUS_RECEIVED);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull(self::read_at);
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull(self::read_at);
    }

    // Helper Methoden
    public function isInbound(): bool
    {
        return $this->direction === self::DIRECTION_INBOUND;
    }

    public function isOutbound(): bool
    {
        return $this->direction === self::DIRECTION_OUTBOUND;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update([self::read_at => now()]);
        }
    }

    public function markAsSent(): void
    {
        $this->update([
            self::status => self::STATUS_SENT,
            self::sent_at => now(),
        ]);
    }

    /**
     * Sendet die E-Mail über den SendEmailJob
     */
    public function send(): void
    {
        if ($this->status === self::STATUS_SENT) {
            return; // Bereits gesendet
        }

        SendEmailJob::dispatch($this);
    }

    public static function getDirectionOptions(): array
    {
        return [
            self::DIRECTION_INBOUND => 'Eingehend',
            self::DIRECTION_OUTBOUND => 'Ausgehend',
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Entwurf',
            self::STATUS_SENT => 'Gesendet',
            self::STATUS_RECEIVED => 'Empfangen',
            self::STATUS_FAILED => 'Fehlgeschlagen',
            self::STATUS_READ => 'Gelesen',
        ];
    }
}
