<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Rental
 *
 * @property int $id
 * @property int $customer_id
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property float $total_price
 * @property string $status
 * @property Carbon|null $paid_at
 * @property string|null $notes
 * @property int|null $rental_months
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Customer $customer
 * @property-read Collection|Field[] $fields
 * @property-read Collection|Inquiry[] $inquiries
 * @property-read RentalContent|null $content
 */
class Rental extends Model
{
    const string customer_id = 'customer_id';
    const string start_date = 'start_date';
    const string end_date = 'end_date';
    const string rental_months = 'rental_months';
    const string total_price = 'total_price';
    const string status = 'status';
    const string paid_at = 'paid_at';
    const string notes = 'notes';

    const string STATUS_PENDING = 'pending';
    const string STATUS_PAID = 'paid';
    const string STATUS_ACTIVE = 'active';
    const string STATUS_COMPLETED = 'completed';
    const string STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        self::customer_id,
        self::start_date,
        self::end_date,
        self::rental_months,
        self::total_price,
        self::status,
        self::paid_at,
        self::notes,
    ];

    protected $casts = [
        self::start_date => 'date',
        self::end_date => 'date',
        self::rental_months => 'integer',
        self::total_price => 'decimal:2',
        self::paid_at => 'datetime',
    ];

    /**
     * Get the customer that owns the rental.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the fields associated with this rental.
     */
    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'field_rental');
    }

    /**
     * Get the inquiries that were converted to this rental.
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    /**
     * Get the content for the rental.
     */
    public function content(): HasOne
    {
        return $this->hasOne(RentalContent::class);
    }

    /**
     * Check if the rental is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->start_date <= now()
            && $this->end_date >= now();
    }

    /**
     * Check if the rental is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID || $this->paid_at !== null;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Ausstehend',
            self::STATUS_PAID => 'Bezahlt',
            self::STATUS_ACTIVE => 'Aktiv',
            self::STATUS_COMPLETED => 'Abgeschlossen',
            self::STATUS_CANCELLED => 'Storniert',
            default => 'Unbekannt',
        };
    }
}
