<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Customer extends Model
{
    const string name = 'name';
    const string company_name = 'company_name';
    const string email = 'email';
    const string phone = 'phone';
    const string address = 'address';
    const string payment_method = 'payment_method';
    const string notes = 'notes';

    protected $fillable = [
        self::name,
        self::company_name,
        self::email,
        self::phone,
        self::address,
        self::payment_method,
        self::notes,
    ];

    /**
     * Get the rentals for the customer.
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /**
     * Get the documents for the customer.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Check if customer is a company.
     */
    public function isCompany(): bool
    {
        return !empty($this->company_name);
    }

    /**
     * Check if customer is a private person.
     */
    public function isPrivatePerson(): bool
    {
        return empty($this->company_name);
    }
}
