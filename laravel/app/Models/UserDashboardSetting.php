<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property array|null $visible_widgets
 * @property array|null $widget_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read User $user
 */
class UserDashboardSetting extends Model
{
    // properties
    const string id = 'id';
    const string user_id = 'user_id';
    const string visible_widgets = 'visible_widgets';
    const string widget_order = 'widget_order';

    // timestamps
    const string created_at = 'created_at';
    const string updated_at = 'updated_at';

    protected $fillable = [
        self::user_id,
        self::visible_widgets,
        self::widget_order,
    ];

    protected function casts(): array
    {
        return [
            self::visible_widgets => 'array',
            self::widget_order => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
