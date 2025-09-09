<?php

namespace App\Modules\UserData\Http\Requests\UserState;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role->name === 'admin';
    }

    public function rules(): array
    {
        $stateId = $this->route('state')->id;
        return [
            'name' => ['sometimes', 'string', 'max:50', 'unique:user_states,name,' . $stateId],
        ];
    }
}