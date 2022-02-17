<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (($user = $this->route('user')) && ($user instanceof User))
            return false;
        else
            return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
            ],
            'first_name' => [
                'required',
                'string',
                'between:3,255'
            ],
            'last_name' => [
                'required',
                'string',
                'between:3,255',
            ],
            'password' => [
                'required',
                Password::min(8)
                    ->numbers()
                    ->mixedCase(),
                'string',
                'max:10485760',
                'confirmed',
            ],

        ];
    }



    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'avatar' => "Avatar",
            'first_name' => "First name",
            'last_name' => "Last name",
            'email' => "Email",
            'password' => "Password",
            'password_confirmation' => "Confirm Password",
        ];
    }
}
