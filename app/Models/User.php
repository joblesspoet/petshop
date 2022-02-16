<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Modules\ApplicationAuth\Entities\ApplicationUser
 *
 * @property int $id
 * @property string $uuid
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $reset_password_token
 * @property \Illuminate\Support\Carbon|null $reset_password_token_expires
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $phone_number
 * @property boolean $is_marketing
 * @property \Spatie\MediaLibrary\MediaCollections\Models\Media|null $avatar
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|Media[] $media
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationUser query()
 * @mixin \Eloquent
 */
class User extends Authenticatable implements JWTSubject
{
    use HasRolesAndAbilities;
    use HasFactory;
    use Notifiable;


    /** @var const */
    const ROLE_ADMIN = "admin";
    const ROLE_USER = "user";

    /** @var array */
    const AVAILABLE_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_USER,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_marketing' => 'boolean',
    ];

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return ['uuid'];
    }
}
