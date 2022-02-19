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
        /** @var \App\Models\User $query */
        ($query = User::query());
        $query->whereIs(User::ROLE_ADMIN);

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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
