<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Webpatser\Uuid\Uuid;

class ProductObserver
{

    /**
     * @param \App\Models\Product $prouct
     * @return void
     * @throws \Exception
     */
    public function creating(Product $product): void
    {
        $product->uuid = (string) Uuid::generate(4);
    }

    /**
     * Handle the Product "created" event.
     *
     * @param  \App\Models\Product  $prouct
     * @return void
     */
    public function created(Product $prouct)
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function updated(Product $prouct)
    {
        //
    }

    /**
     * Handle the Product "deleted" event.
     *
     * @param  \App\Models\Product  $prouct
     * @return void
     */
    public function deleted(Product $prouct)
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     *
     * @param  \App\Models\Product  $prouct
     * @return void
     */
    public function restored(Product $prouct)
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     *
     * @param  \App\Models\Product  $prouct
     * @return void
     */
    public function forceDeleted(Product $prouct)
    {
        //
    }
}
