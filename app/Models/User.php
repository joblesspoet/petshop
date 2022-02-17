<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidBase64Data;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

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
class User extends Authenticatable implements JWTSubject, HasMedia
{
    use HasRolesAndAbilities;
    use HasFactory;
    use Notifiable;
    use InteractsWithMedia;


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
        'first_name',
        'last_name',
        'email',
        'password',
        'email_verified_at',
        'address',
        'avatar',
        'phone_number'
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

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();
    }

    /**
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media|null $media
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 150, 150)
            ->keepOriginalImageFormat()
            ->quality(80)
            ->orientation(Manipulations::ORIENTATION_AUTO)
            ->optimize();
        // ->withResponsiveImages();
    }

    /**
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Media|null
     */
    public function getAvatarAttribute(): ?Media
    {
        return $this->getFirstMedia('avatar');
    }


    /**
     * @param \SplFileInfo|\Spatie\MediaLibrary\MediaCollections\Models\Media|string|null $value
     * @return $this
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Exception
     */

    public function setAvatarAttribute($value): self
    {
        if ($value === null) {
            optional($this->getFirstMedia('avatar'))->delete();

            return $this;
        }

        if ($value instanceof Media) {
            $value->copy($this, 'avatar');

            return $this;
        }

        if (is_string($value)) {
            if (Str::startsWith($value, ['http://', 'https://'])) {
                $this->addMediaFromUrl($value, ['image/jpeg', 'image/png', 'image/webp'])
                    ->toMediaCollection('avatar');

                return $this;
            }


            if (Str::startsWith($value, 'data:')) {

                if (!Str::contains($value, ';base64')) {
                    if (!preg_match('#^data:([^,]+),(.*)$#', $value, $matches)) {
                        throw InvalidBase64Data::create();
                    }

                    $value = 'data:' . $matches[1] . ';base64,' . base64_encode($matches[2]);
                }

                if (!preg_match('#^data:([^;]+);base64,[-A-Za-z0-9+/]+={0,2}$#', $value, $matches)) {
                    throw InvalidBase64Data::create();
                }

                $mime = $matches[1];
                $extension = Arr::first(
                    MimeTypes::getDefault()
                        ->getExtensions($mime)
                ) ?: 'bin';

                $this->addMediaFromBase64($value, ['image/jpeg', 'image/png', 'image/webp'])
                    ->usingFileName(sha1($value) . '.' . $extension)
                    ->toMediaCollection('avatar');

                return $this;
            }

            throw new \InvalidArgumentException("Expected a url, data uri or file");
        }

        if ($value instanceof \SplFileInfo && !($value instanceof File || $value instanceof UploadedFile)) {
            $value = new File($value->getRealPath());
        }

        if ($value instanceof File || $value instanceof UploadedFile) {
            $adder = $this->addMedia($value);

            if (!($value instanceof UploadedFile)) {
                $adder->preservingOriginal();
            }

            $adder->toMediaCollection('avatar');

            return $this;
        }

        throw new \InvalidArgumentException("Expected another media record, a url, a data uri or a file");
    }
}
