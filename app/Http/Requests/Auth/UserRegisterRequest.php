<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'first_name'    => ['required', 'string', 'max:50'],
            'last_name'     => ['required', 'string', 'max:50'],
            'email'         => ['required', 'string', 'email', 'unique:users', 'max:100'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'is_agree_termsconditions' => ['required', 'boolean']
        ];
    }
}
