<?php

namespace App\Modules\UserData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
    public function rules(): array {
    return [
        'username' => ['sometimes','string','min:3','max:30','alpha_dash',"unique:users,username,{$this->user()->id}"],
        'name'   => ['sometimes','string','max:120'],
        'email'  => ['sometimes','email',"unique:users,email,{$this->user()->id}"],
    ];
}
}
