<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Inquiry
 *
 * @property int $id
 * @property int $board_id
 * @property string $customer_name
 * @property string $customer_email
 * @property string|null $customer_phone
 * @property string $start_date
 * @property string $end_date
 * @property array|null $requested_fields
 * @property string $status
 * @property string|null $message
 * @property string|null $admin_notes
 * @property int|null $rental_id
 * @property int $rental_months
 * @property bool $is_company
 * @property string|null $company_name
 * @property string|null $street
 * @property string|null $street_nr
 * @property string|null $zip
 * @property string|null $city
 * @property array|null $attachments
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Board $board
 * @property-read Rental|null $rental
 * @property-read Collection|Field[] $fields
 * @property-read int|null $fields_count
 * @method static Builder|Inquiry newModelQuery()
 * @method static Builder|Inquiry newQuery()
 * @method static Builder|Inquiry query()
 * @method static Builder|Inquiry whereAdminNotes($value)
 * @method static Builder|Inquiry whereBoardId($value)
 * @method static Builder|Inquiry whereCreatedAt($value)
 * @method static Builder|Inquiry whereCustomerEmail($value)
 * @method static Builder|Inquiry whereCustomerName($value)
 * @method static Builder|Inquiry whereCustomerPhone($value)
 * @method static Builder|Inquiry whereEndDate($value)
 * @method static Builder|Inquiry whereId($value)
 * @method static Builder|Inquiry whereMessage($value)
 * @method static Builder|Inquiry whereRentalId($value)
 * @method static Builder|Inquiry whereRequestedFields($value)
 * @method static Builder|Inquiry whereStartDate($value)
 * @method static Builder|Inquiry whereStatus($value)
 * @method static Builder|Inquiry whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Inquiry extends Model
{
    const string board_id = 'board_id';
    const string customer_name = 'customer_name';
    const string customer_email = 'customer_email';
    const string customer_phone = 'customer_phone';
    const string is_company = 'is_company';
    const string company_name = 'company_name';
    const string street = 'street';
    const string street_nr = 'street_nr';
    const string zip = 'zip';
    const string city = 'city';
    const string attachments = 'attachments';
    const string start_date = 'start_date';
    const string end_date = 'end_date';
    const string rental_months = 'rental_months';
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
        self::is_company,
        self::company_name,
        self::street,
        self::street_nr,
        self::zip,
        self::city,
        self::attachments,
        self::start_date,
        self::end_date,
        self::rental_months,
        self::requested_fields,
        self::status,
        self::message,
        self::admin_notes,
        self::rental_id,
    ];

    protected $casts = [
        self::is_company => 'boolean',
        self::attachments => 'array',
        self::start_date => 'date',
        self::end_date => 'date',
        self::rental_months => 'integer',
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
