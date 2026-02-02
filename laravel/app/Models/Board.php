<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Board
 *
 * @property int $id
 * @property int $location_id
 * @property string $name
 * @property int $rows
 * @property int $columns
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Location $location
 * @property-read Collection|Field[] $fields
 * @property-read int|null $fields_count
 * @method static Builder|Board newModelQuery()
 * @method static Builder|Board newQuery()
 * @method static Builder|Board query()
 * @method static Builder|Board whereColumns($value)
 * @method static Builder|Board whereCreatedAt($value)
 * @method static Builder|Board whereDescription($value)
 * @method static Builder|Board whereId($value)
 * @method static Builder|Board whereLocationId($value)
 * @method static Builder|Board whereName($value)
 * @method static Builder|Board whereRows($value)
 * @method static Builder|Board whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Board extends Model
{
    const string location_id = 'location_id';
    const string name = 'name';
    const string rows = 'rows';
    const string columns = 'columns';
    const string description = 'description';
    const string background_image = 'background_image';
    const string grid_offset_x = 'grid_offset_x';
    const string grid_offset_y = 'grid_offset_y';
    const string grid_gap = 'grid_gap';

    protected $fillable = [
        self::location_id,
        self::name,
        self::rows,
        self::columns,
        self::description,
        self::background_image,
        self::grid_offset_x,
        self::grid_offset_y,
        self::grid_gap,
    ];

    protected $casts = [
        self::rows => 'integer',
        self::columns => 'integer',
        self::grid_offset_x => 'integer',
        self::grid_offset_y => 'integer',
        self::grid_gap => 'integer',
    ];

    /**
     * Get the location that owns the board.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the fields for the board.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(Field::class);
    }
}
