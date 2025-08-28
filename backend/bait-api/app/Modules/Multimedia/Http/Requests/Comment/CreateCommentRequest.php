<?php

namespace App\Modules\Multimedia\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class CreateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:500'],
            'post_id' => ['required', 'exists:posts,id'],
        ];
    }
}