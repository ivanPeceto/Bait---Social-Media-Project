<?php

namespace App\Modules\UserData\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // CORREGIDO: Asegura que el usuario esté autenticado.
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = auth()->id();
        return [
            'username' => ['sometimes', 'string', 'min:3', 'max:30', 'alpha_dash', "unique:users,username,{$userId}"],
            'name'     => ['sometimes', 'string', 'max:120'],
            'email'    => ['sometimes', 'email', "unique:users,email,{$userId}"],
            'role_id'   => ['sometimes', 'integer', 'exists:user_roles,id'],
            'state_id'  => ['sometimes', 'integer', 'exists:user_states,id']
        ];
    }
}