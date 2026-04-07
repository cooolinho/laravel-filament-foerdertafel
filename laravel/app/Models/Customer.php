<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Customer
 *
 * @property int $id
 * @property string $name
 * @property string|null $company_name
 * @property bool $is_company
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $street
 * @property string|null $street_nr
 * @property string|null $zip
 * @property string|null $city
 * @property string|null $payment_method
 * @property string|null $notes
 * @property string|null $account_holder
 * @property string|null $iban
 * @property string|null $bic
 * @property string|null $bank_name
 * @property bool|null $sepa_mandate_accepted
 * @property bool|null $billing_use_postal_address
 * @property string|null $billing_street
 * @property string|null $billing_address2
 * @property string|null $billing_zip
 * @property string|null $billing_city
 * @property string|null $billing_country
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection|Rental[] $rentals
 * @property-read Collection|Document[] $documents
 */
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
