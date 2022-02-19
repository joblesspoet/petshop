<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Modules\ApplicationAuth\Entities\ApplicationUser
 *
 * @property int $id
 * @property string $uuid
 * @property enum $payment_type
 * @property json $detail
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationUser query()
 * @mixin \Eloquent
 */
class Payment extends Model
{
    use HasFactory;

    /** @var const */
    const PAYMENT_CREDIT_CARD       = "credit_card";
    const PAYMENT_CASH_ON_DELIEVERY = "cash_on_delivery";
    const PAYMENT_BANK_TRANSFER     = "bank_transfer";

    /** @var array */
    const AVAILABLE_PAYMENTS = [
        self::PAYMENT_CREDIT_CARD,
        self::PAYMENT_CASH_ON_DELIEVERY,
        self::PAYMENT_BANK_TRANSFER
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'payment_type',
        'details',
    ];
}
