<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cviebrock\EloquentSluggable\Sluggable;

use function PHPSTORM_META\map;

/**
 * Modules\ApplicationAuth\Entities\ApplicationUser
 *
 * @property int $id
 * @property string $uuid
 * @property int $category_id
 * @property string $title
 * @property string $description
 * @property float $price
 * @property json $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationUser belongsTo()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationUser query()
 * @mixin \Eloquent
 */

class Product extends Model
{
    use HasFactory;
    use Sluggable;

    /** @var const */
    const STATUS_ACTIVE = "active";
    const STATUS_INACTIVE = "inactive";

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
        'metadata'
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

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }
}
