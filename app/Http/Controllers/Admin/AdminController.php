<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\GetUser;
use App\Http\Controllers\Traits\SearchableTrait;
use App\Http\Controllers\Traits\SortableTrait;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Contracts\Hashing\Hasher;
use App\Http\Requests\Auth\CreateAdminRequest;
use Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class AdminController extends Controller
{
    use SearchableTrait;
    use SortableTrait;
    use GetUser;

    /**
     * Display a listing of the Admin Users.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\CategoryResource
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $this->getUser();
        /** @var \App\Models\User $query */
        ($query = User::query());
        $query->whereNotIn('id', [$user->id])
            ->whereIs(User::ROLE_ADMIN);

        $this->applySearching($request, $query);
        $this->applySorting($request, $query);


        /** @var \App\Http\Resources\UserResource */
        return UserResource::collection(
            $query->paginate(
                $request->has('per_page') ?
                    $request->input('per_page') :
                    config('app.record_per_page')
            )
        );
    }


    /**
     * create a new administrator.
     *
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param \App\Http\Requests\Auth\CreateAdminRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function store(Hasher $hasher, CreateAdminRequest $request): JsonResponse
    {

        $attributes = Arr::except($request->validated(), ['password']);
        /** @var App\Models\User $model */
        $model = User::class;
        $user = new $model();
        $user->fill(array_merge($attributes));

        $user->password = $hasher->make($request->input('password'));
        $user->email_verified_at = now();
        $user->save();

        // assign role as ADMIN...
        $user->assign(User::ROLE_ADMIN);

        if (!$user->wasRecentlyCreated) {
            $this->logoutExistingToken();
        } else {
            Event::dispatch(new Registered($user));
        }

        /** \Illuminate\Http\JsonResponse */
        return new JsonResponse(
            [
                'message' => "Admin created successfully.",
                'data' => UserResource::make($user)
            ],
            JsonResponse::HTTP_OK
        );
    }
}
