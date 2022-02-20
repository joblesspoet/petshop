<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Http\Controllers\Traits\SearchableTrait;
use App\Http\Controllers\Traits\GetUser;
use App\Http\Controllers\Traits\SortableTrait;
use App\Http\Resources\ProductResource;
use App\Http\Requests\Product\StoreProductRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProductController extends Controller
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
        /** @var \App\Models\Product $query */
        ($query = Product::query());

        $this->applySearching($request, $query);
        $this->applySorting($request, $query);

        /** @var \App\Http\Resources\CategoryResource */
        return ProductResource::collection(
            $query->paginate(
                $request->has('per_page') ?
                    $request->input('per_page') :
                    config('app.record_per_page')
            )
        );
    }


    /**
     * Create Posts
     * @param \App\Http\Requests\Product\StoreProductRequest $request
     * @return ProductResource |
     */
    public function store(StoreProductRequest $request): ProductResource
    {

        /** @var array $attributes  */
        $attributes = $request->validated();

        $product = new Product();

        return DB::transaction(function () use ($attributes, $product) {
            $new_product = $product->create($attributes);
            return ProductResource::make($new_product);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProductRequest  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
