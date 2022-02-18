<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the category
     *
     * @param User $user
     * @param Category $uuid
     * @return bool
     */
    public function view(User $user, Category $uuid)
    {
        return $user->id === $uuid->user_id;
    }

    /**
     * Determine whether the user can create cateogry
     *
     * @return bool
     */
    public function create()
    {
        return true;
    }

    /**
     * Determine whether the user can update the category.
     *
     * @param User $user
     * @param Category $uuid
     * @return bool
     */
    public function update(User $user, Category $uuid)
    {
        return $user->id === $uuid->user_id;
    }

    /**
     * Determine whether the user can delete the category item
     *
     * @param User $user
     * @param Category $uuid
     * @return bool
     */
    public function delete(User $user, Category $uuid)
    {
        return $user->id === $uuid->user_id;
    }
}
