<?php

namespace App\Modules\UserData\Http\Requests\UserRole;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // CORRECCIÓN DE SEGURIDAD: Solo un 'admin' puede actualizar roles.
        return auth()->check() && auth()->user()->role->name === 'admin';
    }

    public function rules(): array
    {
        $roleId = $this->route('role')->id;
        return [
            'name' => ['sometimes', 'string', 'max:50', 'unique:user_roles,name,' . $roleId],
        ];
    }
}