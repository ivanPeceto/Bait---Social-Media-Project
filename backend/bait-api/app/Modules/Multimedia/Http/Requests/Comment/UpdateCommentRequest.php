<?php

namespace App\Modules\Multimedia\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'content' => ['sometimes', 'string', 'min:1', 'max:500'],
        ];
    }
}