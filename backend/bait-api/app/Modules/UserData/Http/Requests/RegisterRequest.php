<?php

namespace App\Modules\UserData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
    return [
        'username'   => ['required','string','min:3','max:30','alpha_dash','unique:users,handle'],
        'name'     => ['required','string','max:120'],
        'email'    => ['required','email','unique:users,email'],
        'password' => ['required','string','min:8','confirmed'],
    ];
    }
}
