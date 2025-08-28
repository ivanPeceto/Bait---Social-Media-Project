<?php

namespace App\Modules\UserInteractions\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\UserInteractions\Domain\Models\Chat;

class CreateMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $chat = Chat::find($this->chat_id);
        return $chat && $chat->users->contains($this->user());
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:1000'],
            'chat_id' => ['required', 'exists:chats,id'],
        ];
    }
}