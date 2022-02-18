<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\SearchableTrait;
use App\Http\Controllers\Traits\GetUser;
use App\Http\Controllers\Traits\SortableTrait;
use App\Http\Requests\Category\CreateCategory;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\DB;


class CategoryController extends Controller
{
    use SearchableTrait;
    use SortableTrait;
    use GetUser;

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
    public function store(CreateCategory $request): JsonResponse
    {
        //
        $attributes = $request->validated();
        $objUser = $this->getUser();
        return DB::transaction(function () use ($attributes, $objUser) {
            $objUser->categories()->create($attributes);

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
        $this->authorize('show', $uuid);
        //
        return CategoryResource::make($uuid);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCategoryRequest  $request
     * @param  Category  $uuid
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategoryRequest $request, $uuid)
    {
        //
        $this->authorize('update', $uuid);
        $uuid->update($request->only('title'));
        return CategoryResource::make($uuid);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Category $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $uuid)
    {
        $this->authorize('destroy', $uuid);
        //

        return DB::transaction(function () use ($uuid) {
            $uuid->delete();
            return new JsonResponse([
                'message' => __('Category deleted successfully')
            ], JsonResponse::HTTP_NO_CONTENT);
        });
    }
}
