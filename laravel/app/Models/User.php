<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read UserDashboardSetting|null $dashboardSettings
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // properties
    const string id = 'id';
    const string name = 'name';
    const string email = 'email';
    const string email_verified_at = 'email_verified_at';
    const string password = 'password';
    const string remember_token = 'remember_token';

    // timestamps;
    const string created_at = 'created_at';
    const string updated_at = 'updated_at';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        self::name,
        self::email,
        self::password,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        self::password,
        self::remember_token,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            self::email_verified_at => 'datetime',
            self::password => 'hashed',
        ];
    }

    /**
     * Get the user's dashboard settings.
     */
    public function dashboardSettings(): HasOne
    {
        return $this->hasOne(UserDashboardSetting::class);
    }
}
