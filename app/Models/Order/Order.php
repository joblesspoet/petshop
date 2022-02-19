<?php

namespace App\Models\Order;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\User
 *
 * @property int $id
 * @property int $user_id
 * @property int $order_status_id
 * @property int $payment_id
 * @property string $uuid
 * @property json $products
 * @property json $address
 * @property float $delivery_fee|null
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $shipped_at
 * @method static \Illuminate\Database\Eloquent\Builder|User belongsTo()
 * @method static \Illuminate\Database\Eloquent\Builder|User belongsTo()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @mixin \Eloquent
 */
class Order extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'order_status_id',
        'payment_id',
        'uuid',
        'products',
        'address',
        'delivery_fee',
        'amount',
        'shipped_at'
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'shipped_at' => 'datetime',
        'products'   => 'json',
        'address'    => 'json'
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
     * Order belongs to a user model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\User
     */

    public function user(): BelongsTo
    {
        $this->belongsTo(User::class);
    }

    /**
     * Order belongs to a user model
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|\App\Models\Payment
     */

    public function payment(): HasOne
    {
        $this->hasOne(Payment::class);
    }

    /**
     * Order belongs to a user model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\OrderStatus
     */

    public function order_status(): BelongsTo
    {
        $this->belongsTo(OrderStatus::class,);
    }
}
