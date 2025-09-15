<?php

namespace App\Modules\Multimedia\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'content_posts' => ['sometimes', 'string', 'min:1', 'max:500'],
        ];
    }
}