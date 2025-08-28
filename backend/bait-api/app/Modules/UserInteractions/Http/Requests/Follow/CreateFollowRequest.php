<?php

namespace App\Modules\UserInteractions\Http\Requests\Follow;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFollowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'following_id' => [
                'required',
                'exists:users,id',
                Rule::notIn([$this->user()->id]),
                //sirve para evitar que un usuario se siga a si mismo.
            ],
        ];
    }
}