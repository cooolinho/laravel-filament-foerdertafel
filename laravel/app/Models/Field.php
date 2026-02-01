<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Field extends Model
{
    const string board_id = 'board_id';
    const string name = 'name';
    const string row = 'row';
    const string column = 'column';
    const string width = 'width';
    const string height = 'height';
    const string price_per_month = 'price_per_month';
    const string status = 'status';
    const string description = 'description';

    const string STATUS_AVAILABLE = 'available';
    const string STATUS_RENTED = 'rented';
    const string STATUS_RESERVED = 'reserved';

    protected $fillable = [
        self::board_id,
        self::name,
        self::row,
        self::column,
        self::width,
        self::height,
        self::price_per_month,
        self::status,
        self::description,
    ];

    protected $casts = [
        self::row => 'integer',
        self::column => 'integer',
        self::width => 'integer',
        self::height => 'integer',
        self::price_per_month => 'decimal:2',
    ];

    /**
     * Get the board that owns the field.
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get the rentals that include this field.
     */
    public function rentals(): BelongsToMany
    {
        return $this->belongsToMany(Rental::class, 'field_rental');
    }

    /**
     * Check if the field is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    /**
     * Check if the field is rented.
     */
    public function isRented(): bool
    {
        return $this->status === self::STATUS_RENTED;
    }
}
