<?php

namespace App\Modules\UserData\Http\Requests\UserState;

use Illuminate\Foundation\Http\FormRequest;

class DeleteStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo los administradores pueden borrar estados.
        return auth()->check() && auth()->user()->role->name === 'admin';
    }

    public function rules(): array
    {
        return [];
    }
}