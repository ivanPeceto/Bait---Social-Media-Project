<?php

namespace App\Modules\UserInteractions\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class CreateMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content_messages' => ['required', 'string', 'max:1000'],
            
        ];
    }
}