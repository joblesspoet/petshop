<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the product
     *
     * @param User $user
     * @param App\Models\Product $uuid
     * @return bool
     */
    public function view(User $user, Product $uuid)
    {
        return $user->id === $uuid->user_id;
    }

    /**
     * Determine whether the user can create product
     *
     * @return bool
     */
    public function create()
    {
        return true;
    }

    /**
     * Determine whether the user can update the product.
     *
     * @param User $user
     * @param App\Models\Product $uuid
     * @return bool
     */
    public function update(User $user, Product $uuid)
    {
        return $user->id === $uuid->user_id;
    }

    /**
     * Determine whether the user can delete the product item
     *
     * @param User $user
     * @param App\Models\Product $uuid
     * @return bool
     */
    public function delete(User $user, Product $uuid)
    {
        return $user->id === $uuid->user_id;
    }
}
