<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    const string name = 'name';
    const string address = 'address';
    const string description = 'description';

    protected $fillable = [
        self::name,
        self::address,
        self::description,
    ];

    /**
     * Get the boards for the location.
     */
    public function boards(): HasMany
    {
        return $this->hasMany(Board::class);
    }
}
