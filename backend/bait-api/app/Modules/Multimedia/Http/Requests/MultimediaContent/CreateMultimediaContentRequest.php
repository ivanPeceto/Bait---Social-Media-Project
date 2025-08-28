<?php

namespace App\Modules\Multimedia\Http\Requests\MultimediaContent;

use Illuminate\Foundation\Http\FormRequest;

class CreateMultimediaContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'url', 'max:255'],
            'type' => ['required', 'string', 'in:image,video'],
            'post_id' => ['required', 'exists:posts,id'],
        ];
    }
}