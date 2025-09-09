<?php

namespace App\Modules\UserData\Http\Requests\UserState;

use Illuminate\Foundation\Http\FormRequest;

class CreateStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // CORRECCIÓN DE SEGURIDAD: Solo los administradores pueden crear estados.
        return auth()->check() && auth()->user()->role->name === 'admin';
    }

    public function rules(): array
    {
        return [
            // MEJORADO: Se previene la creación de estados duplicados.
            'name' => ['required', 'string', 'max:50', 'unique:user_states,name'],
        ];
    }
}