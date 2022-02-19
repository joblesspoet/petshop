<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

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
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicatiUseronUser query()
 * @mixin \Eloquent
 */
class Order extends Model
{
    use HasFactory;
}
