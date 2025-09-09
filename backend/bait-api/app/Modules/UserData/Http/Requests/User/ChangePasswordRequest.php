<?php

namespace App\Modules\UserData\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        // CORREGIDO: Asegura que el usuario esté autenticado.
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // MEJORADO: Añadimos una regla personalizada para verificar la contraseña actual.
            'current_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, auth()->user()->password)) {
                        $fail('The :attribute is incorrect.');
                    }
                },
            ],
            'new_password'     => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}