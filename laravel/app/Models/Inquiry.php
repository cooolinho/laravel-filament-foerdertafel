<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Inquiry extends Model
{
    const string board_id = 'board_id';
    const string customer_name = 'customer_name';
    const string customer_email = 'customer_email';
    const string customer_phone = 'customer_phone';
    const string start_date = 'start_date';
    const string end_date = 'end_date';
    const string requested_fields = 'requested_fields';
    const string status = 'status';
    const string message = 'message';
    const string admin_notes = 'admin_notes';
    const string rental_id = 'rental_id';

    const string STATUS_PENDING = 'pending';
    const string STATUS_APPROVED = 'approved';
    const string STATUS_REJECTED = 'rejected';
    const string STATUS_CONVERTED = 'converted';

    protected $fillable = [
        self::board_id,
        self::customer_name,
        self::customer_email,
        self::customer_phone,
        self::start_date,
        self::end_date,
        self::requested_fields,
        self::status,
        self::message,
        self::admin_notes,
        self::rental_id,
    ];

    protected $casts = [
        self::start_date => 'date',
        self::end_date => 'date',
        self::requested_fields => 'array',
    ];

    /**
     * Get the board that this inquiry is for.
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get the rental that was created from this inquiry.
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /**
     * Get the requested fields (many-to-many through field IDs in JSON).
     */
    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'inquiry_field')
            ->withTimestamps();
    }

    /**
     * Check if the inquiry is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the inquiry is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the inquiry has been converted to a rental.
     */
    public function isConverted(): bool
    {
        return $this->status === self::STATUS_CONVERTED;
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Ausstehend',
            self::STATUS_APPROVED => 'Genehmigt',
            self::STATUS_REJECTED => 'Abgelehnt',
            self::STATUS_CONVERTED => 'Vermietung erstellt',
            default => $this->status,
        };
    }
}
