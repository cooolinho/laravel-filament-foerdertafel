<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rental extends Model
{
    const string customer_id = 'customer_id';
    const string start_date = 'start_date';
    const string end_date = 'end_date';
    const string total_price = 'total_price';
    const string status = 'status';
    const string notes = 'notes';

    const string STATUS_ACTIVE = 'active';
    const string STATUS_COMPLETED = 'completed';
    const string STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        self::customer_id,
        self::start_date,
        self::end_date,
        self::total_price,
        self::status,
        self::notes,
    ];

    protected $casts = [
        self::start_date => 'date',
        self::end_date => 'date',
        self::total_price => 'decimal:2',
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
     * Check if the rental is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->start_date <= now()
            && $this->end_date >= now();
    }
}
