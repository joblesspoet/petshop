<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return !($user = $this->user()) ||
               !($user instanceof User);
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
            ],
            'password' => [
                'required',
                'min:8',
                'max:10485760',
                'confirmed',
            ],
            'reset_password_token' => [
                'required']
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
            'email' => ucfirst(trans('auth.email')),
            'password' => ucfirst(trans('auth.password')),
            'password_confirmation' => ucfirst(trans('auth.password_confirmation')),
            'reset_password_token' => ucfirst(trans('passwords.token')),
        ];
    }
}
