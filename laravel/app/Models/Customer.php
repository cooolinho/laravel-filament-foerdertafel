<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Customer extends Model
{
    const string name = 'name';
    const string company_name = 'company_name';
    const string is_company = 'is_company';
    const string email = 'email';
    const string phone = 'phone';
    const string street = 'street';
    const string street_nr = 'street_nr';
    const string zip = 'zip';
    const string city = 'city';
    const string payment_method = 'payment_method';
    const string notes = 'notes';

    // SEPA-Bankdaten
    const string account_holder = 'account_holder';
    const string iban = 'iban';
    const string bic = 'bic';
    const string bank_name = 'bank_name';
    const string sepa_mandate_accepted = 'sepa_mandate_accepted';

    // Rechnungsanschrift
    const string billing_use_postal_address = 'billing_use_postal_address';
    const string billing_street = 'billing_street';
    const string billing_address2 = 'billing_address2';
    const string billing_zip = 'billing_zip';
    const string billing_city = 'billing_city';
    const string billing_country = 'billing_country';

    protected $fillable = [
        self::name,
        self::company_name,
        self::is_company,
        self::email,
        self::phone,
        self::street,
        self::street_nr,
        self::zip,
        self::city,
        self::payment_method,
        self::notes,
        self::account_holder,
        self::iban,
        self::bic,
        self::bank_name,
        self::sepa_mandate_accepted,
        self::billing_use_postal_address,
        self::billing_street,
        self::billing_address2,
        self::billing_zip,
        self::billing_city,
        self::billing_country,
    ];

    protected $casts = [
        self::is_company => 'boolean',
        self::sepa_mandate_accepted => 'boolean',
        self::billing_use_postal_address => 'boolean',
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
        return (bool) $this->is_company;
    }

    /**
     * Check if customer is a private person.
     */
    public function isPrivatePerson(): bool
    {
        return !$this->is_company;
    }
}
