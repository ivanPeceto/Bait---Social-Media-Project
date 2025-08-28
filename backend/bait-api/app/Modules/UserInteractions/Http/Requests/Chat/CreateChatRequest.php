<?php

namespace App\Modules\UserInteractions\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class CreateChatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'participants' => ['required', 'array', 'min:1'],
            'participants.*' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}