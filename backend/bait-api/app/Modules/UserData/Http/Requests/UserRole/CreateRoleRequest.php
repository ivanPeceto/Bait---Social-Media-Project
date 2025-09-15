<?php

namespace App\Modules\UserData\Http\Requests\UserRole;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // CORRECCIÓN DE SEGURIDAD: Solo permite la acción si el usuario
        // está autenticado y tiene el rol de 'admin'.
        return auth()->check() && auth()->user()->role->name === 'admin';
    }

    public function rules(): array
    {
        return [
            // MEJORADO: Añadimos la regla 'unique' para evitar roles duplicados.
            'name' => ['required', 'string', 'max:50', 'unique:user_roles,name'],
        ];
    }
}