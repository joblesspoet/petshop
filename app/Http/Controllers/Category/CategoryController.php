<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\SearchableTrait;
use App\Http\Controllers\Traits\SortableTrait;
use App\Http\Requests\Category\CreateCategory;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    use SearchableTrait;
    use SortableTrait;


    /**
     * Display a listing of the Category.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\CategoryResource
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var \App\Models\Category $query */
        ($query = Category::query());

        $this->applySearching($request, $query);
        $this->applySorting($request, $query);

        // enabl this if required to get the user with relationship
        // $query->with(
        //     [
        //         'user',
        //     ]
        // );

        /** @var \App\Http\Resources\CategoryResource */
        return CategoryResource::collection(
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
     * @return JsonResponse
     */
    public function store(CreateCategory $request, User $user): JsonResponse
    {
        //
        $attributes = $request->validated();
        return DB::transaction(function () use ($attributes, $user) {

            Category::create($attributes);

            return new JsonResponse([
                'message' => __('Category successfully')
            ], JsonResponse::HTTP_CREATED);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $uuid
     * @return CategoryResource
     */
    public function show(Category $uuid): CategoryResource
    {
        //
        return CategoryResource::make($uuid);
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
