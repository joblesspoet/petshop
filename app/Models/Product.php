<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cviebrock\EloquentSluggable\Sluggable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Http\Controllers\Traits\MediaManagerTrait;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Spatie\Image\Manipulations;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $uuid
 * @property int $category_id
 * @property string $title
 * @property string $description
 * @property float $price
 * @property json $metadata
 * @property string|null $images
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read MediaCollection|Media[] $media
 * @method static \Illuminate\Database\Eloquent\Builder|User belongsTo()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Post query()
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereLocationRadius($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereUserId($value)
 * @mixin \Eloquent
 */

class Product extends Model implements HasMedia
{
    use HasFactory;
    use MediaManagerTrait;
    use InteractsWithMedia;

    /** @var const */
    const STATUS_ACTIVE = "active";
    const STATUS_INACTIVE = "inactive";
    const COLLECTION_IMAGE = 'product';

    /** @var array */
    const AVAILABLE_STATUS = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'uuid',
        'title',
        'price',
        'description',
        'metadata',
        'images'
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'metadata'   => 'json'
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * categories function
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\User
     */
    public function category(): BelongsTo
    {
        return $this
            ->belongsTo(
                Category::class
            );
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    /**
     * @return string|null
     */
    public function getImagesAttribute()
    {
        return $this->getMedia(self::COLLECTION_IMAGE);
    }

    public function setDeleteMediaAttribute($value)
    {
        return $this->modelMediaToDelete($value, $this->images);
    }

    /**
     * @return string|null
     */
    public function getImagesThumbAttribute()
    {
        return $this->getFirstTemporaryUrl(Carbon::now()->addHour(), self::COLLECTION_IMAGE, 'thumb');
    }

    /**
     *@return Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|null
     *
     */
    public function getImagesMediaAttribute(): MediaCollection|null
    {
        return $this->getMedia();
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    /**
     * @param \SplFileInfo|\Spatie\MediaLibrary\MediaCollections\Models\Media|string|null $value
     * @return $this
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded
     * @throws \Exception
     */
    public function setImagesAttribute($value)
    {
        return $this->processMedia($value, self::COLLECTION_IMAGE, $this->images);
    }

    /**
     * registerMediaCollections function
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $collections = [
            [
                'collection' => self::COLLECTION_IMAGE,
                'limit' => 5
            ]
        ];
        $this->handleRegisterMediaCollections($collections);
    }

    /**
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media|null $media
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->handleRegisterMediaConversions($media);
    }
}
