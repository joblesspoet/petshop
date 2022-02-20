<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'category_id' => [
                'required',
                'exists:App\Models\Category,id',
            ],
            'title' => [
                'required',
                'string',
                'between:3,255',
            ],
            'price' => [
                'required'
            ],
            'description' => [
                'sometimes'
            ],
            'images' => [
                'sometimes',
                'array'
            ],
        ];
    }
}
